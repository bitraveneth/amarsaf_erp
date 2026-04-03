<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReturnController extends Controller
{
    public function index()
    {
        $returns = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse', 'order.agent'])
            ->where('type', 'customer-return')
            ->latest()
            ->paginate(15);

        return view('admin.returns.customer.index', compact('returns'));
    }

    public function create()
    {
        $orders = Order::with('agent')
            ->where('status', 'delivered')
            ->latest()
            ->limit(50)
            ->get();
        $products = Product::orderBy('name')->get();
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $warehouses = Warehouse::query()
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('id', $warehouseIds);
            })
            ->orderBy('name')
            ->get();

        return view('admin.returns.customer.create', compact('orders', 'products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        $order = Order::findOrFail($data['order_id']);
        $this->ensureWarehouseAccess((int) $data['warehouse_id']);

        if ($order->status !== 'delivered') {
            return back()
                ->withErrors(['order_id' => 'Returns can only be recorded for delivered orders.'])
                ->withInput();
        }

        $orderItem = OrderItem::where('order_id', $order->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if (! $orderItem) {
            return back()
                ->withErrors(['product_id' => 'Selected product is not part of this order.'])
                ->withInput();
        }

        $deliveredQuantity = (float) $orderItem->quantity;
        if ($order->relationLoaded('delivery') || $order->delivery) {
            $deliveryItem = optional($order->delivery)->items()
                ->where('order_item_id', $orderItem->id)
                ->first();

            if ($deliveryItem) {
                $deliveredQuantity = (float) $deliveryItem->qty_delivered;
                if ($deliveredQuantity <= 0 && ((float) $deliveryItem->qty_dispatched > 0 || (float) $deliveryItem->qty_short > 0 || (float) $deliveryItem->qty_damaged > 0)) {
                    $deliveredQuantity = max(
                        (float) $deliveryItem->qty_dispatched - (float) $deliveryItem->qty_short - (float) $deliveryItem->qty_damaged,
                        0
                    );
                }
            }
        }

        $alreadyReturned = (float) StockMovement::where('order_id', $order->id)
            ->where('type', 'customer-return')
            ->whereHas('stockEntry', function ($query) use ($data) {
                $query->where('product_id', $data['product_id']);
            })
            ->sum('quantity');

        $remainingReturnable = max($deliveredQuantity - $alreadyReturned, 0);
        if ((float) $data['quantity'] > $remainingReturnable) {
            return back()
                ->withErrors(['quantity' => 'Return quantity exceeds the delivered quantity remaining for this product.'])
                ->withInput();
        }

        DB::transaction(function () use ($data, $order) {
            $entry = StockEntry::create([
                'warehouse_id' => $data['warehouse_id'],
                'warehouse_location_id' => null,
                'product_id' => $data['product_id'],
                'batch_id' => null,
                'quantity' => $data['quantity'],
                'status' => 'available',
            ]);

            StockMovement::create([
                'stock_entry_id' => $entry->id,
                'order_id' => $order->id,
                'type' => 'customer-return',
                'quantity' => $data['quantity'],
                'notes' => $data['notes'],
            ]);
        });

        return redirect()->route('admin.returns.customer.index')->with('status', 'Customer return recorded and stock updated.');
    }

    protected function ensureWarehouseAccess(int $warehouseId): void
    {
        if (! auth()->user()?->canAccessWarehouse($warehouseId)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }
}
