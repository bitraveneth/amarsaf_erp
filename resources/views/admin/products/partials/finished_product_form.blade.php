@php
    use App\Support\ProductUnits;

    $units = $units ?? collect();
    $uomOptions = ProductUnits::forFinishedProducts($units->isNotEmpty() ? $units : null);
    $standardPackagingTypes = $packagingTypes->where('is_system', true);
    $customPackagingTypes = $packagingTypes->where('is_system', false);
    $selectedPackagingId = old('packaging_type_id', $product->packaging_type_id ?? '');
    $selectedTaxClassId = old('tax_class_id', $product->tax_class_id ?? '');
    $isEdit = filled($product?->id);
    $isActiveStatus = (bool) old('is_active', $product->is_active ?? 1);
    $productImageUrl = filled($product?->image_path) ? asset('storage/'.$product->image_path) : null;
    $productNameOptions = $productNameOptions ?? ['names' => [], 'presets' => []];
    $packagingNameToId = $packagingTypes
        ->mapWithKeys(fn ($type) => [$type->name => (string) $type->id])
        ->all();

    $packagingUomMap = $packagingTypes
        ->filter(fn ($type) => filled($type->unit))
        ->mapWithKeys(fn ($type) => [(string) $type->id => strtolower(trim($type->unit))])
        ->all();
@endphp

<input type="hidden" name="product_type" value="{{ old('product_type', 'finished') }}">
<input type="hidden" name="brand" value="SAF">

@if($isEdit)
    @include('admin.products.partials.catalog_readiness', ['product' => $product])
@endif

<div class="product-master-form mt-4 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
    @if($isEdit)
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/50 px-6 py-3 dark:border-gray-800 dark:bg-gray-800/20">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Pricing is on the
                <a href="{{ route('admin.products.prices.show', $product) }}" class="font-medium text-brand-600 dark:text-brand-400">price list</a>
                @if($product->hasPricingSet())
                    · Trade BDT {{ number_format($product->base_price, 0) }}
                    @if($product->mrp && (float) $product->mrp > 0)
                        · MRP BDT {{ number_format($product->mrp, 0) }}
                    @endif
                @else
                    · <span class="text-amber-600 dark:text-amber-400">Trade price not set</span>
                @endif
            </p>
        </div>
    @endif

    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        {{-- Product details --}}
        <div class="p-6">
            <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Product Details</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define the product name, description, and image.</p>
            </div>

            {{-- Row 1: name + SKU (left) · image (right). Row 2: description full width. --}}
            <div class="mt-5 space-y-4">
                <div class="product-details-top">
                    <div class="product-details-top__fields space-y-4">
                        <x-admin.product-name-field
                            :value="old('name', $product->name ?? '')"
                            :names="$productNameOptions['names'] ?? []"
                            :presets="$productNameOptions['presets'] ?? []"
                        />

                        @if($isEdit)
                            <div>
                                <span class="{{ $labelClass }}">SKU</span>
                                <div class="flex h-[42px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 dark:border-gray-700 dark:bg-gray-800/50">
                                    <input type="hidden" name="sku" value="{{ old('sku', $product->sku) }}">
                                    <span class="truncate font-mono text-sm font-medium uppercase tracking-wide text-gray-800 dark:text-gray-200">{{ $product->sku }}</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Fixed after creation.</p>
                            </div>
                        @else
                            <x-admin.sku-field
                                :value="old('sku', $product->sku ?? '')"
                                product-type="finished"
                                label="SKU"
                                :compact="true"
                            />
                        @endif
                        @error('sku')
                            <p class="-mt-2 text-sm text-error-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="product-details-top__image">
                        <label for="product_image"
                               class="product-details-top__image-drop group relative transition dark:hover:border-brand-500/60 dark:hover:bg-brand-500/5"
                               title="Upload product image">
                            <img id="product-image-preview"
                                 src="{{ $productImageUrl }}"
                                 alt="Product image"
                                 @class([
                                     'absolute inset-0 size-full object-cover',
                                     'hidden' => ! $productImageUrl,
                                 ])>
                            <div id="product-image-placeholder"
                                 @class([
                                     'flex size-full flex-col items-center justify-center gap-1.5 px-2 text-center',
                                     'hidden' => (bool) $productImageUrl,
                                 ])>
                                <svg class="size-6 text-gray-400 transition group-hover:text-brand-500 dark:text-gray-500 sm:size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-[10px] font-semibold leading-tight text-gray-600 group-hover:text-brand-600 dark:text-gray-400 sm:text-[11px]">Add image</span>
                            </div>
                            <span class="pointer-events-none absolute inset-x-0 bottom-0 bg-gray-900/70 py-1 text-center text-[10px] font-medium text-white opacity-0 transition group-hover:opacity-100">
                                {{ $productImageUrl ? 'Change' : 'Upload' }}
                            </span>
                            <input type="file" id="product_image" name="product_image" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>
                        @error('product_image')
                            <p class="mt-1 text-[11px] text-error-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="{{ $labelClass }}">Description</label>
                    <textarea id="description" name="description" rows="2"
                              class="{{ $inputClass }} min-h-[4.5rem] resize-y"
                              placeholder="Optional notes for catalog and invoices">{{ old('description', $product->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Specifications --}}
        <div class="p-6">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Specifications</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Volume, sell unit, packaging, and shelf life. Standard names fill these automatically.</p>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="volume_ml_select" class="{{ $labelClass }}">Net volume</label>
                    <select id="volume_ml_select" class="{{ $selectClass }}">
                        <option value="">Select volume</option>
                        @foreach([500 => '500ml', 1000 => '1L', 2000 => '2L', 20000 => '20L'] as $ml => $volLabel)
                            <option value="{{ $ml }}" @selected((string) $currentVolume === (string) $ml)>{{ $volLabel }}</option>
                        @endforeach
                        <option value="__custom" @selected($currentVolume && ! in_array((int) $currentVolume, [500, 1000, 2000, 20000], true))>Custom…</option>
                    </select>
                    <input type="number" id="volume_ml" name="volume_ml" value="{{ $currentVolume }}" min="0"
                           class="mt-2 hidden {{ $inputClass }}" placeholder="Enter ml">
                    <input type="hidden" id="size" name="size" value="{{ $currentSize }}">
                    @error('volume_ml')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="uom" class="{{ $labelClass }}">Unit of measure</label>
                    <div class="flex items-stretch gap-2">
                        <select id="uom" name="uom" class="min-w-0 flex-1 {{ $selectClass }}">
                            <option value="">Select unit</option>
                            @foreach($uomOptions as $value => $label)
                                <option value="{{ $value }}" @selected($currentUom === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('admin.units.index') }}" target="_blank" rel="noopener"
                           title="Open units master"
                           class="erp-btn-primary inline-flex shrink-0 items-center gap-1.5 self-stretch !px-3 !py-2.5 !text-xs">
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add unit
                        </a>
                    </div>
                    <p id="uom-packaging-hint" class="mt-1 hidden text-xs text-brand-600 dark:text-brand-400"></p>
                    @error('uom')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="packaging_type_id" class="{{ $labelClass }}">Packaging</label>
                    <select id="packaging_type_id" name="packaging_type_id" class="{{ $selectClass }}">
                        <option value="">Unassigned</option>
                        @if($standardPackagingTypes->isNotEmpty())
                            <optgroup label="Standard">
                                @foreach($standardPackagingTypes as $type)
                                    <option value="{{ $type->id }}" @selected((string) $selectedPackagingId === (string) $type->id)>{{ $type->selectOptionLabel() }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if($customPackagingTypes->isNotEmpty())
                            <optgroup label="Custom">
                                @foreach($customPackagingTypes as $type)
                                    <option value="{{ $type->id }}" @selected((string) $selectedPackagingId === (string) $type->id)>{{ $type->selectOptionLabel() }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    @error('packaging_type_id')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="shelf_life_months" class="{{ $labelClass }}">Shelf life (months)</label>
                    <input type="number" step="1" min="1" max="120" id="shelf_life_months" name="shelf_life_months"
                           value="{{ old('shelf_life_months', $product->shelf_life_months ?? '') }}"
                           class="{{ $inputClass }}" placeholder="12">
                    @error('shelf_life_months')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Codes --}}
        <div class="p-6">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Codes & status</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Retail barcode, tax class, and whether the SKU is orderable.</p>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label for="barcode" class="{{ $labelClass }}">Barcode</label>
                    <input type="text" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}"
                           class="{{ $inputClass }} font-mono text-sm" placeholder="EAN-13">
                    @error('barcode')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tax_class_id" class="{{ $labelClass }}">VAT</label>
                    <select id="tax_class_id" name="tax_class_id" class="{{ $selectClass }}">
                        <option value="">Not applicable</option>
                        @foreach($taxClasses as $class)
                            <option value="{{ $class->id }}" @selected((string) $selectedTaxClassId === (string) $class->id)>
                                {{ $class->name }} ({{ $class->rate }}%)
                            </option>
                        @endforeach
                    </select>
                    @error('tax_class_id')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <span class="{{ $labelClass }}">Status</span>
                    <label for="is_active"
                           id="product-status-card"
                           class="product-status-toggle {{ $isActiveStatus ? 'product-status-toggle--active' : 'product-status-toggle--inactive' }}">
                        <span class="product-status-toggle__leading" aria-hidden="true">
                            <svg class="product-status-toggle__icon product-status-toggle__icon--on" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <svg class="product-status-toggle__icon product-status-toggle__icon--off" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span class="product-status-toggle__body">
                            <span id="product-status-badge" class="product-status-toggle__badge">
                                {{ $isActiveStatus ? 'Active' : 'Inactive' }}
                            </span>
                            <span id="product-status-hint" class="product-status-toggle__hint">
                                {{ $isActiveStatus ? 'Available for orders' : 'Hidden from catalog' }}
                            </span>
                        </span>
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               @checked($isActiveStatus)
                               class="sr-only">
                        <span class="product-status-toggle__track" aria-hidden="true">
                            <span class="product-status-toggle__thumb"></span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    @unless($isEdit)
        <div class="border-t border-gray-100 bg-gray-50/50 px-6 py-3 text-xs text-gray-500 dark:border-gray-800 dark:bg-gray-800/20 dark:text-gray-400">
            <p id="product-preset-hint" class="hidden mb-1 text-brand-600 dark:text-brand-400"></p>
            After saving, set trade price and MRP on the
            <a href="{{ route('admin.products.prices.index') }}" class="font-medium text-brand-600 dark:text-brand-400">price list</a>.
        </div>
    @endunless
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const packagingUomMap = @json($packagingUomMap);
                const packagingNameToId = @json($packagingNameToId);
                const standardVolumes = ['500', '1000', '2000', '20000'];
                const volumeSelect = document.getElementById('volume_ml_select');
                const volumeInput = document.getElementById('volume_ml');
                const sizeInput = document.getElementById('size');
                const packagingSelect = document.getElementById('packaging_type_id');
                const uomSelect = document.getElementById('uom');
                const uomHint = document.getElementById('uom-packaging-hint');
                const shelfLifeInput = document.getElementById('shelf_life_months');
                const presetHint = document.getElementById('product-preset-hint');
                const volumeLabels = { '500': '500ml', '1000': '1L', '2000': '2L', '20000': '20L' };

                const showPresetHint = (message) => {
                    if (!presetHint) return;
                    presetHint.textContent = message;
                    presetHint.classList.remove('hidden');
                };

                const setVolume = (volumeMl) => {
                    if (!volumeSelect || !volumeInput) return;
                    const value = String(volumeMl || '');
                    if (standardVolumes.includes(value)) {
                        volumeSelect.value = value;
                    } else if (value) {
                        volumeSelect.value = '__custom';
                        volumeInput.value = value;
                    }
                    volumeInput.classList.toggle('hidden', volumeSelect.value !== '__custom');
                    volumeInput.style.display = volumeSelect.value === '__custom' ? 'block' : 'none';
                    if (sizeInput && volumeLabels[value]) {
                        sizeInput.value = volumeLabels[value];
                    }
                };

                if (volumeSelect && volumeInput) {
                    const syncVolume = () => {
                        const custom = volumeSelect.value === '__custom';
                        volumeInput.classList.toggle('hidden', !custom);
                        volumeInput.style.display = custom ? 'block' : 'none';
                        if (!custom) volumeInput.value = volumeSelect.value;
                        if (sizeInput && !custom) sizeInput.value = volumeLabels[volumeSelect.value] || sizeInput.value;
                    };
                    volumeSelect.addEventListener('change', syncVolume);
                    syncVolume();
                }

                const norm = (v) => String(v || '').toLowerCase().trim().replace(/[\s-]+/g, '_');
                packagingSelect?.addEventListener('change', () => {
                    const hint = packagingUomMap[packagingSelect.value];
                    if (!hint || !uomSelect || uomSelect.value) return;
                    for (const opt of uomSelect.options) {
                        if (opt.value && (norm(opt.value) === norm(hint) || norm(opt.text) === norm(hint))) {
                            uomSelect.value = opt.value;
                            if (uomHint) {
                                const unitLabel = opt.text.trim();
                                uomHint.textContent = 'Sell unit set to ' + unitLabel + ' from packaging.';
                                uomHint.classList.remove('hidden');
                            }
                            break;
                        }
                    }
                });

                uomSelect?.addEventListener('change', () => {
                    uomHint?.classList.add('hidden');
                });

                window.addEventListener('product-name-preset', (event) => {
                    const preset = event.detail || {};
                    if (preset.volume_ml) setVolume(preset.volume_ml);
                    if (preset.size && sizeInput) sizeInput.value = preset.size;
                    if (preset.uom && uomSelect) uomSelect.value = preset.uom;
                    if (preset.packaging_name && packagingSelect) {
                        const packagingId = packagingNameToId[preset.packaging_name];
                        if (packagingId) packagingSelect.value = packagingId;
                    }
                    if (preset.shelf_life_months && shelfLifeInput && !shelfLifeInput.value) {
                        shelfLifeInput.value = preset.shelf_life_months;
                    }
                    const skuInput = document.getElementById('sku');
                    if (skuInput && !skuInput.value && preset.sku) {
                        skuInput.value = preset.sku;
                        skuInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    showPresetHint('Specs filled from standard product line. Review SKU and barcode before saving.');
                });

                const imageInput = document.getElementById('product_image');
                const imagePreview = document.getElementById('product-image-preview');
                const imagePlaceholder = document.getElementById('product-image-placeholder');
                imageInput?.addEventListener('change', () => {
                    const file = imageInput.files?.[0];
                    if (!file || !imagePreview) return;
                    imagePreview.src = URL.createObjectURL(file);
                    imagePreview.classList.remove('hidden');
                    imagePlaceholder?.classList.add('hidden');
                });

                const statusInput = document.getElementById('is_active');
                const statusCard = document.getElementById('product-status-card');
                const statusBadge = document.getElementById('product-status-badge');
                const statusHint = document.getElementById('product-status-hint');
                statusInput?.addEventListener('change', () => {
                    const active = statusInput.checked;
                    statusCard?.classList.toggle('product-status-toggle--active', active);
                    statusCard?.classList.toggle('product-status-toggle--inactive', !active);
                    if (statusBadge) statusBadge.textContent = active ? 'Active' : 'Inactive';
                    if (statusHint) statusHint.textContent = active ? 'Available for orders' : 'Hidden from catalog';
                });
            });
        </script>
    @endpush
@endonce
