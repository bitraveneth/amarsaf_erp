@props([])

@php
    $steps = [
        ['num' => '1', 'label' => 'Setup', 'desc' => 'Products, materials, packaging, tax classes', 'path' => route('admin.products.index')],
        ['num' => '2', 'label' => 'Procure', 'desc' => 'Purchase orders and goods receipts', 'path' => route('admin.purchase-orders.index')],
        ['num' => '3', 'label' => 'Produce', 'desc' => 'BOMs, production runs, and QC', 'path' => route('admin.production.index')],
        ['num' => '4', 'label' => 'Stock', 'desc' => 'Inventory, transfers, and adjustments', 'path' => route('admin.inventory.index')],
        ['num' => '5', 'label' => 'Sell', 'desc' => 'Sales orders, delivery, and invoicing', 'path' => route('admin.orders.index')],
        ['num' => '6', 'label' => 'Account', 'desc' => 'Journals, receipts, and reconciliation', 'path' => route('admin.accounting.dashboard')],
        ['num' => '7', 'label' => 'Report', 'desc' => 'P&L, aging, inventory, and executive views', 'path' => route('admin.reports.dashboard')],
    ];
@endphp

<section {{ $attributes->merge(['class' => 'dash-insights-section dash-insights-section--last']) }}>
    <x-dashboard.section-header
        title="Understand the ERP cycle"
        description="Follow the real business flow from setup through reporting."
        class="mb-4"
    >
        <x-slot:actions>
            <a href="{{ route('admin.learning-hub') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Learning Hub</a>
        </x-slot:actions>
    </x-dashboard.section-header>

    <div class="dash-erp-cycle">
        @foreach($steps as $step)
            <a href="{{ $step['path'] }}" class="dash-erp-cycle__step">
                <span class="dash-erp-cycle__num">{{ $step['num'] }}</span>
                <span class="dash-erp-cycle__label">{{ $step['label'] }}</span>
                <span class="dash-erp-cycle__desc">{{ $step['desc'] }}</span>
            </a>
        @endforeach
    </div>
</section>
