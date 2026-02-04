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
            ->latest()
            ->limit(50)
            ->get();
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

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

        $hasProductOnOrder = OrderItem::where('order_id', $order->id)
            ->where('product_id', $data['product_id'])
            ->exists();

        if (! $hasProductOnOrder) {
            return back()
                ->withErrors(['product_id' => 'Selected product is not part of this order.'])
                ->withInput();
        }

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

        return redirect()->route('admin.returns.customer.index')->with('status', 'Customer return recorded and stock updated.');
    }
}
