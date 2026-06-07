@extends('layouts.app')

@section('content')
@php
    $profitTone = $netProfitEstimate >= 0 ? 'success' : 'danger';
    $profitShare = $grossRevenue > 0 ? ($netProfitEstimate / $grossRevenue) * 100 : 0;
    $reportLinks = [
        ['href' => route('admin.reports.pl', request()->only(['range', 'from', 'to'])), 'label' => 'Profit & loss', 'hint' => 'Formal income statement'],
        ['href' => route('admin.reports.bs'), 'label' => 'Balance sheet', 'hint' => 'Assets, liabilities, equity'],
        ['href' => route('admin.reports.cashflow'), 'label' => 'Cash flow', 'hint' => 'Cash in and out'],
        ['href' => route('admin.reports.vat'), 'label' => 'VAT report', 'hint' => 'VAT summary'],
        ['href' => route('admin.reports.agents'), 'label' => 'Agent performance', 'hint' => 'Agent rankings'],
        ['href' => route('admin.reports.production'), 'label' => 'Production reports', 'hint' => 'Factory output'],
    ];
@endphp

<div class="erp-dash-page">
    <x-dashboard.hero
        eyebrow="Reports"
        title="Reports dashboard"
        subtitle="Executive view of revenue, collections, cost structure, and operational output for the selected period."
        :period="$periodLabel"
    >
        <x-slot:actions>
            @include('admin.finance.partials.print_button', ['label' => 'Print'])
        </x-slot:actions>
    </x-dashboard.hero>

    <x-dashboard.period-filter
        :action="route('admin.reports.dashboard')"
        :range="$range"
        :from="request('from', $from->toDateString())"
        :to="request('to', $to->toDateString())"
        :range-options="$rangeOptions"
    />

    <div class="erp-dash-kpi-grid">
        <x-dashboard.kpi label="Net sales" :value="$currencyCode . ' ' . number_format($grossRevenue, 0)" hint="Invoiced sales after credits" />
        <x-dashboard.kpi label="Collections" tone="success" :value="$currencyCode . ' ' . number_format($totalCollections, 0)" hint="Receipts applied in period" />
        <x-dashboard.kpi label="Outstanding" tone="warning" :value="$currencyCode . ' ' . number_format($outstanding, 0)" hint="Open receivables as of period end" />
        <x-dashboard.kpi label="Est. net profit" :tone="$profitTone" :value="$currencyCode . ' ' . number_format($netProfitEstimate, 0)" hint="Revenue less COGS, commissions, expenses, payroll" />
    </div>

    <div class="erp-dash-layout-split">
        <x-dashboard.panel title="Revenue trend" subtitle="Net invoiced revenue vs collections" badge="Chart">
            <x-dashboard.bar-chart
                :labels="$chartLabels"
                :values="$chartRevenue"
                :secondary="$chartCollections"
                :currency="$currencyCode"
            />
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <x-dashboard.progress label="Collection efficiency" tone="success" :percent="$collectionRate" hint="Collections as a share of net revenue" />
                <x-dashboard.progress
                    label="Receivable pressure"
                    tone="warning"
                    :percent="$grossRevenue > 0 ? ($outstanding / $grossRevenue) * 100 : 0"
                    hint="Outstanding balance relative to revenue"
                />
            </div>
        </x-dashboard.panel>

        <div class="space-y-5">
            <x-dashboard.highlight
                label="Estimated net result"
                :value="$currencyCode . ' ' . number_format($netProfitEstimate, 0)"
                :tone="$profitTone"
                hint="Approximate profit after tracked COGS, commissions, operating spend, and payroll."
            />

            <x-dashboard.panel title="Cost mix" subtitle="Share of revenue consumed by major cost buckets">
                <x-dashboard.bar-chart
                    :labels="$costChartLabels"
                    :values="$costChartValues"
                    primary-label="Cost"
                    :currency="$currencyCode"
                />
            </x-dashboard.panel>
        </div>
    </div>

    <div class="erp-dash-layout-thirds">
        <x-dashboard.panel title="Top agents" subtitle="By net sales in period">
            <x-dashboard.rank-list title="Agents" :currency="$currencyCode" :items="$topAgents" />
        </x-dashboard.panel>

        <x-dashboard.panel title="Operations" subtitle="Production and network footprint">
            <div class="erp-dash-kpi-grid !grid-cols-1 sm:!grid-cols-2">
                <x-dashboard.kpi label="Approved output" :value="number_format($productionQty, 0)" hint="Approved production quantity" />
                <x-dashboard.kpi label="Active agents" :value="number_format($activeAgents)" hint="Selling partners in network" />
            </div>
        </x-dashboard.panel>

        <x-dashboard.panel title="Drill down" subtitle="Open detailed statements" class="border-dashed">
            <x-dashboard.link-grid :links="$reportLinks" />
        </x-dashboard.panel>
    </div>
</div>
@endsection
