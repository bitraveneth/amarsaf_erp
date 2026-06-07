@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'default',
])

@php
    $toneClass = match ($tone) {
        'success' => 'text-success-400',
        'danger' => 'text-error-400',
        default => 'text-white',
    };
@endphp

<div {{ $attributes->merge(['class' => 'erp-dash-highlight']) }}>
    <p class="erp-dash-highlight__label">{{ $label }}</p>
    <p class="erp-dash-highlight__value {{ $toneClass }}">{{ $value }}</p>
    @if($hint)
        <p class="erp-dash-highlight__hint">{{ $hint }}</p>
    @endif
</div>
