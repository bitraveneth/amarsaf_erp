@php
    $currencyCode = $currencyCode ?? config('app.currency', 'BDT');
@endphp

<div class="po-create__grid">
    <section class="po-create__card po-create__card--supplier">
        <header class="po-create__card-head">
            <div>
                <h2 class="po-create__card-title">Carrier &amp; dates</h2>
                <p class="po-create__card-desc">Transport vendor, bill dates, and trip details.</p>
            </div>
            <a href="{{ route('admin.logistics.carriers.create') }}" class="text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400 shrink-0">+ Add carrier</a>
        </header>

        <div class="po-create__fields">
            <div class="po-create__field po-create__field--wide">
                <label for="transport_carrier_id" class="po-create__label">Carrier <span class="po-create__req">*</span></label>
                <select id="transport_carrier_id" name="transport_carrier_id" x-model="selectedCarrierId" required class="po-create__input">
                    <option value="">Select carrier…</option>
                    <template x-for="carrier in carriers" :key="carrier.id">
                        <option :value="String(carrier.id)" x-text="carrier.name"></option>
                    </template>
                </select>
                @error('transport_carrier_id')<p class="po-create__error">{{ $message }}</p>@enderror
            </div>

            <div class="po-create__field">
                <label for="number" class="po-create__label">Vendor invoice no.</label>
                <input type="text" id="number" name="number" x-model="vendorNumber" class="po-create__input" placeholder="Carrier invoice ref">
            </div>

            <div class="po-create__field">
                <label for="bill_date" class="po-create__label">Bill date <span class="po-create__req">*</span></label>
                <input type="date" id="bill_date" name="bill_date" x-model="billDate" required class="po-create__input">
                @error('bill_date')<p class="po-create__error">{{ $message }}</p>@enderror
            </div>

            <div class="po-create__field">
                <label for="due_date" class="po-create__label">Due date</label>
                <input type="date" id="due_date" name="due_date" x-model="dueDate" class="po-create__input">
            </div>

            <div class="po-create__field">
                <label for="service_type" class="po-create__label">Service type</label>
                <select id="service_type" name="service_type" x-model="serviceType" class="po-create__input">
                    @foreach($serviceTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="po-create__field">
                <label for="delivery_route_id" class="po-create__label">Route</label>
                <select id="delivery_route_id" name="delivery_route_id" x-model="routeId" class="po-create__input">
                    <option value="">—</option>
                    <template x-for="route in routes" :key="route.id">
                        <option :value="String(route.id)" x-text="route.name"></option>
                    </template>
                </select>
            </div>

            <div class="po-create__field">
                <label for="trip_date" class="po-create__label">Trip date</label>
                <input type="date" id="trip_date" name="trip_date" x-model="tripDate" class="po-create__input">
            </div>

            <div class="po-create__field po-create__field--wide">
                <label for="notes" class="po-create__label">Notes</label>
                <input type="text" id="notes" name="notes" x-model="notes" class="po-create__input" placeholder="Trip ref, loading terms…">
            </div>
        </div>

        <div class="po-create__supplier-preview">
            <span class="erp-po-index-supplier__avatar" x-text="carrierInitial()"></span>
            <div>
                <p class="po-create__preview-name" x-text="selectedCarrierName()"></p>
                <p class="po-create__preview-meta" x-text="`${filledLineCount()} of ${rows.length} lines filled`"></p>
            </div>
        </div>
    </section>

    <section class="po-create__card po-create__card--lines">
        <header class="po-create__card-head po-create__card-head--border">
            <div>
                <h2 class="po-create__card-title">Line items</h2>
                <p class="po-create__card-desc">Freight, courier, loading, demurrage, and other transport charges.</p>
            </div>
            <span class="po-create__badge" x-text="`${rows.length} line${rows.length === 1 ? '' : 's'}`"></span>
        </header>

        <div class="po-create__table-scroll">
            <table class="po-create__table po-create__table--logistics-bill">
                <colgroup>
                    <col class="col-num">
                    <col class="col-desc">
                    <col class="col-total">
                    <col class="col-act">
                </colgroup>
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th class="col-desc">Description</th>
                        <th class="col-total">Amount</th>
                        <th class="col-act"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in rows" :key="row.key">
                        <tr>
                            <td class="col-num"><span x-text="index + 1"></span></td>
                            <td class="col-desc">
                                <input type="text"
                                       class="po-create__input po-create__input--table po-create__input--cell po-create__input--desc"
                                       x-model="row.description"
                                       :name="`lines[${index}][description]`"
                                       required
                                       placeholder="e.g. Chattogram route freight">
                            </td>
                            <td class="col-total">
                                <div class="po-create__line-total po-create__line-total--input">
                                    <span class="po-create__line-total-label">{{ $currencyCode }}</span>
                                    <input type="text"
                                           inputmode="decimal"
                                           class="po-create__input po-create__input--cell po-create__input--num po-create__input--price"
                                           x-model="row.amount"
                                           :name="`lines[${index}][amount]`"
                                           placeholder="0.00">
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

        @error('lines')<p class="po-create__error px-5 py-2">{{ $message }}</p>@enderror
        @error('lines.*')<p class="po-create__error px-5 py-2">{{ $message }}</p>@enderror

        <div class="px-5 pb-2">
            <div class="po-create__field max-w-xs">
                <label for="vat_amount" class="po-create__label">Bill VAT ({{ $currencyCode }})</label>
                <input type="text" inputmode="decimal" id="vat_amount" name="vat_amount"
                       x-model="vatAmount" class="po-create__input" placeholder="0.00">
            </div>
        </div>

        <footer class="po-create__summary">
            <dl class="po-create__summary-list">
                <div class="po-create__summary-row">
                    <dt>Subtotal</dt>
                    <dd>{{ $currencyCode }} <span x-text="formatMoney(summarySubtotal())"></span></dd>
                </div>
                <div class="po-create__summary-row">
                    <dt>VAT</dt>
                    <dd>{{ $currencyCode }} <span x-text="formatMoney(summaryVat())"></span></dd>
                </div>
                <div class="po-create__summary-row po-create__summary-row--total">
                    <dt>Bill total</dt>
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
                Alpine.data('logisticsBillForm', (config) => ({
                    carriers: config.carriers || [],
                    routes: config.routes || [],
                    selectedCarrierId: config.selectedCarrierId || '',
                    billDate: config.billDate || '',
                    dueDate: config.dueDate || '',
                    vendorNumber: config.vendorNumber || '',
                    serviceType: config.serviceType || 'external_freight',
                    routeId: config.routeId || '',
                    tripDate: config.tripDate || '',
                    notes: config.notes || '',
                    vatAmount: config.vatAmount ?? 0,
                    rows: (config.initialRows || []).map((row, index) => ({
                        key: Date.now() + index,
                        description: row.description || '',
                        amount: row.amount ?? '',
                    })),
                    selectedCarrierName() {
                        const match = (this.carriers || []).find(
                            (carrier) => String(carrier.id) === String(this.selectedCarrierId)
                        );
                        return match?.name || 'Select carrier';
                    },
                    carrierInitial() {
                        const name = this.selectedCarrierName();
                        if (name === 'Select carrier') return '?';
                        return name.charAt(0).toUpperCase();
                    },
                    filledLineCount() {
                        return this.rows.filter((row) => (row.description || '').trim() !== '' && parseFloat(row.amount) > 0).length;
                    },
                    lineAmount(row) {
                        const value = parseFloat(row.amount);
                        return Number.isNaN(value) ? 0 : value;
                    },
                    summarySubtotal() {
                        return this.rows.reduce((sum, row) => sum + this.lineAmount(row), 0);
                    },
                    summaryVat() {
                        const value = parseFloat(this.vatAmount);
                        return Number.isNaN(value) ? 0 : value;
                    },
                    grandTotal() {
                        return this.summarySubtotal() + this.summaryVat();
                    },
                    formatMoney(value) {
                        return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },
                    addRow() {
                        this.rows.push({
                            key: Date.now() + Math.random(),
                            description: '',
                            amount: '',
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
