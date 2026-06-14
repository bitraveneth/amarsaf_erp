@props([
    'value' => '',
    'label' => 'SKU',
    'required' => true,
    'productType' => 'finished',
    'materialHint' => null,
    'readonly' => false,
    'compact' => false,
])

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-mono uppercase tracking-wide text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
    $btnClass = $compact
        ? 'inline-flex shrink-0 items-center rounded-lg border border-gray-300 bg-white px-2.5 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300'
        : 'inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]';
@endphp

<div x-data="skuField(@js([
    'initial' => old('sku', $value),
    'productType' => $productType,
    'suggestUrl' => route('admin.products.suggest-sku'),
    'hint' => $materialHint ?: \App\Support\SkuGenerator::formatHint(),
]))" @class(['max-w-md' => $compact])>
    <label for="sku" class="{{ $labelClass }}">
        {{ $label !== '' ? $label : 'SKU' }}
        @if($required)
            <span class="text-error-500">*</span>
        @endif
    </label>
    <div @class(['flex items-center gap-2' => $compact, 'flex flex-col gap-2 sm:flex-row sm:items-start' => ! $compact])>
        <input type="text"
               id="sku"
               name="sku"
               @unless($readonly) x-model="sku" @endunless
               value="{{ old('sku', $value) }}"
               @unless($readonly) @blur="sku = normalizeSku(sku)" @endunless
               @if($required) required @endif
               @if($readonly) readonly @endif
               placeholder="SAF-500ML-CTN"
               class="{{ $inputClass }} {{ $readonly ? 'cursor-not-allowed bg-gray-50 dark:bg-gray-800/50' : '' }}" />
        @unless($readonly)
            <button type="button" @click="generateSku()" class="{{ $btnClass }}">
                {{ $compact ? 'Generate' : 'Generate SKU' }}
            </button>
        @endunless
    </div>
    @unless($readonly)
        <p @class(['mt-1 text-xs text-gray-500 dark:text-gray-400', 'truncate' => $compact]) x-text="hint"></p>
    @else
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Fixed after creation.</p>
    @endunless
    @error('sku')
        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
    @enderror
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('skuField', (config) => ({
                    sku: config.initial || '',
                    hint: config.hint,
                    async generateSku() {
                        const params = new URLSearchParams({
                            product_type: document.getElementById('product_type')?.value || config.productType || 'finished',
                            size: document.getElementById('size')?.value || '',
                            uom: document.getElementById('uom')?.value || '',
                            material_category_id: document.getElementById('material_category_id')?.value || '',
                        });

                        const response = await fetch(`${config.suggestUrl}?${params.toString()}`, {
                            headers: { 'Accept': 'application/json' },
                        });

                        if (!response.ok) {
                            return;
                        }

                        const payload = await response.json();
                        this.sku = payload.sku || this.sku;
                        if (payload.hint) {
                            this.hint = payload.hint;
                        }
                    },
                    normalizeSku(value) {
                        return String(value || '')
                            .toUpperCase()
                            .trim()
                            .replace(/[\s_]+/g, '-')
                            .replace(/[^A-Z0-9-]+/g, '')
                            .replace(/-+/g, '-')
                            .replace(/^-|-$/g, '');
                    },
                }));
            });
        </script>
    @endpush
@endonce
