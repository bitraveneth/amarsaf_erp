@props([
    'title' => 'Quick actions',
    'subtitleCollapsed' => null,
    'subtitleExpanded' => null,
    'actions' => [],
])

@php
    $collapsed = $subtitleCollapsed ?? count($actions) . ' shortcuts · Click to expand';
    $expanded = $subtitleExpanded ?? 'Open common tasks in this module';
@endphp

<section
    {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900']) }}
    x-data="{ open: false }"
>
    <button
        type="button"
        class="flex w-full items-center justify-between gap-4 px-4 py-3.5 text-left transition hover:bg-gray-50/80 sm:px-5 dark:hover:bg-white/[0.02]"
        @click="open = !open"
        :aria-expanded="open"
        aria-controls="module-quick-actions-panel"
    >
        <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $title }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                <span x-show="!open">{{ $collapsed }}</span>
                <span x-show="open" x-cloak>{{ $expanded }}</span>
            </p>
        </div>

        <span class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            <span x-text="open ? 'Hide' : 'Show'"></span>
            <svg
                class="h-4 w-4 transition-transform duration-200"
                :class="open ? 'rotate-180' : ''"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </span>
    </button>

    <div
        id="module-quick-actions-panel"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="border-t border-gray-100 px-4 pb-4 pt-3 sm:px-5 sm:pb-5 dark:border-gray-800"
    >
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-6">
            @foreach($actions as $action)
                <a
                    href="{{ $action['href'] }}"
                    @class([
                        'group flex min-h-[4.5rem] flex-col items-center justify-center gap-2 rounded-xl border px-2 py-3 text-center transition',
                        'border-brand-200 bg-brand-50 text-brand-700 hover:border-brand-300 hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/15' => ! empty($action['primary']),
                        'border-gray-200 bg-gray-50/80 text-gray-700 hover:border-gray-300 hover:bg-white dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-200 dark:hover:border-gray-600 dark:hover:bg-gray-800' => empty($action['primary']),
                    ])
                >
                    <span @class([
                        'flex h-9 w-9 items-center justify-center rounded-lg',
                        'bg-brand-500 text-white shadow-sm' => ! empty($action['primary']),
                        'bg-white text-brand-600 ring-1 ring-gray-200 dark:bg-gray-900 dark:text-brand-400 dark:ring-gray-700' => empty($action['primary']),
                    ]) aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 0 0-2.654-9.874A3.75 3.75 0 0 0 17.25 6H9.75a3.75 3.75 0 0 0-3.548 2.524A49.902 49.902 0 0 0 3.506 18.376c-.039.62.469 1.124 1.09 1.124H8.25Z" />
                        </svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-xs font-semibold leading-tight">{{ $action['label'] }}</span>
                        <span class="mt-0.5 block truncate text-[10px] text-gray-500 group-hover:text-gray-600 dark:text-gray-400 dark:group-hover:text-gray-300">{{ $action['hint'] ?? '' }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
