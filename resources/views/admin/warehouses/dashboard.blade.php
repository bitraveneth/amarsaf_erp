@extends('layouts.app')

@section('content')
<div class="dash-page">
    <x-dashboard.page-header title="Warehouses" />

    <x-module.take-action-bar
        class="mt-1"
        description="Manage sites, plan shipments, schedule deliveries, and run last-mile dispatch."
        :groups="$takeActionGroups"
    />

    <x-dashboard.snapshot-kpis
        class="mt-2"
        eyebrow="Warehouse snapshot"
        title="Network at a glance"
        description="Sites, fleet, routes, dispatch status, and stock on hand."
        :cards="$snapshotCards"
    />

    <x-module.dispatch-board class="mt-2" :board="$todayDispatchBoard" />

    <x-module.warehouse-stock-breakdown class="mt-2" :warehouses="$warehouseStockBreakdown" />

    <section class="dash-recent-orders-section mt-2">
        <x-dashboard.section-header
            title="Recent deliveries"
            description="Latest dispatch runs and POD updates from your fleet."
            class="mb-5"
        >
            <x-slot:actions>
                <a href="{{ route('admin.deliveries.pod-index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
                    See all deliveries
                </a>
            </x-slot:actions>
        </x-dashboard.section-header>

        <x-module.recent-deliveries
            :show-header="false"
            :deliveries="$recentDeliveries"
            :currency-code="$currencyCode"
        />
    </section>
</div>
@endsection
