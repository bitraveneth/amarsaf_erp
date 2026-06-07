@extends('layouts.app')

@section('content')
<div class="erp-dash-page">
    <x-dashboard.hero
        eyebrow="Sales"
        title="Sales dashboard"
        subtitle="Commercial performance across invoices, collections, agents, and products."
        :period="$periodLabel"
    >
        <x-slot:actions>
            <a href="{{ route('admin.orders.create') }}" class="erp-btn-primary">New order</a>
            <a href="{{ route('admin.orders.index') }}" class="erp-btn-secondary">All orders</a>
        </x-slot:actions>
    </x-dashboard.hero>

    <x-dashboard.period-filter
        :action="route('admin.sales.dashboard')"
        :range="$range"
        :from="request('from', $from->toDateString())"
        :to="request('to', $to->toDateString())"
        :range-options="$rangeOptions"
    />

    <div class="erp-dash-kpi-grid">
        <x-dashboard.kpi label="Invoices" :value="number_format($totalInvoices)" hint="Issued in selected period" />
        <x-dashboard.kpi label="Net sales" :value="$currencyCode . ' ' . number_format($netSales, 0)" :hint="'VAT ' . number_format($vatTotal, 0) . ' · WH ' . number_format($withholdingTotal, 0)" />
        <x-dashboard.kpi label="Collected" tone="success" :value="$currencyCode . ' ' . number_format($collected, 0)" hint="Customer receipts in period" />
        <x-dashboard.kpi label="Outstanding" tone="warning" :value="$currencyCode . ' ' . number_format($outstanding, 0)" hint="Open balance after receipts and credits" />
    </div>

    <x-dashboard.panel title="Sales trend" subtitle="Net invoiced revenue vs collections">
        <x-dashboard.bar-chart
            :labels="$chartLabels"
            :values="$chartRevenue"
            :secondary="$chartCollections"
            :currency="$currencyCode"
        />
        <div class="mt-5">
            <x-dashboard.progress label="Collection rate" tone="success" :percent="$collectionRate" hint="Receipts collected vs net sales" />
        </div>
    </x-dashboard.panel>

    <div class="erp-dash-layout-thirds">
        <x-dashboard.panel title="Top agents" subtitle="By net sales">
            <x-dashboard.rank-list title="Agents" :currency="$currencyCode" :items="$topAgents" />
        </x-dashboard.panel>

        <x-dashboard.panel title="Top products" subtitle="By net invoice value">
            <x-dashboard.rank-list title="SKUs" :currency="$currencyCode" :items="$topProducts" />
        </x-dashboard.panel>

        <x-dashboard.panel title="Recent orders" subtitle="Deliveries in period">
            <x-dashboard.rank-list
                title="Orders"
                :currency="$currencyCode"
                :items="$recentOrders"
                empty="No orders in this period."
            />
        </x-dashboard.panel>
    </div>
</div>
@endsection
