@php
    $bom = $bom ?? null;
    $currencyCode = config('app.currency', 'BDT');
    $isEdit = (bool) $bom;
@endphp

<div class="bom-form__notice">
    <div class="bom-form__notice-inner">
        <div>
            <p class="bom-form__notice-title">Setup once — use for every production run</p>
            <p class="bom-form__notice-text">
                A BOM is the recipe (materials per <strong>1 finished unit</strong>). Run quantity is set when you create a production order — not here.
            </p>
        </div>
        @unless($isEdit)
            <a href="{{ route('admin.production.create') }}" class="bom-form__notice-link">
                Go to production →
            </a>
        @endunless
    </div>
</div>

<div class="po-create__grid">
    <section class="po-create__card po-create__card--supplier">
        <header class="po-create__card-head">
            <div>
                <h2 class="po-create__card-title">Recipe setup</h2>
                <p class="po-create__card-desc">Choose the product, name the recipe, and set active status — components are added in the next section.</p>
            </div>
            <label class="bom-form__active-toggle">
                <input type="hidden" name="is_active" :value="isActive ? '1' : '0'">
                <input type="checkbox" value="1" x-model="isActive"
                       class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800">
                <span class="font-medium">Active recipe</span>
            </label>
        </header>

        @if($isEdit)
            <input type="hidden" name="auto_name" value="0">
        @else
            <input type="hidden" name="auto_name" :value="autoName ? '1' : '0'">
        @endif

        <div class="bom-form__product-layout">
            <div class="bom-form__product-main">
                @unless($isEdit)
                    <div class="po-create__field">
                        <label for="product_id" class="po-create__label">Product <span class="po-create__req">*</span></label>
                        <select id="product_id" name="product_id" x-model="selectedProductId" required
                                @change="onProductChange()"
                                class="po-create__input">
                            <option value="">Select finished product…</option>
                            <template x-for="product in products" :key="product.id">
                                <option :value="String(product.id)" x-text="productLabel(product)"></option>
                            </template>
                        </select>
                        @error('product_id')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                @else
                    <div class="po-create__field">
                        <span class="po-create__label">Product</span>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $bom->product?->sku ? $bom->product->sku . ' – ' : '' }}{{ $bom->product?->name ?? '—' }}
                        </p>
                    </div>
                @endunless

                <div class="po-create__field">
                    <div class="flex items-center justify-between gap-3">
                        <label for="name" class="po-create__label">Recipe name</label>
                        @unless($isEdit)
                            <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <input type="checkbox" x-model="autoName" @change="syncAutoName()"
                                       class="h-3.5 w-3.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800">
                                Auto-generate
                            </label>
                        @endunless
                    </div>
                    <input type="text" id="name" name="name" x-model="bomName"
                           :readonly="autoName && !lockProduct"
                           :class="autoName && !lockProduct ? 'po-create__input bg-gray-50 dark:bg-gray-800/60' : 'po-create__input'"
                           placeholder="Standard · SKU">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="autoName && !lockProduct" x-cloak>
                        Adds a version suffix if an active recipe already exists.
                    </p>
                    @error('name')<p class="po-create__error">{{ $message }}</p>@enderror
                </div>

                <div class="po-create__field">
                    <label for="notes" class="po-create__label">Notes</label>
                    <input type="text" id="notes" name="notes" x-model="notes" class="po-create__input"
                           placeholder="Export version, seasonal variant…">
                    @error('notes')<p class="po-create__error">{{ $message }}</p>@enderror
                </div>

                <div class="bom-form__draft-panel">
                    <p class="bom-form__draft-label">Recipe summary</p>

                    <div class="bom-form__draft-product" x-show="selectedProductId || lockProduct" x-cloak>
                        <span class="erp-po-index-supplier__avatar" x-text="productInitial()"></span>
                        <div class="min-w-0 flex-1">
                            <p class="bom-form__draft-name" x-text="selectedProductName()"></p>
                            <p class="bom-form__draft-recipe" x-text="bomName || 'Untitled recipe'"></p>
                        </div>
                    </div>

                    <p class="bom-form__draft-hint" x-show="! selectedProductId && ! lockProduct">
                        Select a product to start building this recipe.
                    </p>

                    <dl class="bom-form__draft-stats" x-show="selectedProductId || lockProduct" x-cloak>
                        <div class="bom-form__draft-stat">
                            <dt>Components</dt>
                            <dd x-text="`${filledLineCount()} of ${rows.length} lines`"></dd>
                        </div>
                        <div class="bom-form__draft-stat">
                            <dt>Material cost / unit</dt>
                            <dd>{{ $currencyCode }} <span x-text="formatMoney(effectiveCost())"></span></dd>
                        </div>
                        <div class="bom-form__draft-stat" x-show="copyFromBomId" x-cloak>
                            <dt>Loaded from</dt>
                            <dd x-text="selectedSourceLabel()"></dd>
                        </div>
                        <div class="bom-form__draft-stat">
                            <dt>On save</dt>
                            <dd x-text="isActive ? 'Active recipe for production' : 'Saved as inactive draft'"></dd>
                        </div>
                    </dl>
                </div>
            </div>

            @unless($isEdit)
                <aside class="bom-form__recipe-preview" x-show="selectedProductId" x-cloak>
                    <div class="bom-form__preview-head">
                        <label for="preview_recipe_id" class="po-create__label mb-0">Previous recipes</label>
                        <p class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                            Preview any existing recipe, then load it into the form below.
                        </p>
                    </div>

                    <select id="preview_recipe_id" x-model="previewRecipeId"
                            class="po-create__input bom-form__preview-select mt-3">
                        <option value="">Start blank — no previous recipe</option>
                        <template x-for="recipe in recipesForProduct()" :key="recipe.id">
                            <option :value="String(recipe.id)" x-text="recipeOptionLabel(recipe)"></option>
                        </template>
                    </select>

                    <div class="bom-form__preview-body" x-show="previewRecipe()" x-cloak>
                        <div class="bom-form__preview-meta">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-semibold text-brand-600 dark:text-brand-400" x-text="previewRecipe()?.code"></span>
                                <span x-show="previewRecipe()?.is_active"
                                      class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                    Active
                                </span>
                            </div>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white" x-text="previewRecipe()?.name"></p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="previewRecipe()?.items_count"></span> components
                                · updated <span x-text="previewRecipe()?.updated_at"></span>
                            </p>
                        </div>

                        <div class="bom-form__preview-table-wrap">
                            <table class="bom-form__preview-table">
                                <thead>
                                    <tr>
                                        <th>Material</th>
                                        <th class="text-right">Qty / unit</th>
                                        <th class="text-right">Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="line in previewRecipeLines()" :key="line.id">
                                        <tr>
                                            <td x-text="line.label"></td>
                                            <td class="text-right tabular-nums" x-text="line.qty"></td>
                                            <td class="text-right tabular-nums" x-text="line.cost"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="bom-form__preview-foot">
                            <span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Material cost / unit</span>
                            <span class="text-sm font-bold tabular-nums text-brand-600 dark:text-brand-400">
                                {{ $currencyCode }} <span x-text="formatMoney(previewRecipeTotalCost())"></span>
                            </span>
                        </div>

                        <button type="button"
                                @click="usePreviewRecipe()"
                                :disabled="isPreviewApplied()"
                                :class="isPreviewApplied()
                                    ? 'bom-form__preview-use-btn is-applied'
                                    : 'bom-form__preview-use-btn'"
                                x-text="isPreviewApplied() ? 'Loaded in form below' : 'Use this recipe'">
                        </button>
                    </div>

                    <div class="bom-form__preview-empty" x-show="! previewRecipe() && recipesForProduct().length === 0" x-cloak>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">No previous recipes</p>
                        <p class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                            This product has no saved BOM yet. Add components in the table below.
                        </p>
                    </div>

                    <div class="bom-form__preview-empty" x-show="! previewRecipe() && recipesForProduct().length > 0" x-cloak>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Start from scratch</p>
                        <p class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                            Build a new recipe without copying an older version.
                        </p>
                        <button type="button"
                                @click="useBlankRecipe()"
                                :disabled="isPreviewApplied()"
                                class="bom-form__preview-use-btn mt-4"
                                x-text="isPreviewApplied() ? 'Building from scratch' : 'Start blank'">
                        </button>
                    </div>

                    <input type="hidden" name="copy_from_bom_id" :value="copyFromBomId">
                </aside>

                <div class="bom-form__preview-placeholder" x-show="! selectedProductId">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Select a product to preview previous recipes.</p>
                </div>
            @endunless
        </div>
    </section>

    <section class="po-create__card po-create__card--lines">
        <header class="po-create__card-head po-create__card-head--border">
            <div>
                <h2 class="po-create__card-title">Components</h2>
                <p class="po-create__card-desc">Materials per 1 finished unit — UOM and cost come from the product master.</p>
            </div>
            <span class="po-create__badge" x-text="`${rows.length} line${rows.length === 1 ? '' : 's'}`"></span>
        </header>

        <div class="bom-form__search-bar">
            <div class="bom-form__search-field">
                <label for="material_search" class="po-create__label">Search materials</label>
                <input type="search" id="material_search" x-model="materialSearch"
                       class="po-create__input mt-1" placeholder="SKU or name…">
            </div>
        </div>

        <div class="border-b border-amber-100 bg-amber-50/70 px-5 py-2.5 text-sm text-amber-900 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100 sm:px-6"
             x-show="duplicateComponentIds().length > 0" x-cloak>
            Duplicate material on multiple lines — consider merging quantities.
        </div>

        <div class="po-create__table-scroll">
            <table class="po-create__table po-create__table--bom">
                <colgroup>
                    <col class="col-num">
                    <col class="col-desc">
                    <col class="col-qty">
                    <col class="col-uom">
                    <col class="col-price">
                    <col class="col-total">
                    <col class="col-act">
                </colgroup>
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th class="col-desc">Material</th>
                        <th class="col-qty">Qty / unit</th>
                        <th class="col-uom">UOM</th>
                        <th class="col-price">Unit cost</th>
                        <th class="col-total">Line cost</th>
                        <th class="col-act"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in rows" :key="row.key">
                        <tr :class="duplicateComponentIds().includes(row.component_product_id) && row.component_product_id ? 'bg-amber-50/50 dark:bg-amber-500/5' : ''">
                            <td class="col-num"><span x-text="index + 1"></span></td>
                            <td class="col-desc">
                                <input type="hidden"
                                       :name="`items[${index}][component_product_id]`"
                                       :value="row.component_product_id">

                                <div x-show="! row.pickMaterial && row.component_product_id"
                                     class="bom-form__material-pick">
                                    <span class="bom-form__material-name" x-text="rowMaterialLabel(row)"></span>
                                    <button type="button"
                                            class="bom-form__material-change"
                                            @click="row.pickMaterial = true">
                                        Change
                                    </button>
                                </div>

                                <select x-show="row.pickMaterial || ! row.component_product_id"
                                        class="po-create__input po-create__input--table po-create__input--cell"
                                        x-model="row.component_product_id"
                                        @change="onComponentChange(row); row.pickMaterial = false">
                                    <option value="">Select material…</option>
                                    <template x-for="material in selectMaterialsForRow(row)" :key="`${row.key}-${material.id}`">
                                        <option :value="String(material.id)" x-text="materialLabel(material)"></option>
                                    </template>
                                </select>
                            </td>
                            <td class="col-qty">
                                <input type="text" inputmode="decimal"
                                       class="po-create__input po-create__input--cell po-create__input--num"
                                       x-model="row.quantity"
                                       :name="`items[${index}][quantity]`"
                                       required
                                       placeholder="1">
                            </td>
                            <td class="col-uom">
                                <span class="inline-flex min-h-[2.5rem] items-center px-1 text-sm font-medium capitalize text-gray-700 dark:text-gray-300" x-text="row.unit || '—'"></span>
                                <input type="hidden" :name="`items[${index}][unit]`" :value="row.unit">
                            </td>
                            <td class="col-price">
                                <div class="po-create__price-cell">
                                    <input type="text" inputmode="decimal"
                                           class="po-create__input po-create__input--cell po-create__input--num po-create__input--price"
                                           x-model="row.unit_cost"
                                           :name="`items[${index}][unit_cost]`"
                                           placeholder="0.00">
                                    <span class="po-create__currency-hint">{{ $currencyCode }}</span>
                                </div>
                            </td>
                            <td class="col-total">
                                <div class="po-create__line-total">
                                    <span class="po-create__line-total-label">{{ $currencyCode }}</span>
                                    <span class="po-create__line-total-value" x-text="formatMoney(lineCost(row))"></span>
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
            Add component
        </button>

        @error('items')<p class="po-create__error px-5 py-2">{{ $message }}</p>@enderror
        @error('items.*')<p class="po-create__error px-5 py-2">{{ $message }}</p>@enderror

        <div class="bom-form__summary-foot">
            <div class="bom-form__summary-layout">
                <div class="bom-form__cost-override">
                    <label for="material_unit_cost" class="po-create__label">Override material cost ({{ $currencyCode }})</label>
                    <input type="text" inputmode="decimal" id="material_unit_cost" name="material_unit_cost"
                           x-model="materialUnitCost" class="po-create__input mt-1" placeholder="Leave blank to use line estimate">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional — replaces the sum of component lines.</p>
                </div>

                <footer class="po-create__summary !border-0 !bg-transparent !p-0">
                    <dl class="po-create__summary-list">
                        <div class="po-create__summary-row">
                            <dt>Components filled</dt>
                            <dd><span x-text="filledLineCount()"></span> of <span x-text="rows.length"></span></dd>
                        </div>
                        <div class="po-create__summary-row">
                            <dt>Estimated from lines</dt>
                            <dd>{{ $currencyCode }} <span x-text="formatMoney(estimatedCost())"></span></dd>
                        </div>
                        <div class="po-create__summary-row po-create__summary-row--total">
                            <dt>Material cost / unit</dt>
                            <dd>{{ $currencyCode }} <span x-text="formatMoney(effectiveCost())"></span></dd>
                        </div>
                    </dl>
                </footer>
            </div>
            <p class="bom-form__summary-note">
                Totals above are per 1 finished unit. When you run production, enter quantity on the production order — materials scale automatically.
            </p>
        </div>
    </section>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('bomForm', (config) => ({
                    currencyCode: @js($currencyCode),
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
                            pickMaterial: ! componentId,
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
                        if (! this.selectedProductId) return null;
                        return this.activeBomsByProduct[String(this.selectedProductId)] || null;
                    },
                    recipesForProduct() {
                        if (! this.selectedProductId) return [];
                        return this.productRecipesByProduct[String(this.selectedProductId)] || [];
                    },
                    recipeOptionLabel(recipe) {
                        const active = recipe.is_active ? ' · Active' : '';
                        return `${recipe.code} · ${recipe.name}${active}`;
                    },
                    previewRecipe() {
                        if (! this.previewRecipeId) return null;
                        return this.recipesForProduct().find(
                            (item) => String(item.id) === String(this.previewRecipeId)
                        ) || this.findRecipeTemplate(this.previewRecipeId);
                    },
                    previewRecipeLines() {
                        const recipe = this.previewRecipe();
                        if (! recipe) return [];

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
                        if (! recipe) return 0;

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
                        if (! bomId) return null;
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
                        if (! term) return this.groupedMaterials();
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
                        if (! selectedId) return groups;

                        const inList = groups.some((group) =>
                            group.items.some((material) => String(material.id) === selectedId)
                        );
                        if (inList) return groups;

                        const material = this.materialMap()[selectedId];
                        if (! material) return groups;

                        return [
                            { label: 'Selected', items: [material] },
                            ...groups,
                        ];
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
                            if (! id || this.materialMap()[id]) return;

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
                        if (! product) return 'Standard · Product';
                        const label = (product.sku || product.name || 'Product').trim();
                        const base = `Standard · ${label}`;
                        if (! this.activeBom()) return base;
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

                        if (! this.previewRecipeId) {
                            this.rows = [this.blankRow()];
                            this.materialUnitCost = '';
                        }
                    },
                    syncAutoName() {
                        if (! this.autoName) return;
                        const product = this.productMap()[String(this.selectedProductId)];
                        this.bomName = this.defaultNameForProduct(product);
                    },
                    selectRecipeSource(bomId, refreshName = true) {
                        this.copyFromBomId = bomId ? String(bomId) : '';
                        this.previewRecipeId = this.copyFromBomId;

                        if (! bomId) {
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
                        if (! template) return;

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
                        if (! material) {
                            if (! row.unit) row.unit = '';
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
                            if (! row.component_product_id) return;
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
                        if (! Number.isNaN(override) && this.materialUnitCost !== '') return override;
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
                                if (! row.component_label) {
                                    const material = this.materialMap()[String(row.component_product_id)];
                                    if (material) {
                                        row.component_label = this.materialLabel(material);
                                    }
                                }
                                row.pickMaterial = false;
                                if (! row.unit) {
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
            });
        </script>
    @endpush
@endonce
