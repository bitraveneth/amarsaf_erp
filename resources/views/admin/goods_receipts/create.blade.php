@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create GRN</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Receive supplier materials and post inventory.</p>
        </div>
        <a href="{{ route('admin.goods-receipts.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Back</a>
    </div>

    <form action="{{ route('admin.goods-receipts.store') }}" method="POST" class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Purchase Order (optional)</label>
                <select id="po-select" name="purchase_order_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">No PO</option>
                    @foreach($purchaseOrders as $po)
                        <option value="{{ $po->id }}" data-supplier="{{ $po->supplier_id }}" @selected(old('purchase_order_id', $selectedPo?->id) == $po->id)>{{ $po->number }} - {{ $po->supplier->name ?? 'Supplier' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                <select id="supplier-select" name="supplier_id" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Select supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $selectedPo?->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Warehouse</label>
                <select name="warehouse_id" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Select warehouse</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Purchase Bill (optional)</label>
                <select name="purchase_bill_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">None</option>
                    @foreach($purchaseBills as $bill)
                        <option value="{{ $bill->id }}" @selected(old('purchase_bill_id') == $bill->id)>{{ $bill->number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Received At</label>
                <input type="datetime-local" name="received_at" value="{{ old('received_at', now()->format('Y-m-d\TH:i')) }}" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div class="sm:col-span-2 lg:col-span-4">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">GRN Items</h3>
                <button type="button" id="add-grn-item" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Add line</button>
            </div>
            <div id="grn-items" class="space-y-3"></div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">Post GRN</button>
        </div>
    </form>
</div>

<script>
(() => {
    const products = @json($products->map(fn($p) => ['id' => $p->id, 'label' => $p->sku . ' - ' . $p->name])->values());
    const locations = @json($locations->map(fn($l) => ['id' => $l->id, 'label' => $l->code])->values());
    const purchaseOrders = @json($purchaseOrders->map(function ($po) {
        return [
            'id' => $po->id,
            'supplier_id' => $po->supplier_id,
            'items' => $po->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'received_quantity' => (float) $item->received_quantity,
                    'unit_price' => $item->unit_price !== null ? (float) $item->unit_price : null,
                ];
            })->values(),
        ];
    })->values());

    const productOptions = ['<option value="">None</option>'].concat(products.map(p => `<option value="${p.id}">${p.label}</option>`)).join('');
    const locationOptions = ['<option value="">None</option>'].concat(locations.map(l => `<option value="${l.id}">${l.label}</option>`)).join('');

    const container = document.getElementById('grn-items');
    const addBtn = document.getElementById('add-grn-item');
    const poSelect = document.getElementById('po-select');
    const supplierSelect = document.getElementById('supplier-select');
    let idx = 0;

    function addRow(row = null) {
        const quantity = row ? Math.max((row.quantity || 0) - (row.received_quantity || 0), 0) : 1;
        const div = document.createElement('div');
        div.className = 'grid grid-cols-1 gap-3 rounded-xl border border-gray-200 p-3 sm:grid-cols-6 dark:border-gray-700';
        div.innerHTML = `
            <input type="hidden" name="items[${idx}][purchase_order_item_id]" value="${row?.id || ''}">
            <input type="text" name="items[${idx}][description]" value="${row?.description || ''}" placeholder="Description" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" disabled>
            <select name="items[${idx}][product_id]" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">${productOptions}</select>
            <input type="number" min="0.01" step="0.01" name="items[${idx}][quantity]" value="${quantity}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" required>
            <input type="number" min="0" step="0.0001" name="items[${idx}][unit_cost]" value="${row?.unit_price ?? ''}" placeholder="Unit cost" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <select name="items[${idx}][warehouse_location_id]" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">${locationOptions}</select>
            <select name="items[${idx}][qc_status]" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
            <div class="sm:col-span-6 flex gap-2">
                <input type="text" name="items[${idx}][remarks]" placeholder="Remarks" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <button type="button" data-remove class="rounded-lg border border-red-300 px-2 text-xs text-red-600">Del</button>
            </div>
        `;

        const productSelect = div.querySelector(`select[name="items[${idx}][product_id]"]`);
        if (row?.product_id) productSelect.value = String(row.product_id);

        div.querySelector('[data-remove]').addEventListener('click', () => div.remove());
        container.appendChild(div);
        idx++;
    }

    function loadPoLines(poId) {
        container.innerHTML = '';
        idx = 0;

        if (!poId) {
            addRow();
            return;
        }

        const po = purchaseOrders.find(p => String(p.id) === String(poId));
        if (!po) {
            addRow();
            return;
        }

        if (po.supplier_id) supplierSelect.value = String(po.supplier_id);

        po.items.forEach(item => {
            const pendingQty = (item.quantity || 0) - (item.received_quantity || 0);
            if (pendingQty > 0) addRow(item);
        });

        if (!container.children.length) addRow();
    }

    poSelect.addEventListener('change', (e) => loadPoLines(e.target.value));
    addBtn.addEventListener('click', () => addRow());

    loadPoLines(poSelect.value);
})();
</script>
@endsection
