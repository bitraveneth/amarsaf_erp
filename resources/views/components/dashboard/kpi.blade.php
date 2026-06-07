@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'default',
])

@php
    $toneClass = match ($tone) {
        'success' => 'erp-dash-kpi--success',
        'warning' => 'erp-dash-kpi--warning',
        'danger' => 'erp-dash-kpi--danger',
        'brand' => 'erp-dash-kpi--brand',
        default => '',
    };
@endphp

<article {{ $attributes->merge(['class' => 'erp-dash-kpi ' . $toneClass]) }}>
    <p class="erp-dash-kpi__label">{{ $label }}</p>
    <x-admin.metric-value :value="$value" class="erp-dash-kpi__value" />
    @if($hint)
        <p class="erp-dash-kpi__hint">{{ $hint }}</p>
    @endif
</article>
