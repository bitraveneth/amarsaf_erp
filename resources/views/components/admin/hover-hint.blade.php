@props([
    'title',
    'text' => null,
])

<div {{ $attributes->class(['erp-hover-hint']) }}>
    {{ $slot }}
    <div class="erp-hover-hint__card" role="tooltip">
        <p class="erp-hover-hint__title">{{ $title }}</p>
        @if($text)
            <p class="erp-hover-hint__text">{{ $text }}</p>
        @endif
    </div>
</div>
