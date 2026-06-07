@php
    use App\Support\ProductUnits;

    $product = $product ?? null;
    $uomOptions = ProductUnits::options($isMaterials);
    $currentSize = old('size', $product->size ?? '');
    $currentVolume = old('volume_ml', $product->volume_ml ?? '');
    $currentUom = old('uom', $product->uom ?? '');

    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400';
    $selectClass = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white';
    $labelClass = 'mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300';
    $materialCategories = $materialCategories ?? collect();
    $selectedCategory = old('material_category_id', $product->material_category_id ?? '');
    $groupedCategories = $materialCategories->groupBy(fn ($cat) => $cat->group ?: 'Other');
@endphp

{{-- Section 1: Basic details --}}
<div class="space-y-4" data-tour="product-form-basics">
    <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ $isMaterials ? 'Material Details' : 'Product Details' }}
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            @if($isMaterials)
                Define the material name and an optional description.
            @else
                Define the product name, description, and hero image.
            @endif
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="lg:col-span-2">
            <label for="name" class="{{ $labelClass }}">
                {{ $isMaterials ? 'Material Name' : 'Product Name' }}
                <span class="text-error-500">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required class="{{ $inputClass }}">
            @error('name')
                <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
            @enderror
        </div>

        @if($isMaterials)
            <div>
                <label for="product_type" class="{{ $labelClass }}">Material Type</label>
                @php($type = old('product_type', $product->product_type ?? 'raw'))
                <select id="product_type" name="product_type" required class="{{ $selectClass }}">
                    <option value="raw" @selected($type === 'raw')>Raw material (stocked)</option>
                    <option value="service" @selected($type === 'service')>Service / external cost</option>
                    <option value="inhouse" @selected($type === 'inhouse')>In-house step</option>
                </select>
            </div>

            <div>
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
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    What you buy this item as (preform, label, mineral, etc.).
                    <a href="{{ route('admin.material-categories.index') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Manage categories</a>
                </p>
                @error('material_category_id')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        @else
            <input type="hidden" id="product_type" name="product_type" value="{{ old('product_type', 'finished') }}">
        @endif

        @unless($isMaterials)
            <div>
                <label for="product_image" class="{{ $labelClass }}">
                    Product Image
                    <span class="ml-1 text-xs font-normal text-gray-500 dark:text-gray-400">(PNG/JPG, 1080×1080 px preferred)</span>
                </label>
                <input type="file" id="product_image" name="product_image" accept="image/*"
                       class="{{ $inputClass }} file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-700 dark:file:text-gray-300">
                @if($product?->image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}"
                         class="mt-3 h-24 w-24 rounded-lg border border-gray-200 object-cover dark:border-gray-700">
                @endif
            </div>
        @endunless

        <div class="{{ $isMaterials ? 'lg:col-span-2' : 'lg:col-span-2' }}">
            <label for="description" class="{{ $labelClass }}">Description</label>
            <textarea id="description" name="description" rows="3" class="{{ $inputClass }}">{{ old('description', $product->description ?? '') }}</textarea>
        </div>
    </div>
</div>

{{-- Section 2: Measurements --}}
<div class="space-y-4">
    <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Measurements &amp; Units</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            @if($isMaterials)
                Define the unit of measure for this material.
            @else
                Define size labels, units of measure, and volumes.
            @endif
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <label for="uom" class="{{ $labelClass }}">Unit of Measure (UOM)</label>
            <select id="uom" name="uom" class="{{ $selectClass }}">
                <option value="">Select unit</option>
                @foreach($uomOptions as $value => $label)
                    <option value="{{ $value }}" @selected($currentUom === $value)>{{ $label }}</option>
                @endforeach
                @if($currentUom && ! array_key_exists($currentUom, $uomOptions))
                    <option value="{{ $currentUom }}" selected>{{ ProductUnits::label($currentUom) }} (legacy)</option>
                @endif
            </select>
        </div>

        @unless($isMaterials)
            <div>
                <label for="size_select" class="{{ $labelClass }}">Size</label>
                <select id="size_select" class="{{ $selectClass }}">
                    <option value="">Select size</option>
                    @foreach(['500ml', '1L', '20L'] as $size)
                        <option value="{{ $size }}" @selected($currentSize === $size)>{{ $size }}</option>
                    @endforeach
                    <option value="__custom" @selected($currentSize && ! in_array($currentSize, ['500ml', '1L', '20L'], true))>Custom…</option>
                </select>
                <input type="text" id="size" name="size" value="{{ $currentSize }}"
                       class="mt-2 hidden {{ $inputClass }}" placeholder="e.g. 500ml">
            </div>

            <div>
                <label for="volume_ml_select" class="{{ $labelClass }}">Volume (ml)</label>
                <select id="volume_ml_select" class="{{ $selectClass }}">
                    <option value="">Select volume</option>
                    @foreach([500, 1000, 20000] as $volume)
                        <option value="{{ $volume }}" @selected((string) $currentVolume === (string) $volume)>{{ number_format($volume) }}</option>
                    @endforeach
                    <option value="__custom" @selected($currentVolume && ! in_array((int) $currentVolume, [500, 1000, 20000], true))>Custom…</option>
                </select>
                <input type="number" id="volume_ml" name="volume_ml" value="{{ $currentVolume }}" min="0"
                       class="mt-2 hidden {{ $inputClass }}">
            </div>
        @endunless
    </div>
</div>

@unless($isMaterials)
    {{-- Section 3: Packaging & tax --}}
    <div class="space-y-4">
        <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Packaging &amp; Taxation</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Link SKUs to packaging master data and VAT-ready tax classes.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="packaging_type_id" class="{{ $labelClass }}">Packaging Type</label>
                <select id="packaging_type_id" name="packaging_type_id" class="{{ $selectClass }}">
                    <option value="">Unassigned</option>
                    @foreach($packagingTypes as $type)
                        <option value="{{ $type->id }}" @selected(old('packaging_type_id', $product->packaging_type_id ?? '') == $type->id)>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tax_class_id" class="{{ $labelClass }}">Tax Class</label>
                <select id="tax_class_id" name="tax_class_id" class="{{ $selectClass }}">
                    <option value="">Unassigned</option>
                    @foreach($taxClasses as $class)
                        <option value="{{ $class->id }}" @selected(old('tax_class_id', $product->tax_class_id ?? '') == $class->id)>
                            {{ $class->name }} · {{ $class->rate }}%
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
@endunless

{{-- Section 4: Codes & pricing --}}
<div class="space-y-4">
    <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Codes, Pricing &amp; Costing</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            @if($isMaterials)
                Track standard cost and optional supplier for this material.
            @else
                Define the base selling price and stock alert level for this SKU.
            @endif
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-admin.sku-field
                :value="old('sku', $product->sku ?? '')"
                :product-type="$isMaterials ? old('product_type', $product->product_type ?? 'raw') : 'finished'"
                :label="$isMaterials ? 'Material Code (SKU)' : 'SKU'"
            />
        </div>

        @unless($isMaterials)
            <div>
                <label for="base_price" class="{{ $labelClass }}">Base Price (BDT)</label>
                <input type="number" step="0.01" min="0" id="base_price" name="base_price"
                       value="{{ old('base_price', $product->base_price ?? '') }}" required class="{{ $inputClass }}">
                @error('base_price')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        @endunless

        @if($isMaterials)
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
        @endif

        <div>
            <label for="reorder_level" class="{{ $labelClass }}">Reorder Level</label>
            <input type="number" step="1" min="0" id="reorder_level" name="reorder_level"
                   value="{{ old('reorder_level', $product->reorder_level ?? '') }}" class="{{ $inputClass }}">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used by MRP and low-stock alerts.</p>
        </div>

        <div class="sm:col-span-2">
            <label for="is_active" class="inline-flex items-center gap-2">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       @checked(old('is_active', $product->is_active ?? 1))
                       class="h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
            </label>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Inactive {{ $isMaterials ? 'materials' : 'SKUs' }} are hidden from new orders and BOMs.
            </p>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function bindPresetSelect(selectId, inputId) {
                    const select = document.getElementById(selectId);
                    const input = document.getElementById(inputId);
                    if (!select || !input) return;

                    const sync = () => {
                        if (select.value === '__custom') {
                            input.classList.remove('hidden');
                            input.style.display = 'block';
                            if (!input.value) input.focus();
                        } else {
                            input.classList.add('hidden');
                            input.style.display = 'none';
                            input.value = select.value;
                        }
                    };

                    select.addEventListener('change', sync);
                    sync();
                }

                bindPresetSelect('size_select', 'size');
                bindPresetSelect('volume_ml_select', 'volume_ml');
            });
        </script>
    @endpush
@endonce
