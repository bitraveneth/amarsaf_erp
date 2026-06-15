@props([
    'movements' => [],
])

<section {{ $attributes->merge(['class' => 'dash-insights-section']) }}>
    <x-dashboard.section-header
        title="Inventory movement"
        description="Latest stock receipts, issues, and adjustments."
        class="mb-5"
    >
        <x-slot:actions>
            <a href="{{ route('admin.stock.movements') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">All movements</a>
        </x-slot:actions>
    </x-dashboard.section-header>

    <div class="dash-performance-widget">
        @if(count($movements) > 0)
            <ul class="dash-inventory-feed">
                @foreach($movements as $movement)
                    <li>
                        <a href="{{ $movement['href'] ?? route('admin.stock.movements') }}" class="dash-inventory-feed__item">
                            <span class="dash-inventory-feed__main">
                                <span class="dash-inventory-feed__label">{{ $movement['label'] ?? 'Movement' }}</span>
                                <span class="dash-inventory-feed__meta">{{ $movement['meta'] ?? '' }}</span>
                            </span>
                            <span class="dash-inventory-feed__qty">{{ number_format((float) ($movement['value'] ?? 0), 0) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="dash-performance-widget-body">
                <p class="text-sm text-gray-500 dark:text-gray-400">No stock movements recorded yet.</p>
            </div>
        @endif
    </div>
</section>
