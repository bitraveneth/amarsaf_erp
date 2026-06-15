@extends('layouts.app')

@section('content')
@php
    use App\Support\ReportsCatalog;

    $profitTone = $netProfitEstimate >= 0 ? 'brand' : 'danger';
    $periodQuery = request()->only(['range', 'from', 'to']);
    $reportGroups = ReportsCatalog::groups($periodQuery);
    $featuredReports = ReportsCatalog::featured($periodQuery);
    $snapshotCards = [
        [
            'label' => 'Net sales',
            'numeric' => number_format($grossRevenue, 0),
            'currency' => $currencyCode,
            'caption' => 'Invoiced sales after credits',
            'href' => route('admin.reports.pl', $periodQuery),
            'tone' => 'brand',
            'valueTone' => 'brand',
            'icon' => 'revenue',
        ],
        [
            'label' => 'Collections',
            'numeric' => number_format($totalCollections, 0),
            'currency' => $currencyCode,
            'caption' => 'Receipts in ' . strtolower($periodLabel),
            'href' => route('admin.reports.cashflow', $periodQuery),
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'receivables',
        ],
        [
            'label' => 'Outstanding',
            'numeric' => number_format($outstanding, 0),
            'currency' => $currencyCode,
            'caption' => 'Open receivables at period end',
            'href' => route('admin.reports.ar-aging'),
            'tone' => 'orange',
            'valueTone' => $outstanding > 0 ? 'warning' : 'neutral',
            'icon' => 'receivables',
        ],
        [
            'label' => 'Est. net profit',
            'numeric' => number_format($netProfitEstimate, 0),
            'currency' => $currencyCode,
            'caption' => 'Management estimate — see income statement',
            'href' => route('admin.reports.pl', $periodQuery),
            'tone' => 'purple',
            'valueTone' => $profitTone,
            'icon' => 'revenue',
        ],
        [
            'label' => 'Production output',
            'numeric' => number_format($productionQty, 0),
            'caption' => 'Approved quantity in period',
            'href' => route('admin.reports.production', $periodQuery),
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'Active agents',
            'numeric' => number_format($activeAgents),
            'caption' => 'Selling partners in network',
            'href' => route('admin.reports.agents', $periodQuery),
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'users',
        ],
        [
            'label' => 'Operating spend',
            'numeric' => number_format($totalExpenses, 0),
            'currency' => $currencyCode,
            'caption' => 'Expenses, gifts, and campaigns',
            'href' => route('admin.reports.expense-summary', $periodQuery),
            'tone' => 'error',
            'valueTone' => $totalExpenses > 0 ? 'warning' : 'neutral',
            'icon' => 'orders',
        ],
        [
            'label' => 'Collection rate',
            'numeric' => number_format($collectionRate, 1) . '%',
            'caption' => 'Receipts vs net sales',
            'href' => route('admin.reports.agents', $periodQuery),
            'tone' => 'amber',
            'valueTone' => $collectionRate >= 80 ? 'neutral' : 'warning',
            'icon' => 'delivery',
        ],
    ];
@endphp

<div class="dash-page">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <x-dashboard.page-header
            title="Reports dashboard"
            subtitle="Key figures for the selected period, then open any detailed report."
        />
        <div class="flex flex-wrap items-center gap-2 print:hidden">
            <x-dashboard.period-filter
                variant="compact"
                :action="route('admin.reports.dashboard')"
                :range="$range"
                :from="request('from', $from->toDateString())"
                :to="request('to', $to->toDateString())"
                :range-options="$rangeOptions"
                :period-label="$periodLabel"
            />
            @include('admin.finance.partials.print_button', ['label' => 'Print'])
            <a href="{{ route('admin.exports.month-end-pack.download', $periodQuery) }}" class="erp-btn-secondary text-sm">Month-end pack</a>
        </div>
    </div>

    <x-dashboard.snapshot-kpis
        class="mt-2"
        eyebrow="Period snapshot"
        :title="$periodLabel"
        description="Click a card to open the related report. Figures are estimates unless you open a formal statement."
        :cards="$snapshotCards"
    />

    <section class="dash-performance mt-8">
        <x-dashboard.section-header
            title="Revenue and collections"
            description="Net invoiced sales compared with cash collected in the selected period."
            class="mb-5"
        >
            <x-slot:actions>
                <a href="{{ route('admin.reports.pl', $periodQuery) }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Income statement</a>
                <a href="{{ route('admin.reports.cashflow', $periodQuery) }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Cash flow</a>
            </x-slot:actions>
        </x-dashboard.section-header>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <x-dashboard.bar-chart
                    :labels="$chartLabels"
                    :values="$chartRevenue"
                    :secondary="$chartCollections"
                    :currency="$currencyCode"
                />
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <x-dashboard.progress label="Collection efficiency" tone="success" :percent="$collectionRate" hint="Collections as a share of net revenue" />
                    <x-dashboard.progress
                        label="Receivable pressure"
                        tone="warning"
                        :percent="$grossRevenue > 0 ? ($outstanding / $grossRevenue) * 100 : 0"
                        hint="Outstanding relative to revenue"
                    />
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <x-dashboard.section-header
                    title="Cost mix"
                    description="Estimated spend buckets in period."
                    class="mb-4"
                />
                <x-dashboard.bar-chart
                    :labels="$costChartLabels"
                    :values="$costChartValues"
                    primary-label="Cost"
                    :currency="$currencyCode"
                />
                <dl class="mt-5 space-y-3 border-t border-gray-100 pt-4 text-sm dark:border-gray-800">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Est. COGS</dt>
                        <dd class="font-semibold tabular-nums text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($cogsEstimate, 0) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Commissions</dt>
                        <dd class="font-semibold tabular-nums text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($commissionsTotal, 0) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Payroll</dt>
                        <dd class="font-semibold tabular-nums text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($totalPayroll, 0) }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <section class="dash-activity-panel mt-8">
        <x-dashboard.section-header
            title="Top agents"
            description="Highest net sales in the selected period."
            class="mb-5"
        >
            <x-slot:actions>
                <a href="{{ route('admin.reports.agents', $periodQuery) }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Agent performance</a>
            </x-slot:actions>
        </x-dashboard.section-header>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <x-dashboard.rank-list title="Agents" :currency="$currencyCode" :items="$topAgents" />
        </div>
    </section>

    <section class="mt-8">
        <x-dashboard.reports-library
            :groups="$reportGroups"
            :featured="$featuredReports"
        />
    </section>
</div>
@endsection
