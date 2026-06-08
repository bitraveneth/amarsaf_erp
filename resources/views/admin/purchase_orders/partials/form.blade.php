@php
    $order = $order ?? null;
    $currencyCode = $currencyCode ?? config('app.currency', 'BDT');
@endphp

<div class="po-create__grid">
    <section class="po-create__card po-create__card--supplier">
        <header class="po-create__card-head">
            <div>
                <h2 class="po-create__card-title">Supplier &amp; dates</h2>
                <p class="po-create__card-desc">Who you buy from and when goods should arrive.</p>
            </div>
            <div class="po-create__segment">
                <button type="button" @click="supplierMode = 'existing'"
                        :class="supplierMode === 'existing' ? 'is-active' : ''">Registered</button>
                <button type="button" @click="supplierMode = 'new'"
                        :class="supplierMode === 'new' ? 'is-active' : ''">One-time</button>
            </div>
        </header>

        <input type="hidden" name="supplier_mode" x-model="supplierMode">

        <div class="po-create__fields">
            <div class="po-create__field po-create__field--wide" x-show="supplierMode === 'existing'" x-cloak>
                <label for="supplier_id" class="po-create__label">Supplier <span class="po-create__req">*</span></label>
                <select id="supplier_id" name="supplier_id" x-model="selectedSupplierId"
                        :required="supplierMode === 'existing'" :disabled="supplierMode !== 'existing'"
                        class="po-create__input">
                    <option value="">Select supplier…</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">
                            {{ $supplier->name }}@if($supplier->is_one_time) *@endif
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')<p class="po-create__error">{{ $message }}</p>@enderror
            </div>

            <div class="po-create__field" x-show="supplierMode === 'new'" x-cloak>
                <label class="po-create__label">Vendor name <span class="po-create__req">*</span></label>
                <input type="text" name="new_supplier[name]" x-model="newSupplier.name"
                       :required="supplierMode === 'new'" :disabled="supplierMode !== 'new'"
                       class="po-create__input" placeholder="Company name">
                @error('new_supplier.name')<p class="po-create__error">{{ $message }}</p>@enderror
            </div>
            <div class="po-create__field" x-show="supplierMode === 'new'" x-cloak>
                <label class="po-create__label">Phone</label>
                <input type="text" name="new_supplier[phone]" x-model="newSupplier.phone"
                       :disabled="supplierMode !== 'new'" class="po-create__input">
            </div>
            <div class="po-create__field" x-show="supplierMode === 'new'" x-cloak>
                <label class="po-create__label">Contact</label>
                <input type="text" name="new_supplier[contact_person]" x-model="newSupplier.contact_person"
                       :disabled="supplierMode !== 'new'" class="po-create__input">
            </div>

            <div class="po-create__field">
                <label for="order_date" class="po-create__label">Order date <span class="po-create__req">*</span></label>
                <input type="date" id="order_date" name="order_date" required
                       value="{{ old('order_date', $order?->order_date?->format('Y-m-d') ?? now()->toDateString()) }}"
                       class="po-create__input">
                @error('order_date')<p class="po-create__error">{{ $message }}</p>@enderror
            </div>
            <div class="po-create__field">
                <label for="expected_date" class="po-create__label">Expected delivery</label>
                <input type="date" id="expected_date" name="expected_date"
                       value="{{ old('expected_date', $order?->expected_date?->format('Y-m-d') ?? '') }}"
                       class="po-create__input">
            </div>
            <div class="po-create__field po-create__field--wide">
                <label for="notes" class="po-create__label">Notes</label>
                <input type="text" id="notes" name="notes" class="po-create__input"
                       value="{{ old('notes', $order?->notes ?? '') }}"
                       placeholder="Quote ref, delivery terms…">
            </div>
        </div>

        <div class="po-create__supplier-preview">
            <span class="erp-po-index-supplier__avatar" x-text="supplierInitial()"></span>
            <div>
                <p class="po-create__preview-name" x-text="selectedSupplierName()"></p>
                <p class="po-create__preview-meta" x-text="`${filledLineCount()} of ${rows.length} lines filled`"></p>
            </div>
        </div>
    </section>

    <section class="po-create__card po-create__card--lines">
        <header class="po-create__card-head po-create__card-head--border">
            <div>
                <h2 class="po-create__card-title">Line items</h2>
                <p class="po-create__card-desc">Pick material or custom item, then add qty, unit, and pricing.</p>
            </div>
            <span class="po-create__badge" x-text="`${rows.length} line${rows.length === 1 ? '' : 's'}`"></span>
        </header>

        <div class="po-create__table-scroll">
            <table class="po-create__table">
                <colgroup>
                    <col class="col-num">
                    <col class="col-desc">
                    <col class="col-qty">
                    <col class="col-uom">
                    <col class="col-price">
                    <col class="col-disc">
                    <col class="col-vat">
                    <col class="col-total">
                    <col class="col-act">
                </colgroup>
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th class="col-desc">Description</th>
                        <th class="col-qty">Qty</th>
                        <th class="col-uom">UOM</th>
                        <th class="col-price">Unit price</th>
                        <th class="col-disc">Disc %</th>
                        <th class="col-vat">VAT %</th>
                        <th class="col-total">Line total</th>
                        <th class="col-act"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in rows" :key="row.key">
                        <tr>
                            <td class="col-num"><span x-text="index + 1"></span></td>
                            <td class="col-desc">
                                <input type="hidden"
                                       :name="`items[${index}][product_id]`"
                                       :value="isCustom(row) ? '' : row.product_id">
                                <div class="po-create__desc-cell">
                                    <select class="po-create__input po-create__input--table po-create__input--cell"
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
                                    <input type="text"
                                           class="po-create__input po-create__input--table po-create__input--cell po-create__input--desc"
                                           x-model="row.description"
                                           :name="`items[${index}][description]`"
                                           :required="isCustom(row)"
                                           :placeholder="isCustom(row) ? 'What are you buying?' : 'Optional line note'">
                                </div>
                            </td>
                            <td class="col-qty">
                                <input type="text" inputmode="decimal"
                                       class="po-create__input po-create__input--cell po-create__input--num"
                                       x-model="row.quantity"
                                       :name="`items[${index}][quantity]`" required>
                            </td>
                            <td class="col-uom">
                                <select class="po-create__input po-create__input--cell po-create__input--uom"
                                        x-model="row.uom"
                                        :name="`items[${index}][uom]`"
                                        required>
                                    <template x-for="opt in uomOptions" :key="opt.value">
                                        <option :value="opt.value" x-text="opt.label"></option>
                                    </template>
                                </select>
                            </td>
                            <td class="col-price">
                                <div class="po-create__price-cell">
                                    <input type="text" inputmode="decimal"
                                           class="po-create__input po-create__input--cell po-create__input--num po-create__input--price"
                                           x-model="row.unit_price"
                                           :name="`items[${index}][unit_price]`" placeholder="0.00">
                                    <span class="po-create__currency-hint">{{ $currencyCode }}</span>
                                </div>
                            </td>
                            <td class="col-disc">
                                <input type="text" inputmode="decimal"
                                       class="po-create__input po-create__input--cell po-create__input--num"
                                       x-model="row.discount_percent"
                                       :name="`items[${index}][discount_percent]`" placeholder="0">
                            </td>
                            <td class="col-vat">
                                <input type="text" inputmode="decimal"
                                       class="po-create__input po-create__input--cell po-create__input--num"
                                       x-model="row.vat_rate"
                                       :name="`items[${index}][vat_rate]`" placeholder="0">
                            </td>
                            <td class="col-total">
                                <div class="po-create__line-total">
                                    <span class="po-create__line-total-label">{{ $currencyCode }}</span>
                                    <span class="po-create__line-total-value" x-text="formatMoney(lineTotal(row))"></span>
                                </div>
                            </td>
                            <td class="col-act">
                                <button type="button" @click="removeRow(index)" x-show="rows.length > 1"
                                        class="po-create__remove" title="Remove line">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <button type="button" @click="addRow()" class="po-create__add-row">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add line item
        </button>

        @error('items')<p class="po-create__error px-5 py-2">{{ $message }}</p>@enderror
        @error('items.*')<p class="po-create__error px-5 py-2">{{ $message }}</p>@enderror

        <footer class="po-create__summary">
            <dl class="po-create__summary-list">
                <div class="po-create__summary-row">
                    <dt>Subtotal</dt>
                    <dd>{{ $currencyCode }} <span x-text="formatMoney(summarySubtotal())"></span></dd>
                </div>
                <div class="po-create__summary-row">
                    <dt>Discount</dt>
                    <dd>− {{ $currencyCode }} <span x-text="formatMoney(summaryDiscount())"></span></dd>
                </div>
                <div class="po-create__summary-row">
                    <dt>VAT</dt>
                    <dd>{{ $currencyCode }} <span x-text="formatMoney(summaryVat())"></span></dd>
                </div>
                <div class="po-create__summary-row po-create__summary-row--total">
                    <dt>PO total</dt>
                    <dd>{{ $currencyCode }} <span x-text="formatMoney(grandTotal())"></span></dd>
                </div>
            </dl>
        </footer>
    </section>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('purchaseOrderForm', (config) => ({
                    products: config.products || [],
                    suppliers: config.suppliers || [],
                    uomOptions: config.uomOptions || [],
                    supplierMode: config.supplierMode || 'existing',
                    selectedSupplierId: config.selectedSupplierId || '',
                    newSupplier: config.newSupplier || {},
                    rows: (config.initialRows || []).map((row, index) => ({
                        key: Date.now() + index,
                        product_id: row.product_id === 'custom'
                            ? 'custom'
                            : (row.product_id ? String(row.product_id) : ''),
                        description: row.description || '',
                        uom: row.uom || 'piece',
                        quantity: row.quantity ?? 1,
                        unit_price: row.unit_price ?? '',
                        discount_percent: row.discount_percent ?? 0,
                        vat_rate: row.vat_rate ?? 0,
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
                    selectedSupplierName() {
                        if (this.supplierMode === 'new') {
                            return (this.newSupplier?.name || '').trim() || 'New vendor';
                        }
                        const match = (this.suppliers || []).find(
                            (s) => String(s.id) === String(this.selectedSupplierId)
                        );
                        return match?.name || 'Select supplier';
                    },
                    supplierInitial() {
                        const name = this.selectedSupplierName();
                        if (name === 'Select supplier' || name === 'New vendor') return '?';
                        return name.charAt(0).toUpperCase();
                    },
                    filledLineCount() {
                        return this.rows.filter((row) => {
                            if (this.isCustom(row)) return (row.description || '').trim() !== '';
                            return row.product_id !== '';
                        }).length;
                    },
                    onProductChange(row) {
                        if (this.isCustom(row)) {
                            if (! row.uom) row.uom = 'piece';
                            return;
                        }
                        const product = this.productFor(row);
                        if (! product) return;
                        if (! row.description) row.description = product.name;
                        if (product.uom_key) row.uom = product.uom_key;
                        if (row.unit_price === '' && product.standard_cost !== null) {
                            row.unit_price = product.standard_cost;
                        }
                        if (! row.vat_rate || row.vat_rate === 0) {
                            row.vat_rate = product.vat_rate ?? 0;
                        }
                    },
                    lineSubtotal(row) {
                        const qty = parseFloat(row.quantity) || 0;
                        const price = parseFloat(row.unit_price);
                        return Number.isNaN(price) ? 0 : qty * price;
                    },
                    lineDiscount(row) {
                        const pct = parseFloat(row.discount_percent) || 0;
                        return this.lineSubtotal(row) * (pct / 100);
                    },
                    lineNet(row) {
                        return this.lineSubtotal(row) - this.lineDiscount(row);
                    },
                    lineVat(row) {
                        const rate = parseFloat(row.vat_rate) || 0;
                        return this.lineNet(row) * (rate / 100);
                    },
                    lineTotal(row) {
                        return this.lineNet(row) + this.lineVat(row);
                    },
                    summarySubtotal() {
                        return this.rows.reduce((sum, row) => sum + this.lineSubtotal(row), 0);
                    },
                    summaryDiscount() {
                        return this.rows.reduce((sum, row) => sum + this.lineDiscount(row), 0);
                    },
                    summaryVat() {
                        return this.rows.reduce((sum, row) => sum + this.lineVat(row), 0);
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
                            uom: 'piece',
                            quantity: 1,
                            unit_price: '',
                            discount_percent: 0,
                            vat_rate: 0,
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
