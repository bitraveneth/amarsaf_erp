export function registerProductionForm(Alpine) {
    Alpine.data('productionForm', (config) => ({
        currencyCode: config.currencyCode || 'BDT',
        products: config.products || [],
        previousRunsByProduct: config.previousRunsByProduct || {},
        materialRequirements: config.materialRequirements || {},
        productUnitCosts: config.productUnitCosts || {},
        productSkus: config.productSkus || {},
        bomCatalog: config.bomCatalog || {},
        selectedProductId: config.selectedProductId ? String(config.selectedProductId) : '',
        quantity: config.quantity ?? '',
        warehouseId: config.warehouseId ? String(config.warehouseId) : '',
        line: config.line || 'Line 1',
        shift: config.shift || 'Morning',
        supervisorId: config.supervisorId ? String(config.supervisorId) : '',
        status: config.status || 'confirmed',
        notes: config.notes || '',
        materialsReserved: config.materialsReserved || '',
        orderNumber: config.orderNumber || '',
        previewRunId: config.previewRunId ? String(config.previewRunId) : '',
        appliedRunId: config.appliedRunId ? String(config.appliedRunId) : '',
        initialRepeatRunId: config.initialRepeatRunId || null,
        useExistingBatch: config.useExistingBatch === true,
        init() {
            this.$nextTick(() => {
                if (this.initialRepeatRunId) {
                    this.previewRunId = String(this.initialRepeatRunId);
                    this.usePreviewRun(true);
                } else if (this.selectedProductId && !this.previewRunId) {
                    this.previewRunId = this.defaultPreviewRunId();
                }
                this.syncExternalWidgets();
            });
        },
        productLabel(product) {
            const sku = (product.sku || '').trim();
            const name = (product.name || '').trim();
            return sku ? `${sku} – ${name}` : name;
        },
        productMap() {
            return Object.fromEntries(this.products.map((p) => [String(p.id), p]));
        },
        selectedProductName() {
            const match = this.productMap()[String(this.selectedProductId)];
            return match ? this.productLabel(match) : 'Select finished product';
        },
        productInitial() {
            const name = this.selectedProductName();
            if (name === 'Select finished product') return '?';
            return name.charAt(0).toUpperCase();
        },
        runsForProduct() {
            if (!this.selectedProductId) return [];
            return this.previousRunsByProduct[String(this.selectedProductId)] || [];
        },
        runOptionLabel(run) {
            const batch = run.batch_code ? ` · ${run.batch_code}` : '';
            return `${run.code} · ${run.quantity} units${batch}`;
        },
        defaultPreviewRunId() {
            const runs = this.runsForProduct();
            return runs.length ? String(runs[0].id) : '';
        },
        previewRun() {
            if (!this.previewRunId) return null;
            return this.runsForProduct().find((item) => String(item.id) === String(this.previewRunId)) || null;
        },
        previewRunLines() {
            const run = this.previewRun();
            if (!run || !this.selectedProductId) return [];

            const components = this.materialRequirements[String(this.selectedProductId)] || [];
            const qty = parseFloat(run.quantity) || 0;

            return components.map((comp) => {
                const required = (parseFloat(comp.quantity_per_unit) || 0) * qty;
                const label = comp.sku ? `${comp.sku} — ${comp.name || ''}` : (comp.name || 'Material');

                return {
                    id: comp.product_id,
                    label,
                    qty: `${required.toFixed(2)} ${comp.uom || ''}`.trim(),
                };
            });
        },
        previewRunTotalCost() {
            const run = this.previewRun();
            if (!run) return 0;

            if (run.material_total_cost !== null && run.material_total_cost !== undefined) {
                return parseFloat(run.material_total_cost) || 0;
            }

            const unitCost = this.productUnitCosts[String(this.selectedProductId)] || 0;
            return unitCost * (parseFloat(run.quantity) || 0);
        },
        currentUnitCost() {
            const cost = this.productUnitCosts[String(this.selectedProductId)];
            return cost !== undefined && cost !== null ? cost : null;
        },
        currentTotalCost() {
            const unitCost = this.currentUnitCost();
            const qty = parseFloat(this.quantity) || 0;
            if (unitCost === null || !qty) return null;
            return unitCost * qty;
        },
        hasActiveBom() {
            return !!(this.bomCatalog[String(this.selectedProductId)] ?? null);
        },
        previewBatchCode() {
            if (!this.selectedProductId) return '—';
            const sku = (this.productSkus[String(this.selectedProductId)] || (`P${this.selectedProductId}`))
                .replace(/[^A-Z0-9]/gi, '')
                .toUpperCase();
            const dateKey = new Date().toISOString().slice(0, 10).replace(/-/g, '');
            return `${sku}-${dateKey}-001`;
        },
        formatMoney(value) {
            if (value === null || value === undefined || value === '') return '—';
            const amount = parseFloat(value);
            if (Number.isNaN(amount)) return '—';
            return amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        selectedRunLabel() {
            const run = this.runsForProduct().find((item) => String(item.id) === String(this.appliedRunId));
            return run ? `${run.code} · ${run.quantity} units` : '';
        },
        isPreviewApplied() {
            return !!this.appliedRunId && String(this.appliedRunId) === String(this.previewRunId);
        },
        onProductChange() {
            this.previewRunId = this.defaultPreviewRunId();
            this.appliedRunId = '';
            this.syncExternalWidgets();
        },
        usePreviewRun(focusQuantity = true) {
            const run = this.previewRun();
            if (!run) return;

            this.quantity = String(run.quantity || '');
            this.line = run.line || 'Line 1';
            this.shift = run.shift || 'Morning';
            this.warehouseId = run.warehouse_id ? String(run.warehouse_id) : this.warehouseId;
            this.supervisorId = run.supervisor_id ? String(run.supervisor_id) : '';
            this.notes = run.notes || '';
            this.materialsReserved = run.materials_reserved || '';
            this.appliedRunId = String(run.id);
            this.syncExternalWidgets();

            if (focusQuantity) {
                this.$nextTick(() => {
                    const quantityInput = document.getElementById('quantity');
                    quantityInput?.focus();
                    quantityInput?.select();
                });
            }
        },
        syncExternalWidgets() {
            window.dispatchEvent(new CustomEvent('production-form-sync'));
        },
    }));
}

export function initProductionFormWidgets() {
    const form = document.getElementById('production-create-form');
    if (!form) {
        return;
    }

    let config;
    try {
        config = JSON.parse(form.dataset.productionWidgets || '{}');
    } catch {
        return;
    }

    const {
        productUnitCosts = {},
        materialRequirements = {},
        warehouseStock = {},
        warehouseNames = {},
        bomCatalog = {},
        openBatchesByProduct = {},
        defaultWarehouseId = '',
        oldBatchId = null,
        bomCreateUrl = '',
    } = config;

    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity');
    const warehouseSelect = document.getElementById('warehouse_id');
    const materialsBody = document.getElementById('materials-required-body');
    const materialsSummary = document.getElementById('materials-required-summary');
    const bomStatusBanner = document.getElementById('bom-status-banner');
    const useExistingBatch = document.getElementById('use_existing_batch');
    const batchModeInput = document.getElementById('batch_mode');
    const batchSelect = document.getElementById('batch_id');

    function populateOpenBatches() {
        if (!batchSelect || !productSelect) return;

        const productId = productSelect.value;
        const batches = openBatchesByProduct[productId] || [];

        batchSelect.innerHTML = '<option value="">Select open batch for this product…</option>';
        batches.forEach((batch) => {
            const option = document.createElement('option');
            option.value = batch.id;
            option.textContent = `${batch.code}${batch.production_date ? ` · ${batch.production_date}` : ''}`;
            batchSelect.appendChild(option);
        });

        if (oldBatchId) {
            batchSelect.value = String(oldBatchId);
        }
    }

    function syncBatchMode() {
        const useExisting = useExistingBatch?.checked;
        if (batchModeInput) {
            batchModeInput.value = useExisting ? 'existing' : 'auto';
        }
        if (batchSelect) {
            batchSelect.disabled = !useExisting;
            if (!useExisting) {
                batchSelect.value = '';
            }
        }
    }

    function updateBomStatusBanner() {
        if (!bomStatusBanner) return;

        const productId = productSelect.value;
        if (!productId) {
            bomStatusBanner.className = 'rounded-2xl border px-5 py-4 text-sm border-gray-200 bg-gray-50 text-gray-600 dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-400';
            bomStatusBanner.innerHTML = 'Select a product to see whether an active manufacturing recipe is loaded.';
            return;
        }

        const bom = bomCatalog[productId] ?? null;
        if (bom) {
            bomStatusBanner.className = 'rounded-2xl border px-5 py-4 text-sm border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100';
            bomStatusBanner.innerHTML = `
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold">Active recipe loaded — no new BOM needed</p>
                        <p class="mt-1">${bom.name} · ${bom.items_count} component${bom.items_count === 1 ? '' : 's'}. Materials below are calculated from this recipe.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <a href="${bom.show_url}" class="inline-flex items-center rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 dark:border-emerald-500/40 dark:bg-emerald-950/40 dark:text-emerald-100">View recipe</a>
                        <a href="${bom.edit_url}" class="inline-flex items-center rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 dark:border-emerald-500/40 dark:bg-emerald-950/40 dark:text-emerald-100">Edit recipe</a>
                    </div>
                </div>`;
            return;
        }

        const createUrl = `${bomCreateUrl}?product_id=${encodeURIComponent(productId)}`;
        bomStatusBanner.className = 'rounded-2xl border px-5 py-4 text-sm border-amber-200 bg-amber-50 text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100';
        bomStatusBanner.innerHTML = `
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold">No active recipe for this product</p>
                    <p class="mt-1">You can still record production, but materials will not auto-calculate or deduct until a BOM exists.</p>
                </div>
                <a href="${createUrl}" class="inline-flex shrink-0 items-center rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">Create BOM</a>
            </div>`;
    }

    function updateMaterialRequirements() {
        const productId = productSelect.value;
        const quantity = parseFloat(quantityInput.value) || 0;
        const warehouseId = warehouseSelect.value || defaultWarehouseId;

        const components = materialRequirements[productId] ?? null;
        if (!materialsBody) return;

        materialsBody.innerHTML = '';

        const rawDetails = {};
        let labourRequired = 0;
        let utilitiesRequired = 0;
        let labourShortage = 0;
        let utilitiesShortage = 0;
        let hasComponents = false;

        if (!components || components.length === 0 || !quantity) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td colspan="5" class="px-4 py-8 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="h-12 w-12 text-gray-300 dark:text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            ${!productId ? 'Select a product' : !quantity ? 'Enter quantity' : 'No BOM defined for this product'}.
                        </p>
                    </div>
                </td>
            `;
            materialsBody.appendChild(row);

            if (materialsSummary) {
                materialsSummary.textContent = 'Select a product and quantity to see summary of required materials and services.';
            }
            return;
        }

        components.forEach((comp) => {
            const required = comp.quantity_per_unit * quantity;
            const available = warehouseStock[warehouseId]?.[comp.product_id] ?? 0;
            const shortage = Math.max(0, required - available);
            const warehouseName = warehouseNames[warehouseId] || 'Selected warehouse';
            const type = (comp.type || '').toString().toLowerCase();

            if (type === 'raw') {
                const key = comp.sku || comp.name || 'Raw material';
                rawDetails[key] = (rawDetails[key] || 0) + required;
            }

            if (comp.sku && comp.sku.startsWith('SV-LAB')) {
                labourRequired += required;
                if (shortage > 0) labourShortage += shortage;
            } else if (comp.sku && comp.sku.startsWith('SV-UTIL')) {
                utilitiesRequired += required;
                if (shortage > 0) utilitiesShortage += shortage;
            }

            const row = document.createElement('tr');
            row.className = shortage > 0 ? 'bg-error-50/30 dark:bg-error-500/5' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50';
            row.innerHTML = `
                <td class="px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            ${comp.sku ? `${comp.sku} — ` : ''}${comp.name ?? ''}
                        </p>
                        ${comp.type ? `<span class="text-xs text-gray-500 dark:text-gray-400">${comp.type}</span>` : ''}
                    </div>
                </td>
                <td class="px-4 py-3 text-right">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        ${required.toFixed(2)} ${comp.uom ?? ''}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">${warehouseName}</td>
                <td class="px-4 py-3 text-right">
                    <span class="text-sm ${available >= required ? 'text-gray-700 dark:text-gray-300' : 'text-error-600 dark:text-error-500'}">
                        ${available.toFixed(2)} ${comp.uom ?? ''}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    ${shortage > 0
                        ? `<span class="inline-flex items-center rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/20 dark:text-error-400">${shortage.toFixed(2)} ${comp.uom ?? ''}</span>`
                        : '<span class="text-xs text-success-600 dark:text-success-400">OK</span>'}
                </td>
            `;
            materialsBody.appendChild(row);
            hasComponents = true;
        });

        if (materialsSummary && hasComponents) {
            const lines = [];

            if (Object.keys(rawDetails).length) {
                const rawLine = Object.entries(rawDetails)
                    .map(([name, qty]) => `${qty.toFixed(2)} × ${name}`)
                    .join(', ');
                lines.push(`<span class="font-medium">Raw materials:</span> ${rawLine}`);
            }

            lines.push(`<span class="font-medium">Labour:</span> ${labourRequired.toFixed(2)} day (shortage: ${labourShortage.toFixed(2)})`);
            lines.push(`<span class="font-medium">Utilities:</span> ${utilitiesRequired.toFixed(2)} shift (shortage: ${utilitiesShortage.toFixed(2)})`);
            materialsSummary.innerHTML = lines.join('<br>');
        } else if (materialsSummary) {
            materialsSummary.textContent = 'Select a product and quantity to see summary of required materials and services.';
        }
    }

    function refreshProductionWidgets() {
        updateBomStatusBanner();
        populateOpenBatches();
        syncBatchMode();
        updateMaterialRequirements();
    }

    window.addEventListener('production-form-sync', refreshProductionWidgets);
    useExistingBatch?.addEventListener('change', syncBatchMode);
    productSelect?.addEventListener('change', refreshProductionWidgets);
    quantityInput?.addEventListener('input', updateMaterialRequirements);
    warehouseSelect?.addEventListener('change', updateMaterialRequirements);

    refreshProductionWidgets();

    const generateBtn = document.getElementById('btn-generate-order-number');
    const orderNumberInput = document.getElementById('order_number');

    generateBtn?.addEventListener('click', () => {
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        orderNumberInput.value = `PO-${year}${month}${day}-${random}`;
        orderNumberInput.dispatchEvent(new Event('input', { bubbles: true }));
    });
}
