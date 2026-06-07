@props([
    'title',
    'subtitle' => null,
    'badge' => null,
])

<section {{ $attributes->merge(['class' => 'erp-dash-panel']) }}>
    <div class="erp-dash-panel__header">
        <div>
            <h2 class="erp-dash-panel__title">{{ $title }}</h2>
            @if($subtitle)
                <p class="erp-dash-panel__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @if($badge)
            <span class="erp-dash-panel__badge">{{ $badge }}</span>
        @endif
    </div>

    <div class="erp-dash-panel__body">
        {{ $slot }}
    </div>
</section>
