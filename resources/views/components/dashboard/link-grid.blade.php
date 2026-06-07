@props([
    'links' => [],
])

<div {{ $attributes->merge(['class' => 'erp-dash-links']) }}>
    @foreach($links as $link)
        <a href="{{ $link['href'] }}" class="erp-dash-links__item">
            <span class="erp-dash-links__label">{{ $link['label'] }}</span>
            @if(! empty($link['hint']))
                <span class="erp-dash-links__hint">{{ $link['hint'] }}</span>
            @endif
        </a>
    @endforeach
</div>
