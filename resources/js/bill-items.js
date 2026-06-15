function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function buildProductOptions(productOptions) {
    const options = ['<option value="">None</option>'];

    productOptions.forEach((product) => {
        options.push(
            `<option value="${product.id}" data-default-vat="${product.defaultVat ?? 0}">${escapeHtml(product.label)}</option>`
        );
    });

    return options.join('');
}

export function initBillItems() {
    const wrapper = document.getElementById('bill-items');
    if (!wrapper) {
        return;
    }

    const addBtn = document.getElementById('add-bill-item');
    const itemCount = document.getElementById('item-count');
    let index = parseInt(wrapper.dataset.startIndex || '1', 10);
    const currency = wrapper.dataset.currency || 'BDT';

    let productOptions = [];
    try {
        productOptions = JSON.parse(wrapper.dataset.productOptions || '[]');
    } catch {
        productOptions = [];
    }

    const productOptionsHtml = buildProductOptions(productOptions);

    function updateItemCount() {
        const count = wrapper.children.length;
        if (itemCount) {
            itemCount.textContent = `${count} ${count === 1 ? 'item' : 'items'}`;
        }
    }

    function bindRemoveButtons() {
        wrapper.querySelectorAll('.bill-item-remove').forEach((btn) => {
            btn.onclick = () => {
                const row = btn.closest('.bill-item-row');
                if (!row) {
                    return;
                }

                row.remove();
                updateItemCount();

                wrapper.querySelectorAll('.bill-item-row').forEach((itemRow, idx) => {
                    const numberBadge = itemRow.querySelector('.flex.h-8.w-8 span');
                    if (numberBadge) {
                        numberBadge.textContent = String(idx + 1);
                    }

                    const title = itemRow.querySelector('h4');
                    if (title) {
                        title.textContent = `Line Item ${idx + 1}`;
                    }

                    itemRow.querySelectorAll('input[name^="items["], select[name^="items["]').forEach((input) => {
                        const name = input.getAttribute('name');
                        if (!name) return;
                        input.setAttribute('name', name.replace(/items\[\d+\]/, `items[${idx}]`));
                    });
                });
            };
        });
    }

    function calculateLineTotal(row) {
        const qty = row.querySelector('input[name*="[quantity]"]')?.value || 0;
        const price = row.querySelector('input[name*="[unit_price]"]')?.value || 0;
        const total = parseFloat(qty) * parseFloat(price);
        const totalEl = row.querySelector('.line-total-display');

        if (totalEl) {
            totalEl.textContent = `${currency} ${total.toFixed(2)}`;
        }

        return total;
    }

    function calculateBillTotal() {
        let subtotal = 0;
        let vatTotal = 0;

        wrapper.querySelectorAll('.bill-item-row').forEach((row) => {
            subtotal += calculateLineTotal(row);

            const qty = parseFloat(row.querySelector('input[name*="[quantity]"]')?.value || 0);
            const price = parseFloat(row.querySelector('input[name*="[unit_price]"]')?.value || 0);
            const vatRate = parseFloat(row.querySelector('input[name*="[vat_rate]"]')?.value || 0);
            vatTotal += qty * price * (vatRate / 100);
        });

        const subtotalEl = document.getElementById('bill-subtotal');
        const vatEl = document.getElementById('bill-vat');
        const totalEl = document.getElementById('bill-total');

        if (subtotalEl) {
            subtotalEl.textContent = `${currency} ${subtotal.toFixed(2)}`;
        }

        if (vatEl) {
            vatEl.textContent = `VAT: ${currency} ${vatTotal.toFixed(2)}`;
        }

        if (totalEl) {
            totalEl.textContent = `Total: ${currency} ${(subtotal + vatTotal).toFixed(2)}`;
        }
    }

    function bindRowCalculations(row) {
        row.querySelectorAll('input[name*="[quantity]"], input[name*="[unit_price]"], input[name*="[vat_rate]"]').forEach((input) => {
            input.addEventListener('input', () => calculateBillTotal());
        });

        row.querySelector('select[name*="[product_id]"]')?.addEventListener('change', (event) => {
            const selected = event.target.selectedOptions[0];
            const vatInput = row.querySelector('input[name*="[vat_rate]"]');

            if (vatInput && selected && parseFloat(vatInput.value || 0) === 0) {
                vatInput.value = selected.dataset.defaultVat || 0;
                calculateBillTotal();
            }
        });
    }

    addBtn?.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'bill-item-row rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800/50';
        row.innerHTML = `
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/20">
                        <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">${index + 1}</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Line Item ${index + 1}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Material or service</p>
                    </div>
                </div>
                <button type="button" class="bill-item-remove erp-btn-action-danger">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Remove
                </button>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Description <span class="text-error-500">*</span>
                    </label>
                    <input type="text" name="items[${index}][description]" placeholder="e.g., PET Bottle 500ml, Packaging Service, etc." required
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Product (Optional)</label>
                    <select name="items[${index}][product_id]"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        ${productOptionsHtml}
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity <span class="text-error-500">*</span></label>
                    <input type="number" name="items[${index}][quantity]" min="1" value="1" required
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Unit Price <span class="text-error-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">${currency}</span>
                        <input type="number" name="items[${index}][unit_price]" step="0.01" min="0" required placeholder="0.00"
                               class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">VAT Rate %</label>
                    <input type="number" name="items[${index}][vat_rate]" step="0.01" min="0" max="100" value="0" placeholder="0.00"
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Line Total</label>
                    <div class="line-total-display flex h-10 items-center rounded-lg bg-gray-100 px-4 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        ${currency} 0.00
                    </div>
                </div>
            </div>
        `;

        wrapper.appendChild(row);
        index += 1;
        bindRemoveButtons();
        bindRowCalculations(row);
        updateItemCount();
        calculateBillTotal();
    });

    bindRemoveButtons();
    updateItemCount();

    wrapper.querySelectorAll('.bill-item-row').forEach((row) => bindRowCalculations(row));
    calculateBillTotal();
}
