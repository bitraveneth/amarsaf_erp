@props([
    'delivery' => [],
])

<section {{ $attributes->merge(['class' => 'dash-insights-section']) }}>
    <x-dashboard.section-header
        title="Delivery & logistics"
        description="Orders due, in-transit deliveries, and POD completion this week."
        class="mb-4"
    >
        <x-slot:actions>
            <a href="{{ route('admin.deliveries.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Deliveries</a>
        </x-slot:actions>
    </x-dashboard.section-header>

    <div class="dash-supply-grid dash-supply-grid--four">
        <a href="{{ route('admin.orders.index', ['type' => 'sales']) }}" class="dash-supply-tile dash-supply-tile--brand">
            <span class="dash-supply-tile__value">{{ number_format($delivery['orders_due_today'] ?? 0) }}</span>
            <span class="dash-supply-tile__label">Due today</span>
            <span class="dash-supply-tile__caption">Sales orders scheduled today</span>
        </a>
        <a href="{{ route('admin.orders.index', ['type' => 'sales']) }}" class="dash-supply-tile dash-supply-tile--neutral">
            <span class="dash-supply-tile__value">{{ number_format($delivery['orders_due_week'] ?? 0) }}</span>
            <span class="dash-supply-tile__label">Due this week</span>
            <span class="dash-supply-tile__caption">Next 7 days of deliveries</span>
        </a>
        <a href="{{ route('admin.deliveries.index') }}" class="dash-supply-tile dash-supply-tile--warning">
            <span class="dash-supply-tile__value">{{ number_format($delivery['in_transit'] ?? 0) }}</span>
            <span class="dash-supply-tile__label">In transit</span>
            <span class="dash-supply-tile__caption">Active delivery runs</span>
        </a>
        <a href="{{ route('admin.reports.delivery-performance') }}" class="dash-supply-tile dash-supply-tile--success">
            <span class="dash-supply-tile__value">{{ number_format((float) ($delivery['pod_rate'] ?? 0), 1) }}%</span>
            <span class="dash-supply-tile__label">POD rate (7d)</span>
            <span class="dash-supply-tile__caption">Delivered with proof of delivery</span>
        </a>
    </div>
</section>
