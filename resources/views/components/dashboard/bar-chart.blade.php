@props([
    'title',
    'labels' => [],
    'values' => [],
    'secondary' => [],
    'primaryLabel' => 'Revenue',
    'secondaryLabel' => 'Collections',
    'currency' => null,
])

@php
    $max = max(1, ...array_map('floatval', array_merge($values, $secondary ?: [0])));
    $format = fn ($n) => $currency
        ? $currency . ' ' . number_format($n, 0)
        : number_format($n, 0);
@endphp

<div {{ $attributes->merge(['class' => 'erp-dash-chart']) }}>
    <div class="erp-dash-chart__legend">
        <span class="erp-dash-chart__legend-item"><span class="erp-dash-chart__swatch erp-dash-chart__swatch--primary"></span>{{ $primaryLabel }}</span>
        @if(! empty($secondary))
            <span class="erp-dash-chart__legend-item"><span class="erp-dash-chart__swatch erp-dash-chart__swatch--secondary"></span>{{ $secondaryLabel }}</span>
        @endif
    </div>

    @if(empty($labels))
        <p class="erp-body text-gray-500 dark:text-gray-400 py-8 text-center">No chart data for this period.</p>
    @else
        <div class="erp-dash-chart__grid">
            @foreach($labels as $index => $label)
                @php
                    $primary = (float) ($values[$index] ?? 0);
                    $second = (float) ($secondary[$index] ?? 0);
                @endphp
                <div class="erp-dash-chart__column">
                    <div class="erp-dash-chart__bars">
                        <div class="erp-dash-chart__bar erp-dash-chart__bar--primary" style="height: {{ max(4, ($primary / $max) * 100) }}%" title="{{ $primaryLabel }}: {{ $format($primary) }}"></div>
                        @if(! empty($secondary))
                            <div class="erp-dash-chart__bar erp-dash-chart__bar--secondary" style="height: {{ max(4, ($second / $max) * 100) }}%" title="{{ $secondaryLabel }}: {{ $format($second) }}"></div>
                        @endif
                    </div>
                    <span class="erp-dash-chart__label">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
