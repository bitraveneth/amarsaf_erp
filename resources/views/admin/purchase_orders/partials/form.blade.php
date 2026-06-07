@php
    $order = $order ?? null;
@endphp

<div class="space-y-6">
    <section class="erp-order-panel">
        <div class="erp-order-panel__head">
            <div>
                <h3 class="erp-order-panel__title">Supplier &amp; dates</h3>
                <p class="erp-order-panel__desc">Who you buy from and when goods should arrive</p>
            </div>
            <div class="erp-order-segment">
                <button type="button" @click="supplierMode = 'existing'"
                        :class="supplierMode === 'existing' ? 'is-active' : ''"
                        class="erp-order-segment__btn">Registered</button>
                <button type="button" @click="supplierMode = 'new'"
                        :class="supplierMode === 'new' ? 'is-active' : ''"
                        class="erp-order-segment__btn">One-time</button>
            </div>
        </div>

        <input type="hidden" name="supplier_mode" x-model="supplierMode">

        <div class="erp-order-field-grid">
            <div class="lg:col-span-5" x-show="supplierMode === 'existing'" x-cloak>
                <label for="supplier_id" class="erp-order-label">Supplier <span class="text-red-500">*</span></label>
                <select id="supplier_id" name="supplier_id" x-model="selectedSupplierId"
                        :required="supplierMode === 'existing'" :disabled="supplierMode !== 'existing'"
                        class="erp-order-input">
                    <option value="">Select supplier…</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">
                            {{ $supplier->name }}@if($supplier->is_one_time) *@endif
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-6 lg:col-span-3" x-show="supplierMode === 'new'" x-cloak>
                <label class="erp-order-label">Vendor name <span class="text-red-500">*</span></label>
                <input type="text" name="new_supplier[name]" x-model="newSupplier.name"
                       :required="supplierMode === 'new'" :disabled="supplierMode !== 'new'"
                       class="erp-order-input" placeholder="Company name">
                @error('new_supplier.name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-3 lg:col-span-2" x-show="supplierMode === 'new'" x-cloak>
                <label class="erp-order-label">Phone</label>
                <input type="text" name="new_supplier[phone]" x-model="newSupplier.phone"
                       :disabled="supplierMode !== 'new'" class="erp-order-input">
            </div>
            <div class="sm:col-span-3 lg:col-span-2" x-show="supplierMode === 'new'" x-cloak>
                <label class="erp-order-label">Contact</label>
                <input type="text" name="new_supplier[contact_person]" x-model="newSupplier.contact_person"
                       :disabled="supplierMode !== 'new'" class="erp-order-input">
            </div>

            <div class="sm:col-span-3 lg:col-span-2">
                <label for="order_date" class="erp-order-label">Order date <span class="text-red-500">*</span></label>
                <input type="date" id="order_date" name="order_date" required
                       value="{{ old('order_date', $order?->order_date?->format('Y-m-d') ?? now()->toDateString()) }}"
                       class="erp-order-input">
                @error('order_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-3 lg:col-span-2">
                <label for="expected_date" class="erp-order-label">Expected delivery</label>
                <input type="date" id="expected_date" name="expected_date"
                       value="{{ old('expected_date', $order?->expected_date?->format('Y-m-d') ?? '') }}"
                       class="erp-order-input">
            </div>
            <div class="sm:col-span-6 lg:col-span-4">
                <label for="notes" class="erp-order-label">Notes</label>
                <input type="text" id="notes" name="notes" class="erp-order-input"
                       value="{{ old('notes', $order?->notes ?? '') }}"
                       placeholder="Quote ref, delivery terms…">
            </div>
        </div>
    </section>

    <section class="erp-order-lines">
        <div class="erp-order-lines__head">
            <div>
                <h3 class="erp-order-panel__title">Line items</h3>
                <p class="erp-order-panel__desc">Materials &amp; services — or a custom one-off line</p>
            </div>
        </div>

        <div class="erp-order-lines__table-wrap">
            <table class="erp-order-lines__table">
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <th class="min-w-[200px]">Item</th>
                        <th class="min-w-[120px]">Notes</th>
                        <th class="w-28 text-center">Qty</th>
                        <th class="w-24 text-center">UOM</th>
                        <th class="w-32 text-center">Unit price</th>
                        <th class="w-36 text-right">Line total</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in rows" :key="row.key">
                        <tr>
                            <td class="text-xs text-gray-400" x-text="index + 1"></td>
                            <td>
                                <input type="hidden"
                                       :name="`items[${index}][product_id]`"
                                       :value="isCustom(row) ? '' : row.product_id">
                                <select class="erp-order-input max-w-[220px] text-xs"
                                        x-model="row.product_id"
                                        @change="onProductChange(row)">
                                    <option value="">Select material…</option>
                                    <option value="custom">Custom item</option>
                                    <template x-for="group in groupedProducts()" :key="group.label">
                                        <optgroup :label="group.label">
                                            <template x-for="product in group.items" :key="product.id">
                                                <option :value="String(product.id)" x-text="product.label"></option>
                                            </template>
                                        </optgroup>
                                    </template>
                                </select>
                                <div class="mt-2" x-show="isCustom(row)" x-cloak>
                                    <input type="text" class="erp-order-input text-xs"
                                           x-model="row.description"
                                           :name="isCustom(row) ? `items[${index}][description]` : null"
                                           :required="isCustom(row)"
                                           placeholder="What are you buying?">
                                </div>
                                <p class="mt-1 text-[10px] text-gray-400"
                                   x-show="!isCustom(row) && productFor(row)" x-cloak>
                                    <span x-text="productFor(row)?.type_label"></span>
                                    <span x-show="productFor(row)?.category"> · </span>
                                    <span x-text="productFor(row)?.category"></span>
                                </p>
                            </td>
                            <td>
                                <input type="text" class="erp-order-input text-xs"
                                       x-show="!isCustom(row)"
                                       x-model="row.description"
                                       :name="`items[${index}][description]`"
                                       :required="!isCustom(row)"
                                       placeholder="Optional">
                                <span x-show="isCustom(row)" x-cloak class="text-xs text-gray-400">—</span>
                            </td>
                            <td>
                                <input type="number" min="0.01" step="0.01"
                                       class="erp-order-input erp-order-input--num text-center"
                                       x-model.number="row.quantity"
                                       :name="`items[${index}][quantity]`" required>
                            </td>
                            <td>
                                <input type="text"
                                       class="erp-order-input erp-order-input--num text-center text-xs uppercase"
                                       x-show="isCustom(row)"
                                       x-model="row.uom"
                                       :name="isCustom(row) ? `items[${index}][uom]` : null"
                                       :required="isCustom(row)"
                                       placeholder="pcs">
                                <div x-show="!isCustom(row)"
                                     class="flex h-[42px] items-center justify-center rounded-xl bg-gray-100 text-sm font-bold uppercase text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                     x-text="uomFor(row)"></div>
                            </td>
                            <td>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-400">BDT</span>
                                    <input type="number" min="0" step="0.01"
                                           class="erp-order-input erp-order-input--num pl-11 text-right"
                                           x-model="row.unit_price"
                                           :name="`items[${index}][unit_price]`" placeholder="0.00">
                                </div>
                            </td>
                            <td class="text-right">
                                <div class="erp-order-total-pill !min-w-[6.5rem] !px-3 !py-2">
                                    <span class="erp-order-total-pill__label">Total</span>
                                    <span class="erp-order-total-pill__value !text-base">
                                        <span class="text-xs">BDT </span><span x-text="formatMoney(lineTotal(row))"></span>
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" @click="removeRow(index)" x-show="rows.length > 1"
                                        class="erp-order-btn--danger-icon" title="Remove">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot class="border-t border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-900/30">
                    <tr>
                        <td colspan="8" class="!p-0">
                            <button type="button" @click="addRow()" class="erp-order-lines__add">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add line item
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" class="px-5 py-4 text-right">
                            <span class="text-sm text-gray-500" x-text="`${rows.length} line${rows.length === 1 ? '' : 's'}`"></span>
                            <span class="mx-2 text-gray-300">·</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">PO total</span>
                        </td>
                        <td class="px-3 py-4 text-right" colspan="2">
                            <div class="erp-order-total-pill">
                                <span class="erp-order-total-pill__label">Estimated</span>
                                <span class="erp-order-total-pill__value">BDT <span x-text="formatMoney(grandTotal())"></span></span>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @error('items')<p class="px-5 py-2 text-xs text-red-600">{{ $message }}</p>@enderror
        @error('items.*')<p class="px-5 py-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </section>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('purchaseOrderForm', (config) => ({
                    products: config.products || [],
                    suppliers: config.suppliers || [],
                    supplierMode: config.supplierMode || 'existing',
                    selectedSupplierId: config.selectedSupplierId || '',
                    newSupplier: config.newSupplier || {},
                    rows: (config.initialRows || []).map((row, index) => ({
                        key: Date.now() + index,
                        product_id: row.product_id === 'custom'
                            ? 'custom'
                            : (row.product_id ? String(row.product_id) : ''),
                        description: row.description || '',
                        uom: row.uom || 'pcs',
                        quantity: row.quantity ?? 1,
                        unit_price: row.unit_price ?? '',
                    })),
                    isCustom(row) {
                        return row.product_id === 'custom';
                    },
                    productMap() {
                        return Object.fromEntries(this.products.map((p) => [String(p.id), p]));
                    },
                    productFor(row) {
                        if (this.isCustom(row) || ! row.product_id) return null;
                        return this.productMap()[String(row.product_id)] || null;
                    },
                    groupedProducts() {
                        const order = { 'Raw material': 1, 'Service': 2 };
                        const groups = {};
                        this.products.forEach((product) => {
                            const label = product.group_label || product.type_label || 'Other';
                            (groups[label] ??= []).push(product);
                        });
                        return Object.entries(groups)
                            .sort(([a], [b]) => (order[a] ?? 99) - (order[b] ?? 99) || a.localeCompare(b))
                            .map(([label, items]) => ({
                                label,
                                items: items.sort((a, b) => a.label.localeCompare(b.label)),
                            }));
                    },
                    uomFor(row) {
                        if (this.isCustom(row)) return row.uom || '—';
                        return this.productFor(row)?.uom ?? '—';
                    },
                    onProductChange(row) {
                        if (this.isCustom(row)) {
                            if (! row.uom) row.uom = 'pcs';
                            return;
                        }
                        const product = this.productFor(row);
                        if (! product) return;
                        if (! row.description) row.description = product.name;
                        if (row.unit_price === '' && product.standard_cost !== null) {
                            row.unit_price = product.standard_cost;
                        }
                    },
                    lineTotal(row) {
                        const qty = parseFloat(row.quantity) || 0;
                        const price = parseFloat(row.unit_price);
                        return Number.isNaN(price) ? 0 : qty * price;
                    },
                    grandTotal() {
                        return this.rows.reduce((sum, row) => sum + this.lineTotal(row), 0);
                    },
                    formatMoney(value) {
                        return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },
                    addRow() {
                        this.rows.push({
                            key: Date.now() + Math.random(),
                            product_id: '',
                            description: '',
                            uom: 'pcs',
                            quantity: 1,
                            unit_price: '',
                        });
                    },
                    removeRow(index) {
                        if (this.rows.length > 1) this.rows.splice(index, 1);
                    },
                }));
            });
        </script>
    @endpush
@endonce
