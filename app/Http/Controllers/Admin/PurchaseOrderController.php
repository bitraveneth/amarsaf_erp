<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with(['supplier', 'items'])
            ->withSum('items as total_value', 'line_total')
            ->withCount('goodsReceipts')
            ->latest('order_date')
            ->paginate(15);

        return view('admin.purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        return view('admin.purchase_orders.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validatedOrder($request);
        $po = null;

        DB::transaction(function () use ($request, $data, &$po) {
            $po = PurchaseOrder::create([
                'supplier_id' => $this->resolveSupplierId($request, $data),
                'number' => $this->nextPoNumber(),
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($po, $data['items']);
        });

        return redirect()
            ->route('admin.purchase-orders.show', $po)
            ->with('status', 'Purchase order created. Review and approve before GRN posting.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product.materialCategory', 'goodsReceipts']);

        return view('admin.purchase_orders.show', ['order' => $purchaseOrder]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (! $this->isEditable($purchaseOrder)) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders without receipts can be edited.');
        }

        $purchaseOrder->load(['supplier', 'items.product']);

        return view('admin.purchase_orders.edit', array_merge(
            $this->formData($purchaseOrder),
            ['order' => $purchaseOrder]
        ));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (! $this->isEditable($purchaseOrder)) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only draft purchase orders without receipts can be edited.');
        }

        $data = $this->validatedOrder($request);

        DB::transaction(function () use ($request, $purchaseOrder, $data) {
            $purchaseOrder->update([
                'supplier_id' => $this->resolveSupplierId($request, $data),
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $purchaseOrder->items()->delete();
            $this->syncItems($purchaseOrder, $data['items']);
        });

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('status', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Only draft purchase orders can be deleted.');
        }

        if ($purchaseOrder->goodsReceipts()->exists()) {
            return back()->with('error', 'Purchase order is linked to GRNs and cannot be deleted.');
        }

        $purchaseOrder->delete();

        return redirect()
            ->route('admin.purchase-orders.index')
            ->with('status', 'Purchase order deleted.');
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('status', 'Only draft purchase orders can be approved.');
        }

        $purchaseOrder->update(['status' => 'approved']);

        return back()->with('status', 'Purchase order approved. You can now create GRN.');
    }

    protected function formData(?PurchaseOrder $order = null): array
    {
        $products = $this->procurementProducts();
        $productCatalog = $this->productCatalog($products);

        return [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => $products,
            'productCatalog' => $productCatalog,
            'productTypeOptions' => $this->productTypeOptions(),
            'formState' => $this->buildFormState($productCatalog, $order),
        ];
    }

    protected function buildFormState(array $productCatalog, ?PurchaseOrder $order = null): array
    {
        $initialRows = old('items');
        if ($initialRows === null && $order) {
            $initialRows = $order->items->map(fn ($item) => [
                'product_id' => $item->product_id ? (string) $item->product_id : 'custom',
                'description' => $item->description,
                'uom' => $item->uom ?? '',
                'quantity' => (float) $item->quantity,
                'unit_price' => $item->unit_price !== null ? (float) $item->unit_price : '',
            ])->values()->all();
        }
        if (empty($initialRows)) {
            $initialRows = [['product_id' => '', 'description' => '', 'quantity' => 1, 'unit_price' => '']];
        }

        return [
            'products' => $productCatalog,
            'initialRows' => $initialRows,
            'supplierMode' => old('supplier_mode', 'existing'),
            'newSupplier' => old('new_supplier', [
                'name' => '',
                'contact_person' => '',
                'phone' => '',
                'email' => '',
                'address' => '',
                'tax_id' => '',
            ]),
            'suppliers' => Supplier::orderBy('name')->get()->map(fn ($s) => [
                'id' => (string) $s->id,
                'name' => $s->name,
                'phone' => $s->phone,
                'contact' => $s->contact_person,
            ])->values()->all(),
            'selectedSupplierId' => (string) old('supplier_id', $order?->supplier_id ?? ''),
            'typeOptions' => $this->productTypeOptions(),
        ];
    }

    protected function procurementProducts()
    {
        return Product::query()
            ->purchasable()
            ->with('materialCategory')
            ->orderByRaw("CASE product_type WHEN 'raw' THEN 1 WHEN 'service' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->get();
    }

    protected function productCatalog($products): array
    {
        return $products->map(function (Product $product) {
            $type = $product->product_type;
            $typeLabel = $this->productTypeLabel($type);
            $category = $product->materialCategory;
            $categoryName = $category?->name;

            return [
                'id' => (string) $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'type' => $type,
                'type_label' => $typeLabel,
                'category' => $categoryName,
                'category_group' => $category?->group,
                'group_label' => $typeLabel,
                'uom' => $product->uom ? ucfirst($product->uom) : '—',
                'standard_cost' => $product->standard_cost !== null ? (float) $product->standard_cost : null,
                'label' => trim(($product->sku ? $product->sku . ' · ' : '') . $product->name),
                'search' => mb_strtolower(collect([
                    $product->sku,
                    $product->name,
                    $typeLabel,
                    $categoryName,
                    $category?->group,
                ])->filter()->implode(' ')),
            ];
        })->values()->all();
    }

    protected function productTypeOptions(): array
    {
        return [
            ['value' => '', 'label' => 'All purchasable'],
            ['value' => 'raw', 'label' => 'Raw materials & packaging'],
            ['value' => 'service', 'label' => 'Services & utilities'],
        ];
    }

    protected function productTypeLabel(?string $type): string
    {
        return match ($type) {
            'raw' => 'Raw material',
            'service' => 'Service',
            'inhouse' => 'In-house step',
            'finished', null => 'Finished product',
            default => ucfirst((string) $type),
        };
    }

    protected function resolveSupplierId(Request $request, array $data): int
    {
        if (($data['supplier_mode'] ?? 'existing') === 'new') {
            $newSupplier = $data['new_supplier'];

            $supplier = Supplier::create([
                'name' => $newSupplier['name'],
                'contact_person' => $newSupplier['contact_person'] ?? null,
                'phone' => $newSupplier['phone'] ?? null,
                'email' => $newSupplier['email'] ?? null,
                'address' => $newSupplier['address'] ?? null,
                'tax_id' => $newSupplier['tax_id'] ?? null,
                'is_one_time' => true,
            ]);

            return $supplier->id;
        }

        return (int) $data['supplier_id'];
    }

    protected function validatedOrder(Request $request): array
    {
        $rules = [
            'supplier_mode' => 'required|in:existing,new',
            'supplier_id' => 'required_if:supplier_mode,existing|nullable|exists:suppliers,id',
            'new_supplier.name' => 'required_if:supplier_mode,new|nullable|string|max:255',
            'new_supplier.contact_person' => 'nullable|string|max:255',
            'new_supplier.phone' => 'nullable|string|max:50',
            'new_supplier.email' => 'nullable|email|max:255',
            'new_supplier.address' => 'nullable|string|max:1000',
            'new_supplier.tax_id' => 'nullable|string|max:255',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereIn('product_type', ['raw', 'service'])),
            ],
            'items.*.uom' => 'nullable|string|max:20',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ];

        $data = $request->validate($rules);

        foreach ($data['items'] as $index => $item) {
            $productId = $item['product_id'] ?? null;
            if ($productId) {
                continue;
            }

            if (empty(trim($item['uom'] ?? ''))) {
                throw ValidationException::withMessages([
                    "items.{$index}.uom" => 'Unit of measure is required for custom line items.',
                ]);
            }
        }

        if (($data['supplier_mode'] ?? 'existing') === 'new') {
            $exists = Supplier::query()
                ->where('name', $data['new_supplier']['name'])
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'new_supplier.name' => 'A supplier with this name already exists. Choose them from the registered list instead.',
                ]);
            }
        }

        return $data;
    }

    protected function syncItems(PurchaseOrder $po, array $items): void
    {
        foreach ($items as $item) {
            $qty = (float) $item['quantity'];
            $unitPrice = isset($item['unit_price']) && $item['unit_price'] !== ''
                ? (float) $item['unit_price']
                : null;

            $productId = $item['product_id'] ?? null;

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $productId ?: null,
                'description' => $item['description'],
                'uom' => $productId ? null : ($item['uom'] ?? null),
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice !== null ? ($qty * $unitPrice) : null,
                'received_quantity' => 0,
            ]);
        }
    }

    protected function nextPoNumber(): string
    {
        return 'PO-' . str_pad((string) ((int) PurchaseOrder::max('id') + 1), 6, '0', STR_PAD_LEFT);
    }

    protected function isEditable(PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->status === 'draft'
            && ! $purchaseOrder->goodsReceipts()->exists();
    }
}
