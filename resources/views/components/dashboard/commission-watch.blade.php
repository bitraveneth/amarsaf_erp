@props([
    'commission' => [],
])

@php
    $belowTarget = $commission['below_target'] ?? [];
    $pendingSettlements = (int) ($commission['pending_settlements'] ?? 0);
@endphp

<section {{ $attributes->merge(['class' => 'dash-insights-section']) }}>
    <x-dashboard.section-header
        title="Commission & targets"
        description="Agents behind pace and open commission settlements."
        class="mb-5"
    >
        <x-slot:actions>
            <a href="{{ route('admin.settlements.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Settlements</a>
        </x-slot:actions>
    </x-dashboard.section-header>

    <div class="dash-activity-grid dash-activity-grid--pair">
        <div class="dash-performance-widget h-full">
            <div class="dash-performance-widget-header">
                <div>
                    <h3 class="dash-performance-widget-title">Below 50% of target</h3>
                    <p class="dash-performance-widget-desc !mt-0">Agents who need a sales push this month</p>
                </div>
            </div>
            <div class="dash-performance-widget-body">
                @if(count($belowTarget) > 0)
                    <ul class="erp-dash-rank__list">
                        @foreach($belowTarget as $index => $item)
                            <li class="erp-dash-rank__item">
                                <span class="erp-dash-rank__index">{{ $index + 1 }}</span>
                                <div class="erp-dash-rank__content">
                                    <a href="{{ $item['href'] ?? '#' }}" class="erp-dash-rank__label erp-link">{{ $item['label'] }}</a>
                                    @if(! empty($item['meta']))
                                        <span class="erp-dash-rank__meta">{{ $item['meta'] }}</span>
                                    @endif
                                </div>
                                <span class="erp-dash-rank__value">{{ number_format((float) ($item['value'] ?? 0), 1) }}%</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">All targeted agents are at or above 50% this month.</p>
                @endif
            </div>
        </div>

        <a href="{{ route('admin.settlements.index') }}" class="dash-performance-widget h-full transition hover:border-brand-200 dark:hover:border-brand-500/30">
            <div class="dash-performance-widget-body flex flex-col justify-center gap-3 text-center">
                <span class="text-4xl font-bold tabular-nums text-gray-900 dark:text-white">{{ number_format($pendingSettlements) }}</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">Pending commission settlements</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Open or approved payouts waiting for payment</span>
                <span class="text-xs font-medium text-brand-600 dark:text-brand-300">Review settlements →</span>
            </div>
        </a>
    </div>
</section>
