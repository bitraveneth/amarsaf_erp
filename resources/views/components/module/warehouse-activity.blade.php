@props([
    'inbox' => [],
])

@if(count($inbox) > 0)
    <section {{ $attributes->merge(['class' => 'dash-procurement']) }}>
        <x-dashboard.section-header
            title="Dispatch inbox"
            description="Items that may need action today."
            class="mb-4"
        >
            <x-slot:actions>
                <a href="{{ route('admin.deliveries.pod-index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
                    Deliveries & POD
                </a>
            </x-slot:actions>
        </x-dashboard.section-header>

        <div class="dash-procurement__grid">
            @foreach($inbox as $item)
                <a href="{{ $item['href'] }}" @class([
                    'dash-procurement__card',
                    'dash-procurement__card--warn' => ($item['tone'] ?? '') === 'warn',
                    'dash-procurement__card--info' => ($item['tone'] ?? '') === 'info',
                ])>
                    <span class="dash-procurement__count">{{ number_format($item['count']) }}</span>
                    <span class="dash-procurement__label">{{ $item['label'] }}</span>
                    @if(! empty($item['cta']))
                        <span class="dash-procurement__cta">{{ $item['cta'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
