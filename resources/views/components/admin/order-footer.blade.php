@props([
    'submitLabel' => 'Save order',
    'cancelUrl' => null,
    'hint' => null,
])

<footer class="erp-order-footer">
    @if($hint)
        <p class="erp-order-footer__hint">{{ $hint }}</p>
    @else
        <span></span>
    @endif

    <div class="erp-order-footer__actions">
        @if($cancelUrl)
            <a href="{{ $cancelUrl }}" class="erp-order-btn erp-order-btn--secondary">Cancel</a>
        @endif
        @isset($actions)
            {{ $actions }}
        @endisset
        <button type="submit" {{ $attributes->merge(['class' => 'erp-order-btn erp-order-btn--primary']) }}>
            {{ $submitLabel }}
        </button>
    </div>
</footer>
