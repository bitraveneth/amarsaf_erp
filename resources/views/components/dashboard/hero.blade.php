@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'period' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        @if($eyebrow)
            <p class="erp-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 @class(['erp-dash-h1', 'mt-1' => $eyebrow])>{{ $title }}</h1>
        @if($subtitle)
            <p class="erp-page-subtitle mt-1">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="flex shrink-0 flex-col items-start gap-3 sm:items-end">
        @if($period)
            <span class="dash-period-badge">{{ $period }}</span>
        @endif

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
