@props([
    'value' => '',
    'label' => 'SKU',
    'required' => true,
    'productType' => 'finished',
])

@php
    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-mono uppercase tracking-wide text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400';
    $labelClass = 'mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300';
    $btnClass = 'inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]';
@endphp

<div x-data="skuField(@js([
    'initial' => old('sku', $value),
    'productType' => $productType,
    'suggestUrl' => route('admin.products.suggest-sku'),
    'hint' => \App\Support\SkuGenerator::formatHint(),
]))">
    <label for="sku" class="{{ $labelClass }}">
        {{ $label }}
        @if($required)
            <span class="text-error-500">*</span>
        @endif
    </label>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
        <input type="text"
               id="sku"
               name="sku"
               x-model="sku"
               @blur="sku = normalizeSku(sku)"
               @if($required) required @endif
               placeholder="SAF-500ML-CTN"
               class="{{ $inputClass }}" />
        <button type="button" @click="generateSku()" class="{{ $btnClass }}">
            Generate SKU
        </button>
    </div>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="hint"></p>
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
                            product_type: config.productType || 'finished',
                            size: document.getElementById('size')?.value || '',
                            uom: document.getElementById('uom')?.value || '',
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
