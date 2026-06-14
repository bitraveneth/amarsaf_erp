@props([
    'value' => '',
    'names' => [],
    'presets' => [],
    'required' => true,
])

@php
    $inputClass = 'w-full rounded-lg border-0 bg-transparent px-3 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-0 dark:text-white dark:placeholder-gray-400';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
@endphp

<div x-data="productNameField(@js([
    'initial' => old('name', $value),
    'names' => array_values($names),
    'presets' => array_values($presets),
]))"
     class="relative"
     @click.outside="open = false">
    <label for="name" class="{{ $labelClass }}">
        Product name
        @if($required)
            <span class="text-error-500">*</span>
        @endif
    </label>
    <div class="flex overflow-hidden rounded-lg border border-gray-300 bg-white shadow-theme-xs transition focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800">
        <input type="text"
               id="name"
               name="name"
               x-model="query"
               @focus="open = true"
               @input="open = true"
               @keydown.escape.prevent="open = false"
               @if($required) required @endif
               autocomplete="off"
               placeholder="Choose or type a product name"
               class="{{ $inputClass }}">
        <button type="button"
                @click="open = !open"
                :aria-expanded="open"
                class="inline-flex shrink-0 items-center border-l border-gray-200 px-2.5 text-gray-500 transition hover:bg-gray-50 hover:text-gray-700 dark:border-gray-700 dark:hover:bg-gray-900/60 dark:hover:text-gray-300"
                title="Show product names">
            <svg class="size-4 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Choose from the list or type a custom name.</p>

    <div x-show="open"
         x-transition.origin.top
         x-cloak
         class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-900">
        <template x-if="filtered.length === 0">
            <p class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">No matches — press Tab to use your text.</p>
        </template>
        <template x-for="name in filtered" :key="name">
            <button type="button"
                    @mousedown.prevent="select(name)"
                    class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm text-gray-800 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800/80">
                <span x-text="name" class="min-w-0 truncate"></span>
                <span x-show="isPreset(name)"
                      class="shrink-0 rounded bg-brand-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    Standard
                </span>
            </button>
        </template>
    </div>

    @error('name')
        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
    @enderror
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('productNameField', (config) => ({
                    query: config.initial || '',
                    open: false,
                    names: config.names || [],
                    presets: config.presets || [],
                    presetNameSet: new Set((config.presets || []).map((row) => row.name)),
                    get filtered() {
                        const q = String(this.query || '').toLowerCase().trim();
                        const list = this.names || [];

                        if (!q) {
                            return list.slice(0, 10);
                        }

                        return list.filter((name) => name.toLowerCase().includes(q)).slice(0, 10);
                    },
                    isPreset(name) {
                        return this.presetNameSet.has(name);
                    },
                    select(name) {
                        this.query = name;
                        this.open = false;

                        const preset = (this.presets || []).find((row) => row.name === name);
                        if (preset) {
                            window.dispatchEvent(new CustomEvent('product-name-preset', { detail: preset }));
                        }
                    },
                }));
            });
        </script>
    @endpush
@endonce
