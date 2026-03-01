<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\PurchaseOrder;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    public function index()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $receipts = GoodsReceipt::with(['supplier', 'warehouse', 'purchaseOrder'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->latest('received_at')
            ->paginate(15);

        return view('admin.goods_receipts.index', compact('receipts'));
    }

    public function create(Request $request)
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::query()
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('id', $warehouseIds);
            })
            ->orderBy('name')
            ->get();
        $locations = WarehouseLocation::query()
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->orderBy('code')
            ->get();
        $products = Product::orderBy('name')->get();
        $purchaseBills = PurchaseBill::orderByDesc('bill_date')->limit(100)->get();

        $purchaseOrders = PurchaseOrder::with(['supplier', 'items.product'])
            ->whereIn('status', ['approved', 'partial_received'])
            ->orderByDesc('order_date')
            ->get();

        $selectedPo = null;
        if ($request->filled('purchase_order_id')) {
            $selectedPo = $purchaseOrders->firstWhere('id', (int) $request->input('purchase_order_id'));
        }

        return view('admin.goods_receipts.create', compact(
            'suppliers',
            'warehouses',
            'locations',
            'products',
            'purchaseBills',
            'purchaseOrders',
            'selectedPo'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'purchase_bill_id' => 'nullable|exists:purchase_bills,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'received_at' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'items.*.warehouse_location_id' => 'nullable|exists:warehouse_locations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.qc_status' => 'required|in:pending,approved,rejected',
            'items.*.remarks' => 'nullable|string',
        ]);

        $this->ensureWarehouseAccess((int) $data['warehouse_id']);

        $receipt = null;

        DB::transaction(function () use ($data, &$receipt) {
            $receipt = GoodsReceipt::create([
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'purchase_bill_id' => $data['purchase_bill_id'] ?? null,
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => auth()->id(),
                'grn_number' => 'GRN-' . str_pad((string) (GoodsReceipt::max('id') + 1), 6, '0', STR_PAD_LEFT),
                'received_at' => $data['received_at'],
                'status' => 'posted',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $qty = (float) $item['quantity'];
                $unitCost = isset($item['unit_cost']) ? (float) $item['unit_cost'] : null;

                $grnItem = GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'product_id' => $item['product_id'] ?? null,
                    'batch_id' => $item['batch_id'] ?? null,
                    'warehouse_location_id' => $item['warehouse_location_id'] ?? null,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'line_total' => $unitCost !== null ? ($qty * $unitCost) : null,
                    'qc_status' => $item['qc_status'],
                    'remarks' => $item['remarks'] ?? null,
                ]);

                if ($grnItem->purchase_order_item_id) {
                    $poItem = $grnItem->purchaseOrderItem;
                    if ($poItem) {
                        $poItem->received_quantity = (float) $poItem->received_quantity + $qty;
                        $poItem->save();
                    }
                }

                if ($grnItem->qc_status === 'approved' && $grnItem->product_id) {
                    StockEntry::create([
                        'purchase_bill_id' => $receipt->purchase_bill_id,
                        'goods_receipt_id' => $receipt->id,
                        'warehouse_id' => $receipt->warehouse_id,
                        'warehouse_location_id' => $grnItem->warehouse_location_id,
                        'product_id' => $grnItem->product_id,
                        'batch_id' => $grnItem->batch_id,
                        'quantity' => $qty,
                        'status' => 'available',
                    ]);
                }
            }

            if ($receipt->purchase_order_id) {
                $po = PurchaseOrder::with('items')->find($receipt->purchase_order_id);
                if ($po) {
                    $allReceived = $po->items->every(function ($item) {
                        return (float) $item->received_quantity >= (float) $item->quantity;
                    });

                    $hasAnyReceived = $po->items->contains(function ($item) {
                        return (float) $item->received_quantity > 0;
                    });

                    if ($allReceived) {
                        $po->status = 'received';
                    } elseif ($hasAnyReceived) {
                        $po->status = 'partial_received';
                    }

                    $po->save();
                }
            }
        });

        return redirect()
            ->route('admin.goods-receipts.show', $receipt)
            ->with('status', 'Goods receipt posted and stock updated.');
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $this->ensureWarehouseAccess((int) $goodsReceipt->warehouse_id);

        $goodsReceipt->load([
            'supplier',
            'warehouse',
            'purchaseOrder',
            'purchaseBill',
            'creator',
            'items.product',
            'items.batch',
            'items.location',
            'items.purchaseOrderItem',
        ]);

        return view('admin.goods_receipts.show', ['receipt' => $goodsReceipt]);
    }

    protected function ensureWarehouseAccess(int $warehouseId): void
    {
        if (! auth()->user()?->canAccessWarehouse($warehouseId)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }
}
