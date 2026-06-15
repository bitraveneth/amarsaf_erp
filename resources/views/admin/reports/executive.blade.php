@extends('layouts.app')

@section('content')
@php
    use App\Support\ReportsCatalog;
    $periodQuery = request()->only(['range', 'from', 'to']);
    $reportGroups = ReportsCatalog::groups($periodQuery);
    $featuredReports = ReportsCatalog::featured($periodQuery);
    $snapshotCards = [
        ['label' => 'Net sales', 'numeric' => number_format($grossRevenue, 0), 'currency' => $currencyCode, 'caption' => 'Invoiced sales after credits', 'href' => route('admin.reports.pl', $periodQuery), 'tone' => 'brand', 'valueTone' => 'brand', 'icon' => 'revenue'],
        ['label' => 'Collections', 'numeric' => number_format($totalCollections, 0), 'currency' => $currencyCode, 'caption' => 'Receipts in period', 'href' => route('admin.reports.cashflow', $periodQuery), 'tone' => 'success', 'valueTone' => 'neutral', 'icon' => 'receivables'],
        ['label' => 'Outstanding', 'numeric' => number_format($outstanding, 0), 'currency' => $currencyCode, 'caption' => 'Open receivables', 'href' => route('admin.reports.outstanding-invoices'), 'tone' => 'orange', 'valueTone' => $outstanding > 0 ? 'warning' : 'neutral', 'icon' => 'receivables'],
        ['label' => 'Est. net profit', 'numeric' => number_format($netProfitEstimate, 0), 'currency' => $currencyCode, 'caption' => 'See income statement', 'href' => route('admin.reports.pl', $periodQuery), 'tone' => 'purple', 'valueTone' => $netProfitEstimate >= 0 ? 'brand' : 'danger', 'icon' => 'revenue'],
        ['label' => 'Operating spend', 'numeric' => number_format($totalExpenses, 0), 'currency' => $currencyCode, 'caption' => 'Expenses, gifts, campaigns', 'href' => route('admin.reports.expense-summary', $periodQuery), 'tone' => 'error', 'valueTone' => $totalExpenses > 0 ? 'warning' : 'neutral', 'icon' => 'orders'],
        ['label' => 'Production output', 'numeric' => number_format($productionQty, 0), 'caption' => 'Approved quantity', 'href' => route('admin.reports.production', $periodQuery), 'tone' => 'blue', 'valueTone' => 'neutral', 'icon' => 'production'],
    ];
@endphp

<x-report.page eyebrow="Executive" title="Executive summary" subtitle="Management KPI rollup for the selected period." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.executive')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.export-actions module="executive-summary" :from="$from" :to="$to" />
            <a href="{{ route('admin.exports.month-end-pack.download', $periodQuery) }}" class="erp-btn-secondary text-sm">Month-end pack</a>
        </x-report.header-actions>
    </x-slot:actions>

    <x-dashboard.snapshot-kpis class="mb-6" eyebrow="Period snapshot" :title="$periodLabel" description="Click a card to drill into the related report." :cards="$snapshotCards" />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2 mb-8">
        <x-dashboard.panel title="Revenue vs collections">
            <x-dashboard.bar-chart :labels="$chartLabels" :values="$chartRevenue" :secondary="$chartCollections" :currency="$currencyCode" />
        </x-dashboard.panel>
        <x-dashboard.panel title="Cost mix">
            <x-dashboard.bar-chart :labels="$costChartLabels" :values="$costChartValues" primary-label="Cost" :currency="$currencyCode" />
        </x-dashboard.panel>
    </div>

    <x-dashboard.reports-library :groups="$reportGroups" :featured="$featuredReports" />
</x-report.page>
@endsection
