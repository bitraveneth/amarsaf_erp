@props([
    'value',
    'tone' => '',
])

@php
    $display = is_string($value) ? trim($value) : (string) $value;
    $compact = preg_replace('/\s/u', '', $display) ?? $display;
    $length = strlen($compact);

    $sizeClass = match (true) {
        $length <= 2 => 'erp-metric-value--2xl',
        $length <= 4 => 'erp-metric-value--xl',
        $length <= 7 => 'erp-metric-value--lg',
        $length <= 10 => 'erp-metric-value--md',
        $length <= 14 => 'erp-metric-value--sm',
        default => 'erp-metric-value--xs',
    };
@endphp

<p
    {{ $attributes->merge(['class' => trim("erp-metric-value {$sizeClass} {$tone}")]) }}
    style="--metric-chars: {{ $length }}"
    title="{{ $display }}"
>{{ $display }}</p>
