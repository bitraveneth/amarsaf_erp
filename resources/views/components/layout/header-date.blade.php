@props([
    'date' => null,
    'compact' => false,
])

@php
    $now = ($date ?? now())->locale(app()->getLocale());
    $dateIso = $now->toDateString();
    $dayName = $now->translatedFormat('l');
    $dateLabel = $now->translatedFormat('j F Y');
    $monthLabel = $now->translatedFormat('F Y');
    $dayNumber = $now->format('j');
    $monthShort = $now->translatedFormat('M');
@endphp

@if($compact === 'toolbar')
    <div {{ $attributes->merge(['class' => 'header-date-toolbar']) }}>
        <span class="header-date-toolbar__badge" aria-hidden="true">
            <span class="header-date-toolbar__month">{{ $monthShort }}</span>
            <span class="header-date-toolbar__day">{{ $dayNumber }}</span>
        </span>
        <time datetime="{{ $dateIso }}" class="header-date-toolbar__label">
            {{ $dayName }}
        </time>
    </div>
@elseif($compact)
    <div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/90 px-2.5 py-1.5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/90']) }}>
        <span class="flex h-8 w-8 flex-col items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-sm">
            <span class="text-[9px] font-bold uppercase leading-none tracking-wide">{{ $monthShort }}</span>
            <span class="text-sm font-bold leading-none tabular-nums">{{ $dayNumber }}</span>
        </span>
        <time datetime="{{ $dateIso }}" class="max-w-[7rem] truncate text-[11px] font-semibold leading-tight text-gray-700 dark:text-gray-200">
            {{ $dayName }}
        </time>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 rounded-2xl border border-gray-200/90 bg-white/90 px-3 py-2 shadow-theme-xs backdrop-blur-sm dark:border-gray-800 dark:bg-gray-900/90']) }}>
        <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-sm" aria-hidden="true">
            <span class="text-[10px] font-bold uppercase leading-none tracking-[0.14em] text-white/90">{{ $monthShort }}</span>
            <span class="text-lg font-bold leading-none tabular-nums">{{ $dayNumber }}</span>
        </div>

        <div class="min-w-0 pr-1 text-left leading-tight">
            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-brand-600 dark:text-brand-400">{{ $monthLabel }}</p>
            <p class="mt-0.5 text-sm font-semibold tabular-nums text-gray-900 dark:text-white">
                <time datetime="{{ $dateIso }}">{{ $dayName }}, {{ $dateLabel }}</time>
            </p>
        </div>
    </div>
@endif
