@props([
    'title',
    'subtitle' => null,
    'date' => null,
])

@php
    $dateLabel = null;
    $dateIso = null;

    if ($date instanceof \DateTimeInterface) {
        $dateLabel = $date->format('l, j F Y');
        $dateIso = $date->format('Y-m-d');
    } elseif (filled($date)) {
        $dateLabel = (string) $date;
    }
@endphp

<header {{ $attributes->merge(['class' => 'flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        <h1 class="erp-dash-h1">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
        @endif
    </div>

    @if($dateLabel)
        <p class="shrink-0 text-sm font-medium tabular-nums text-gray-500 dark:text-gray-400">
            @if($dateIso)
                <time datetime="{{ $dateIso }}">{{ $dateLabel }}</time>
            @else
                {{ $dateLabel }}
            @endif
        </p>
    @endif
</header>
