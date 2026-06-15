@props([
    'tiles' => [],
])

@if(count($tiles) > 0)
    <section {{ $attributes->merge(['class' => 'dash-insights-section']) }}>
        <x-dashboard.section-header
            title="Supply chain snapshot"
            description="Production, quality, stock, and batch expiry at a glance."
            class="mb-4"
        />

        <div class="dash-supply-grid">
            @foreach($tiles as $tile)
                <a href="{{ $tile['href'] ?? '#' }}" class="dash-supply-tile dash-supply-tile--{{ $tile['tone'] ?? 'neutral' }}">
                    <span class="dash-supply-tile__value">{{ $tile['value'] ?? '0' }}</span>
                    <span class="dash-supply-tile__label">{{ $tile['label'] ?? '' }}</span>
                    <span class="dash-supply-tile__caption">{{ $tile['caption'] ?? '' }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
