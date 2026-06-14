@php
    use App\Support\ProductUnits;

    $product = $product ?? null;
    $units = $units ?? collect();
    $uomOptions = $units->isNotEmpty()
        ? $units->pluck('name', 'code')->all()
        : ProductUnits::options($isMaterials);
    $materialCategories = $materialCategories ?? collect();
    $groupedCategories = $groupedCategories ?? $materialCategories->groupBy(fn ($cat) => $cat->group ?: 'Other');
    $selectedCategory = old('material_category_id', $product->material_category_id ?? '');
    $currentSize = old('size', $product->size ?? '');
    $currentVolume = old('volume_ml', $product->volume_ml ?? '');
    $currentUom = old('uom', $product->uom ?? '');

    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400';
    $selectClass = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white';
    $labelClass = 'mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300';
@endphp

@if($isMaterials)
    {{-- Section 1: Basic details --}}
    <div class="space-y-4" data-tour="product-form-basics">
        <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Material Details</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define the material name and an optional description.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="lg:col-span-2">
                <label for="name" class="{{ $labelClass }}">Material Name <span class="text-error-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required class="{{ $inputClass }}">
                @error('name')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ materialType: @js(old('product_type', $product->product_type ?? 'raw')) }">
                <label for="product_type" class="{{ $labelClass }}">Material Type</label>
                @php($type = old('product_type', $product->product_type ?? 'raw'))
                <select id="product_type" name="product_type" required class="{{ $selectClass }}"
                        @change="materialType = $event.target.value">
                    <option value="raw" @selected($type === 'raw')>Raw material</option>
                    <option value="service" @selected($type === 'service')>Service / external cost</option>
                    <option value="inhouse" @selected($type === 'inhouse')>In-house step</option>
                </select>

                <div class="mt-4" x-show="materialType === 'raw'" x-cloak>
                    <label for="sourcing" class="{{ $labelClass }}">Sourcing</label>
                    @php($sourcing = old('sourcing', $product->sourcing ?? 'purchased'))
                    <select id="sourcing" name="sourcing" class="{{ $selectClass }}">
                        <option value="purchased" @selected($sourcing === 'purchased')>Purchased</option>
                        <option value="inhouse" @selected($sourcing === 'inhouse')>Made in-house</option>
                        <option value="both" @selected($sourcing === 'both')>Both</option>
                    </select>
                </div>
            </div>

            <div x-data="{ showAddCategory: false }">
                <div class="flex items-end gap-2">
                    <div class="min-w-0 flex-1">
                        <label for="material_category_id" class="{{ $labelClass }}">Material Category</label>
                        <select id="material_category_id" name="material_category_id" class="{{ $selectClass }}">
                            <option value="">Select category</option>
                            @foreach($groupedCategories as $groupName => $items)
                                <optgroup label="{{ $groupName }}">
                                    @foreach($items as $category)
                                        <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" @click="showAddCategory = !showAddCategory"
                            class="mb-0.5 inline-flex shrink-0 items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        + Add
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <a href="{{ route('admin.material-categories.index') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Manage categories</a>
                </p>
                @error('material_category_id')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="lg:col-span-2">
                <label for="description" class="{{ $labelClass }}">Description</label>
                <textarea id="description" name="description" rows="3" class="{{ $inputClass }}">{{ old('description', $product->description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Section 2: Measurements --}}
    <div class="space-y-4">
        <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Measurements & Units</h3>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div x-data="{ showAddUom: false }">
                <div class="flex items-end gap-2">
                    <div class="min-w-0 flex-1">
                        <label for="uom" class="{{ $labelClass }}">Unit of Measure (UOM)</label>
                        <select id="uom" name="uom" class="{{ $selectClass }}">
                            <option value="">Select unit</option>
                            @foreach($uomOptions as $value => $label)
                                <option value="{{ $value }}" @selected($currentUom === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" @click="showAddUom = !showAddUom"
                            class="mb-0.5 inline-flex shrink-0 items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        + Add
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <a href="{{ route('admin.units.index') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Manage units</a>
                </p>
            </div>

            <div>
                <label for="size" class="{{ $labelClass }}">Size / variant</label>
                <input type="text" id="size" name="size" value="{{ $currentSize }}" placeholder="e.g. 500ml, 1L"
                       class="{{ $inputClass }}">
            </div>

            <div class="lg:col-span-2">
                <label for="chemical_name" class="{{ $labelClass }}">Chemical / scientific name</label>
                <input type="text" id="chemical_name" name="chemical_name"
                       value="{{ old('chemical_name', $product->chemical_name ?? '') }}"
                       class="{{ $inputClass }}">
                @error('chemical_name')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Section 3: Codes & costing --}}
    <div class="space-y-4">
        <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Codes, Costing & Status</h3>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-admin.sku-field
                    :value="old('sku', $product->sku ?? '')"
                    :product-type="old('product_type', $product->product_type ?? 'raw')"
                    label="Material Code (SKU)"
                    material-hint="RM- · SV- · IH- prefix by type"
                />
            </div>

            <div>
                <label for="standard_cost" class="{{ $labelClass }}">Standard Cost per Unit</label>
                <input type="number" step="0.01" min="0" id="standard_cost" name="standard_cost"
                       value="{{ old('standard_cost', $product->standard_cost ?? '') }}" class="{{ $inputClass }}">
            </div>

            <div>
                <label for="supplier_name" class="{{ $labelClass }}">Supplier Name (optional)</label>
                <input type="text" id="supplier_name" name="supplier_name"
                       value="{{ old('supplier_name', $product->supplier_name ?? '') }}" class="{{ $inputClass }}">
            </div>

            <div>
                <label for="reorder_level" class="{{ $labelClass }}">Reorder Level</label>
                <input type="number" step="1" min="0" id="reorder_level" name="reorder_level"
                       value="{{ old('reorder_level', $product->reorder_level ?? '') }}" class="{{ $inputClass }}">
            </div>

            <div class="sm:col-span-2">
                <label for="is_active" class="inline-flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $product->is_active ?? 1))
                           class="h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                </label>
            </div>
        </div>
    </div>
@else
    @include('admin.products.partials.finished_product_form', [
        'product' => $product,
        'packagingTypes' => $packagingTypes,
        'taxClasses' => $taxClasses,
        'units' => $units,
        'currentSize' => $currentSize,
        'currentVolume' => $currentVolume,
        'currentUom' => $currentUom,
        'inputClass' => $inputClass,
        'selectClass' => $selectClass,
        'labelClass' => $labelClass,
        'productNameOptions' => $productNameOptions ?? ['names' => [], 'presets' => []],
    ])
@endif
