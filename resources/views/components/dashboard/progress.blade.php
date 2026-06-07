@props([
    'label',
    'percent' => 0,
    'hint' => null,
    'tone' => 'brand',
])

@php
    $width = min(max((float) $percent, 0), 100);
    $toneClass = match ($tone) {
        'success' => 'erp-dash-progress--success',
        'warning' => 'erp-dash-progress--warning',
        default => '',
    };
@endphp

<div {{ $attributes->merge(['class' => 'erp-dash-progress ' . $toneClass]) }}>
    <div class="erp-dash-progress__head">
        <span class="erp-dash-progress__label">{{ $label }}</span>
        <span class="erp-dash-progress__value">{{ number_format($width, 1) }}%</span>
    </div>
    <div class="erp-dash-progress__track">
        <div class="erp-dash-progress__fill" style="width: {{ $width }}%"></div>
    </div>
    @if($hint)
        <p class="erp-dash-progress__hint">{{ $hint }}</p>
    @endif
</div>
