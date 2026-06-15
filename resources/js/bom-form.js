export function registerBomForm(Alpine) {
    Alpine.data('bomForm', (config) => ({
        currencyCode: config.currencyCode || 'BDT',
        products: config.products || [],
        materials: config.materials || [],
        activeBomsByProduct: config.activeBomsByProduct || {},
        productRecipesByProduct: config.productRecipesByProduct || {},
        productBomCounts: config.productBomCounts || {},
        selectedProductId: config.selectedProductId || '',
        bomName: config.bomName || '',
        autoName: config.autoName !== false,
        notes: config.notes || '',
        isActive: config.isActive !== false,
        materialUnitCost: config.materialUnitCost ?? '',
        lockProduct: config.lockProduct === true,
        materialSearch: config.materialSearch || '',
        copyFromBomId: config.copyFromBomId || '',
        previewRecipeId: config.copyFromBomId || '',
        rows: (config.initialRows || []).map((row, index) => {
            const componentId = row.component_product_id ? String(row.component_product_id) : '';

            return {
                key: Date.now() + index,
                component_product_id: componentId,
                component_label: row.component_label || '',
                pickMaterial: !componentId,
                quantity: row.quantity ?? 1,
                unit_cost: row.unit_cost ?? '',
                unit: row.unit || '',
            };
        }),
        blankRow() {
            return {
                key: Date.now() + Math.random(),
                component_product_id: '',
                component_label: '',
                pickMaterial: true,
                quantity: 1,
                unit_cost: '',
                unit: '',
            };
        },
        componentLabelFromItem(item) {
            const sku = (item.component_sku || '').trim();
            const name = (item.component_name || '').trim();
            if (sku && name) return `${sku} – ${name}`;
            if (name) return name;
            if (sku) return sku;

            const material = this.materialMap()[String(item.component_product_id || '')];
            return material ? this.materialLabel(material) : '';
        },
        rowMaterialLabel(row) {
            if (row.component_label) return row.component_label;

            const material = this.materialMap()[String(row.component_product_id)];
            if (material) return this.materialLabel(material);

            return row.component_product_id ? `Product #${row.component_product_id}` : 'Select material…';
        },
        productLabel(product) {
            const sku = (product.sku || '').trim();
            const name = (product.name || '').trim();
            return sku ? `${sku} – ${name}` : name;
        },
        materialLabel(material) {
            const sku = (material.sku || '').trim();
            const name = (material.name || '').trim();
            return sku ? `${sku} – ${name}` : name;
        },
        materialMap() {
            return Object.fromEntries(this.materials.map((m) => [String(m.id), m]));
        },
        productMap() {
            return Object.fromEntries(this.products.map((p) => [String(p.id), p]));
        },
        activeBom() {
            if (!this.selectedProductId) return null;
            return this.activeBomsByProduct[String(this.selectedProductId)] || null;
        },
        recipesForProduct() {
            if (!this.selectedProductId) return [];
            return this.productRecipesByProduct[String(this.selectedProductId)] || [];
        },
        recipeOptionLabel(recipe) {
            const active = recipe.is_active ? ' · Active' : '';
            return `${recipe.code} · ${recipe.name}${active}`;
        },
        previewRecipe() {
            if (!this.previewRecipeId) return null;
            return (
                this.recipesForProduct().find((item) => String(item.id) === String(this.previewRecipeId))
                || this.findRecipeTemplate(this.previewRecipeId)
            );
        },
        previewRecipeLines() {
            const recipe = this.previewRecipe();
            if (!recipe) return [];

            return (recipe.items || []).map((item) => {
                const material = this.materialMap()[String(item.component_product_id)];
                const qty = parseFloat(item.quantity) || 0;
                const cost = parseFloat(item.unit_cost);
                const lineCost = Number.isNaN(cost) ? 0 : qty * cost;
                const unit = item.unit || material?.uom || '';
                const formatQty = (value) => Number(value).toLocaleString(undefined, { maximumFractionDigits: 4 });
                const fallbackName = [item.component_sku, item.component_name].filter(Boolean).join(' – ')
                    || item.component_name
                    || 'Material';

                return {
                    id: item.component_product_id,
                    label: material ? this.materialLabel(material) : fallbackName,
                    qty: `${formatQty(qty)} ${unit}`.trim(),
                    cost: `${this.currencyCode} ${this.formatMoney(lineCost)}`,
                };
            });
        },
        previewRecipeTotalCost() {
            const recipe = this.previewRecipe();
            if (!recipe) return 0;

            if (recipe.material_unit_cost !== null
                && recipe.material_unit_cost !== undefined
                && recipe.material_unit_cost !== '') {
                return parseFloat(recipe.material_unit_cost) || 0;
            }

            return (recipe.items || []).reduce((sum, item) => {
                const qty = parseFloat(item.quantity) || 0;
                const cost = parseFloat(item.unit_cost);
                return sum + (Number.isNaN(cost) ? 0 : qty * cost);
            }, 0);
        },
        defaultPreviewRecipeId() {
            const recipes = this.recipesForProduct();
            const active = recipes.find((recipe) => recipe.is_active);
            if (active) return String(active.id);
            if (recipes.length > 0) return String(recipes[0].id);
            return '';
        },
        isPreviewApplied() {
            return String(this.copyFromBomId || '') === String(this.previewRecipeId || '');
        },
        usePreviewRecipe() {
            this.selectRecipeSource(this.previewRecipeId);
        },
        useBlankRecipe() {
            this.previewRecipeId = '';
            this.selectRecipeSource('');
        },
        draftSummary() {
            const parts = [
                `${this.filledLineCount()} of ${this.rows.length} components`,
                `${this.formatMoney(this.estimatedCost())} ${this.currencyCode}/unit`,
            ];

            if (this.copyFromBomId) {
                parts.unshift(`From ${this.selectedSourceLabel()}`);
            }

            return parts.join(' · ');
        },
        selectedSourceLabel() {
            const recipe = this.recipesForProduct().find(
                (item) => String(item.id) === String(this.copyFromBomId)
            );
            return recipe ? `${recipe.code} · ${recipe.name}` : '';
        },
        findRecipeTemplate(bomId) {
            if (!bomId) return null;
            for (const productId of Object.keys(this.productRecipesByProduct)) {
                const match = (this.productRecipesByProduct[productId] || []).find(
                    (item) => String(item.id) === String(bomId)
                );
                if (match) return match;
            }
            return null;
        },
        groupedMaterials() {
            const order = { RAW: 1, INHOUSE: 2, SERVICE: 3 };
            const groups = {};
            this.materials.forEach((material) => {
                const label = material.type || 'RAW';
                (groups[label] ??= []).push(material);
            });
            return Object.entries(groups)
                .sort(([a], [b]) => (order[a] ?? 99) - (order[b] ?? 99) || a.localeCompare(b))
                .map(([label, items]) => ({
                    label,
                    items: items.sort((a, b) => this.materialLabel(a).localeCompare(this.materialLabel(b))),
                }));
        },
        filteredMaterials() {
            const term = (this.materialSearch || '').trim().toLowerCase();
            if (!term) return this.groupedMaterials();
            return this.groupedMaterials()
                .map((group) => ({
                    label: group.label,
                    items: group.items.filter((material) => {
                        const haystack = `${material.sku || ''} ${material.name || ''}`.toLowerCase();
                        return haystack.includes(term);
                    }),
                }))
                .filter((group) => group.items.length > 0);
        },
        materialGroupsForRow(row) {
            const groups = this.filteredMaterials();
            const selectedId = String(row.component_product_id || '');
            if (!selectedId) return groups;

            const inList = groups.some((group) =>
                group.items.some((material) => String(material.id) === selectedId)
            );
            if (inList) return groups;

            const material = this.materialMap()[selectedId];
            if (!material) return groups;

            return [{ label: 'Selected', items: [material] }, ...groups];
        },
        selectMaterialsForRow(row) {
            const seen = new Set();
            const list = [];

            this.materialGroupsForRow(row).forEach((group) => {
                group.items.forEach((material) => {
                    const id = String(material.id);
                    if (seen.has(id)) return;
                    seen.add(id);
                    list.push(material);
                });
            });

            return list.sort((a, b) => this.materialLabel(a).localeCompare(this.materialLabel(b)));
        },
        registerMaterialsFromTemplate(template) {
            (template?.items || []).forEach((item) => {
                const id = String(item.component_product_id || '');
                if (!id || this.materialMap()[id]) return;

                this.materials.push({
                    id: parseInt(id, 10),
                    sku: item.component_sku || '',
                    name: item.component_name || `Product #${id}`,
                    uom: item.unit || 'piece',
                    standard_cost: item.unit_cost !== '' && item.unit_cost !== null
                        ? parseFloat(item.unit_cost)
                        : null,
                    type: String(item.component_type || 'RAW').toUpperCase(),
                });
            });
        },
        selectedProductName() {
            const match = this.productMap()[String(this.selectedProductId)];
            if (this.lockProduct) return match ? this.productLabel(match) : 'Finished product';
            return match ? this.productLabel(match) : 'Select finished product';
        },
        productInitial() {
            const name = this.selectedProductName();
            if (name === 'Select finished product' || name === 'Finished product') return '?';
            return name.charAt(0).toUpperCase();
        },
        defaultNameForProduct(product) {
            if (!product) return 'Standard · Product';
            const label = (product.sku || product.name || 'Product').trim();
            const base = `Standard · ${label}`;
            if (!this.activeBom()) return base;
            const count = this.productBomCounts[String(product.id)] || 0;
            return `${base} · v${count + 1}`;
        },
        onProductChange() {
            const product = this.productMap()[String(this.selectedProductId)];
            this.previewRecipeId = this.defaultPreviewRecipeId();
            this.copyFromBomId = '';

            if (this.autoName) {
                this.bomName = this.defaultNameForProduct(product);
            }

            if (!this.previewRecipeId) {
                this.rows = [this.blankRow()];
                this.materialUnitCost = '';
            }
        },
        syncAutoName() {
            if (!this.autoName) return;
            const product = this.productMap()[String(this.selectedProductId)];
            this.bomName = this.defaultNameForProduct(product);
        },
        selectRecipeSource(bomId, refreshName = true) {
            this.copyFromBomId = bomId ? String(bomId) : '';
            this.previewRecipeId = this.copyFromBomId;

            if (!bomId) {
                this.rows = [this.blankRow()];
                this.materialUnitCost = '';
                return;
            }

            this.applyCopyTemplate();

            if (refreshName && this.autoName) {
                const product = this.productMap()[String(this.selectedProductId)];
                this.bomName = this.defaultNameForProduct(product);
            }
        },
        applyCopyTemplate() {
            const template = this.findRecipeTemplate(this.copyFromBomId);
            if (!template) return;

            this.registerMaterialsFromTemplate(template);

            const newRows = (template.items || []).map((row, index) => ({
                key: Date.now() + index + Math.random(),
                component_product_id: row.component_product_id ? String(row.component_product_id) : '',
                component_label: this.componentLabelFromItem(row),
                pickMaterial: false,
                quantity: row.quantity ?? 1,
                unit_cost: row.unit_cost ?? '',
                unit: row.unit || '',
            }));

            this.rows = newRows.length > 0 ? newRows : [this.blankRow()];

            this.rows.forEach((row) => {
                if (row.component_product_id) {
                    this.onComponentChange(row);
                }
            });

            if (template.material_unit_cost !== null
                && template.material_unit_cost !== undefined
                && template.material_unit_cost !== '') {
                this.materialUnitCost = template.material_unit_cost;
            }
        },
        onComponentChange(row) {
            const material = this.materialMap()[String(row.component_product_id)];
            if (!material) {
                if (!row.unit) row.unit = '';
                return;
            }
            row.unit = material.uom || 'piece';
            row.component_label = this.materialLabel(material);
            if (row.unit_cost === '' && material.standard_cost !== null && material.standard_cost !== undefined) {
                row.unit_cost = material.standard_cost;
            }
        },
        duplicateComponentIds() {
            const seen = new Set();
            const dupes = new Set();
            this.rows.forEach((row) => {
                if (!row.component_product_id) return;
                if (seen.has(row.component_product_id)) dupes.add(row.component_product_id);
                seen.add(row.component_product_id);
            });
            return [...dupes];
        },
        filledLineCount() {
            return this.rows.filter((row) => row.component_product_id !== '').length;
        },
        lineCost(row) {
            const qty = parseFloat(row.quantity) || 0;
            const cost = parseFloat(row.unit_cost);
            return Number.isNaN(cost) ? 0 : qty * cost;
        },
        estimatedCost() {
            return this.rows.reduce((sum, row) => sum + this.lineCost(row), 0);
        },
        effectiveCost() {
            const override = parseFloat(this.materialUnitCost);
            if (!Number.isNaN(override) && this.materialUnitCost !== '') return override;
            return this.estimatedCost();
        },
        formatMoney(value) {
            return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        addRow() {
            this.rows.push(this.blankRow());
        },
        removeRow(index) {
            if (this.rows.length > 1) this.rows.splice(index, 1);
        },
        init() {
            this.rows.forEach((row) => {
                if (row.component_product_id) {
                    if (!row.component_label) {
                        const material = this.materialMap()[String(row.component_product_id)];
                        if (material) {
                            row.component_label = this.materialLabel(material);
                        }
                    }
                    row.pickMaterial = false;
                    if (!row.unit) {
                        this.onComponentChange(row);
                    }
                } else {
                    row.pickMaterial = true;
                }
            });

            if (this.lockProduct) {
                return;
            }

            if (this.copyFromBomId) {
                this.previewRecipeId = this.copyFromBomId;
                this.applyCopyTemplate();
            } else if (this.selectedProductId) {
                this.previewRecipeId = this.defaultPreviewRecipeId();
                this.onProductChange();
            } else if (this.autoName) {
                this.syncAutoName();
            }
        },
    }));
}
