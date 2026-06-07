@props([
    'href' => null,
    'external' => false,
    'danger' => false,
])

@php
    $classes = $danger
        ? 'flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10'
        : 'flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/[0.03]';
@endphp

@if($href)
    <a
        href="{{ $href }}"
        @class([$classes])
        @if($external) target="_blank" rel="noopener" @endif
    >
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
