@props([
    'label' => 'More',
    'icon' => 'chevron',
])

<div
    {{ $attributes->merge(['class' => 'relative inline-block text-left']) }}
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        @click="open = !open"
        class="erp-btn-action"
        :aria-expanded="open.toString()"
        aria-haspopup="true"
    >
        @if($icon === 'dots')
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 15a1.5 1.5 0 110 3 1.5 1.5 0 010-3z" />
            </svg>
            <span class="sr-only">{{ $label }}</span>
        @else
            {{ $label }}
            <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        @click.outside="open = false"
        class="absolute right-0 z-40 mt-1.5 min-w-[11rem] origin-top-right overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-900"
    >
        {{ $slot }}
    </div>
</div>
