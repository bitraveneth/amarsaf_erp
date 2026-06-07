@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'default',
])

@php
    $valueClass = match ($tone) {
        'success' => 'text-success-600 dark:text-success-400',
        'warning' => 'text-orange-600 dark:text-orange-400',
        'danger' => 'text-error-600 dark:text-error-400',
        default => '',
    };
@endphp

<div {{ $attributes->merge(['class' => 'erp-stat-card']) }}>
    <p class="erp-stat-label">{{ $label }}</p>
    <x-admin.metric-value :value="$value" class="mt-2 {{ $valueClass }}" />
    @if($hint)
        <p class="erp-stat-hint">{{ $hint }}</p>
    @endif
</div>
