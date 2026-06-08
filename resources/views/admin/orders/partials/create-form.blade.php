@php
    $currencyCode = config('app.currency', 'BDT');
    $productOptions = $products->map(function ($p) use ($availability) {
        return [
            'id' => $p->id,
            'sku' => $p->sku,
            'name' => $p->name,
            'stock' => $availability[$p->id] ?? 0,
        ];
    })->values();
@endphp

<div class="so-create__grid">
    <section class="so-create__card so-create__card--agent">
        <header class="so-create__card-head">
            <div>
                <h2 class="so-create__card-title">Agent &amp; delivery</h2>
                <p class="so-create__card-desc">Who is ordering and where goods should go.</p>
            </div>
        </header>

        <div class="so-create__fields">
            <div class="so-create__field so-create__field--wide">
                <label for="agent_id" class="so-create__label">Agent <span class="so-create__req">*</span></label>
                <select id="agent_id" name="agent_id" required class="so-create__input">
                    <option value="">Select agent…</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}"{{ old('agent_id') == $agent->id ? ' selected' : '' }}>
                            {{ $agent->name }} · {{ $agent->zone ?? '—' }} · {{ $agent->area ?? '' }}
                        </option>
                    @endforeach
                </select>
                @error('agent_id')<p class="so-create__error">{{ $message }}</p>@enderror
            </div>

            <div class="so-create__field">
                <label for="agent_reference" class="so-create__label">Agent PO / reference</label>
                <input type="text" id="agent_reference" name="agent_reference"
                       value="{{ old('agent_reference') }}" placeholder="e.g. PO-2025-001"
                       class="so-create__input">
                @error('agent_reference')<p class="so-create__error">{{ $message }}</p>@enderror
            </div>

            <div class="so-create__field">
                <label for="order_type" class="so-create__label">Order type</label>
                <select id="order_type" name="order_type" class="so-create__input">
                    @foreach(['regular', 'bulk', 'sample', 'return'] as $type)
                        <option value="{{ $type }}"{{ old('order_type', 'regular') == $type ? ' selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
                <p class="so-create__hint">Sample = non-billable. Return = pickup only, no stock reserve.</p>
            </div>

            <div class="so-create__field">
                <label for="delivery_date" class="so-create__label">Delivery date</label>
                <input type="date" id="delivery_date" name="delivery_date"
                       value="{{ old('delivery_date', now()->addDays(7)->format('Y-m-d')) }}"
                       class="so-create__input">
                @error('delivery_date')<p class="so-create__error">{{ $message }}</p>@enderror
            </div>

            <div class="so-create__field">
                <label for="payment_mode" class="so-create__label">Payment mode</label>
                <select id="payment_mode" name="payment_mode" class="so-create__input">
                    <option value="">Select payment mode…</option>
                    @foreach(['cash' => 'Cash', 'credit' => 'Credit', 'bkash' => 'bKash', 'bank_transfer' => 'Bank Transfer'] as $value => $label)
                        <option value="{{ $value }}"{{ old('payment_mode') === $value ? ' selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="so-create__field">
                <label for="delivery_contact_name" class="so-create__label">Delivery contact</label>
                <input type="text" id="delivery_contact_name" name="delivery_contact_name"
                       value="{{ old('delivery_contact_name') }}" placeholder="Contact name"
                       class="so-create__input">
            </div>

            <div class="so-create__field">
                <label for="delivery_contact_phone" class="so-create__label">Contact phone</label>
                <input type="text" id="delivery_contact_phone" name="delivery_contact_phone"
                       value="{{ old('delivery_contact_phone') }}" placeholder="+880 1XXX-XXXXXX"
                       class="so-create__input">
            </div>

            <div class="so-create__field so-create__field--wide">
                <label for="delivery_address" class="so-create__label">Delivery address</label>
                <textarea id="delivery_address" name="delivery_address" rows="2"
                          placeholder="Street, city, postal code — leave blank for agent default"
                          class="so-create__input so-create__input--area">{{ old('delivery_address') }}</textarea>
            </div>

            <div class="so-create__field so-create__field--wide">
                <label for="notes" class="so-create__label">Notes</label>
                <textarea id="notes" name="notes" rows="2"
                          placeholder="Special instructions or delivery notes…"
                          class="so-create__input so-create__input--area">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="so-create__agent-preview">
            <span class="erp-po-index-supplier__avatar" id="so-agent-initial">?</span>
            <div>
                <p class="so-create__preview-name" id="so-agent-name">Select agent</p>
                <p class="so-create__preview-meta" id="so-line-count">0 lines on order</p>
            </div>
        </div>
    </section>

    <section class="so-create__card so-create__card--lines">
        <header class="so-create__card-head so-create__card-head--border">
            <div>
                <h2 class="so-create__card-title">Line items</h2>
                <p class="so-create__card-desc">Finished products, quantities, and negotiated prices.</p>
            </div>
            <span class="so-create__badge" id="so-line-badge">1 line</span>
        </header>

        <div class="so-create__table-scroll">
            <table class="so-create__table">
                <colgroup>
                    <col class="col-num">
                    <col class="col-product">
                    <col class="col-qty">
                    <col class="col-price">
                    <col class="col-total">
                    <col class="col-act">
                </colgroup>
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th class="col-product">Product</th>
                        <th class="col-qty">Qty</th>
                        <th class="col-price">Unit price</th>
                        <th class="col-total">Line total</th>
                        <th class="col-act"></th>
                    </tr>
                </thead>
                <tbody id="order-items">
                    <tr class="so-create__line-row">
                        <td class="col-num"><span class="so-create__row-num">1</span></td>
                        <td class="col-product">
                            <select name="items[0][product_id]" data-product-select required
                                    class="so-create__input so-create__input--cell so-create__input--product">
                                <option value="">Select SKU…</option>
                                @foreach($products as $product)
                                    @php $available = $availability[$product->id] ?? 0; @endphp
                                    <option value="{{ $product->id }}" data-stock="{{ $available }}">
                                        {{ $product->sku }} — {{ $product->name }} ({{ number_format($available, 0) }} avail.)
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="col-qty">
                            <input type="number" name="items[0][quantity]" value="1" min="1" required
                                   data-qty-input
                                   class="so-create__input so-create__input--cell so-create__input--num">
                        </td>
                        <td class="col-price">
                            <div class="so-create__price-cell">
                                <input type="number" name="items[0][unit_price]" step="0.01" min="0"
                                       data-unit-price required placeholder="0.00"
                                       class="so-create__input so-create__input--cell so-create__input--num so-create__input--price">
                                <span class="so-create__currency-hint">{{ $currencyCode }}</span>
                            </div>
                        </td>
                        <td class="col-total">
                            <div class="so-create__line-total">
                                <span class="so-create__line-total-label">{{ $currencyCode }}</span>
                                <span class="so-create__line-total-value" data-line-total>0.00</span>
                            </div>
                        </td>
                        <td class="col-act"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button type="button" id="add-item" class="so-create__add-row">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add line item
        </button>

        @error('items')<p class="so-create__error px-5 py-2">{{ $message }}</p>@enderror
        @error('items.*')<p class="so-create__error px-5 py-2">{{ $message }}</p>@enderror

        <footer class="so-create__summary">
            <dl class="so-create__summary-list">
                <div class="so-create__summary-row so-create__summary-row--total">
                    <dt>Order total</dt>
                    <dd>{{ $currencyCode }} <span id="so-order-total">0.00</span></dd>
                </div>
            </dl>
        </footer>
    </section>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.agentPricing = @json($priceLists);
                window.productBasePrices = @json($products->pluck('base_price', 'id'));

                const itemsWrapper = document.getElementById('order-items');
                const addBtn = document.getElementById('add-item');
                const agentSelect = document.getElementById('agent_id');
                const agentNameEl = document.getElementById('so-agent-name');
                const agentInitialEl = document.getElementById('so-agent-initial');
                const lineBadge = document.getElementById('so-line-badge');
                const lineCountEl = document.getElementById('so-line-count');
                const orderTotalEl = document.getElementById('so-order-total');
                let index = 1;

                const productOptions = @json($productOptions);

                function formatMoney(value) {
                    return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                function rowLineTotal(row) {
                    const qty = parseFloat(row.querySelector('[data-qty-input]')?.value) || 0;
                    const price = parseFloat(row.querySelector('[data-unit-price]')?.value);
                    if (Number.isNaN(price)) return 0;
                    return qty * price;
                }

                function updateTotals() {
                    const rows = itemsWrapper.querySelectorAll('.so-create__line-row');
                    let grand = 0;
                    rows.forEach((row) => {
                        const total = rowLineTotal(row);
                        grand += total;
                        const totalEl = row.querySelector('[data-line-total]');
                        if (totalEl) totalEl.textContent = formatMoney(total);
                    });
                    if (orderTotalEl) orderTotalEl.textContent = formatMoney(grand);

                    const count = rows.length;
                    const label = `${count} line${count === 1 ? '' : 's'}`;
                    if (lineBadge) lineBadge.textContent = label;
                    if (lineCountEl) lineCountEl.textContent = `${count} on order`;
                }

                function renumberRows() {
                    itemsWrapper.querySelectorAll('.so-create__line-row').forEach((row, i) => {
                        const num = row.querySelector('.so-create__row-num');
                        if (num) num.textContent = String(i + 1);
                    });
                }

                function refreshPrices() {
                    const agentId = agentSelect?.value || null;
                    const agentPrices = (window.agentPricing && agentId && window.agentPricing[agentId]) || {};

                    itemsWrapper.querySelectorAll('[data-product-select]').forEach((select) => {
                        const row = select.closest('.so-create__line-row');
                        const priceInput = row?.querySelector('[data-unit-price]');
                        if (! priceInput || ! select.value) return;

                        let price = null;
                        if (Object.prototype.hasOwnProperty.call(agentPrices, select.value)) {
                            price = agentPrices[select.value];
                        } else if (window.productBasePrices && Object.prototype.hasOwnProperty.call(window.productBasePrices, select.value)) {
                            price = window.productBasePrices[select.value];
                        }

                        if (price !== null && price !== undefined && price !== '') {
                            priceInput.value = price;
                        }
                    });
                    updateTotals();
                }

                function updateAgentPreview() {
                    if (! agentSelect || ! agentNameEl || ! agentInitialEl) return;
                    const opt = agentSelect.options[agentSelect.selectedIndex];
                    const name = agentSelect.value ? opt.text.split('·')[0].trim() : 'Select agent';
                    agentNameEl.textContent = name;
                    agentInitialEl.textContent = agentSelect.value ? name.charAt(0).toUpperCase() : '?';
                }

                function bindRow(row) {
                    row.querySelector('[data-product-select]')?.addEventListener('change', refreshPrices);
                    row.querySelector('[data-qty-input]')?.addEventListener('input', updateTotals);
                    row.querySelector('[data-unit-price]')?.addEventListener('input', updateTotals);

                    const removeBtn = row.querySelector('[data-remove-row]');
                    removeBtn?.addEventListener('click', () => {
                        if (itemsWrapper.querySelectorAll('.so-create__line-row').length <= 1) return;
                        row.remove();
                        renumberRows();
                        updateTotals();
                    });
                }

                function buildProductSelect(name) {
                    let html = `<select name="${name}" data-product-select required class="so-create__input so-create__input--cell so-create__input--product"><option value="">Select SKU…</option>`;
                    productOptions.forEach((p) => {
                        html += `<option value="${p.id}" data-stock="${p.stock}">${p.sku} — ${p.name} (${Number(p.stock).toLocaleString()} avail.)</option>`;
                    });
                    html += '</select>';
                    return html;
                }

                addBtn?.addEventListener('click', () => {
                    const row = document.createElement('tr');
                    row.className = 'so-create__line-row';
                    row.innerHTML = `
                        <td class="col-num"><span class="so-create__row-num">${index + 1}</span></td>
                        <td class="col-product">${buildProductSelect(`items[${index}][product_id]`)}</td>
                        <td class="col-qty">
                            <input type="number" name="items[${index}][quantity]" value="1" min="1" required data-qty-input
                                   class="so-create__input so-create__input--cell so-create__input--num">
                        </td>
                        <td class="col-price">
                            <div class="so-create__price-cell">
                                <input type="number" name="items[${index}][unit_price]" step="0.01" min="0" data-unit-price required placeholder="0.00"
                                       class="so-create__input so-create__input--cell so-create__input--num so-create__input--price">
                                <span class="so-create__currency-hint">{{ $currencyCode }}</span>
                            </div>
                        </td>
                        <td class="col-total">
                            <div class="so-create__line-total">
                                <span class="so-create__line-total-label">{{ $currencyCode }}</span>
                                <span class="so-create__line-total-value" data-line-total>0.00</span>
                            </div>
                        </td>
                        <td class="col-act">
                            <button type="button" data-remove-row class="so-create__remove" title="Remove line">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </td>
                    `;
                    itemsWrapper.appendChild(row);
                    bindRow(row);
                    index += 1;
                    renumberRows();
                    refreshPrices();
                });

                agentSelect?.addEventListener('change', () => {
                    updateAgentPreview();
                    refreshPrices();
                });

                itemsWrapper.querySelectorAll('.so-create__line-row').forEach(bindRow);
                updateAgentPreview();
                refreshPrices();
            });
        </script>
    @endpush
@endonce
