@extends('layouts.app')

@section('content')
@php
    $profitTone = $netProfitEstimate >= 0 ? 'success' : 'danger';
    $gapTone = $cashGap >= 0 ? 'success' : 'warning';
@endphp

<div class="erp-dash-page">
    <x-dashboard.hero
        eyebrow="Accounting"
        title="Accounting dashboard"
        subtitle="Invoice-basis sales, cash collections, operating costs, payroll, and management profit estimate."
        :period="$periodLabel"
    />

    <x-dashboard.period-filter
        :action="route('admin.accounting.dashboard')"
        :range="$range"
        :from="request('from', $from->toDateString())"
        :to="request('to', $to->toDateString())"
        :range-options="$rangeOptions"
    />

    <div class="erp-dash-layout-split">
        <x-dashboard.panel title="Cash and invoice trend" subtitle="Net sales vs receipts collected">
            <x-dashboard.bar-chart
                :labels="$chartLabels"
                :values="$chartRevenue"
                :secondary="$chartCollections"
                :currency="$currencyCode"
            />
        </x-dashboard.panel>

        <x-dashboard.highlight
            label="Management estimate"
            :value="$currencyCode . ' ' . number_format($netProfitEstimate, 0)"
            :tone="$profitTone"
            hint="Invoiced sales minus period expenses and payroll. Management estimate, not a formal statement."
        />
    </div>

    <div class="erp-dash-kpi-grid">
        <x-dashboard.kpi label="Net sales" :value="$currencyCode . ' ' . number_format($netSales, 0)" hint="Invoice basis before VAT" />
        <x-dashboard.kpi label="Collected receipts" tone="success" :value="$currencyCode . ' ' . number_format($collected, 0)" hint="Cash received in period" />
        <x-dashboard.kpi label="Outstanding" tone="warning" :value="$currencyCode . ' ' . number_format($outstanding, 0)" hint="Open receivables after credits" />
        <x-dashboard.kpi label="Cash after costs" :tone="$gapTone" :value="$currencyCode . ' ' . number_format($cashGap, 0)" hint="Receipts minus expenses and payroll" />
    </div>

    <div class="erp-dash-kpi-grid">
        <x-dashboard.kpi label="Invoices issued" :value="number_format($totalInvoices)" hint="Count in selected period" />
        <x-dashboard.kpi label="Collection rate" tone="brand" :value="number_format($collectionRate, 1) . '%'" hint="Receipts vs net sales" />
        <x-dashboard.kpi label="Operating expenses" tone="danger" :value="$currencyCode . ' ' . number_format($totalExpenses, 0)" hint="Expenses, gifts, campaigns" />
        <x-dashboard.kpi label="Payroll" tone="danger" :value="$currencyCode . ' ' . number_format($totalPayroll, 0)" hint="Salary distributions in period" />
    </div>

    <div class="erp-dash-layout-split">
        <x-dashboard.panel title="Invoice-basis view" subtitle="Invoicing and receivables">
            <div class="space-y-3">
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Net sales</div>
                        <div class="erp-dash-cost-row__hint">Invoice value before VAT</div>
                    </div>
                    <div>
                        <div class="erp-dash-cost-row__value !text-gray-900 dark:!text-white">{{ $currencyCode }} {{ number_format($netSales, 0) }}</div>
                    </div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">VAT tracked</div>
                        <div class="erp-dash-cost-row__hint">Invoice VAT in period</div>
                    </div>
                    <div class="erp-dash-cost-row__value !text-gray-900 dark:!text-white">{{ $currencyCode }} {{ number_format($vatTotal, 0) }}</div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Withholding tracked</div>
                        <div class="erp-dash-cost-row__hint">Expected customer withholding</div>
                    </div>
                    <div class="erp-dash-cost-row__value !text-gray-900 dark:!text-white">{{ $currencyCode }} {{ number_format($withholdingTotal, 0) }}</div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Outstanding balance</div>
                        <div class="erp-dash-cost-row__hint">Receivables still open</div>
                    </div>
                    <div class="erp-dash-cost-row__value !text-orange-600 dark:!text-orange-400">{{ $currencyCode }} {{ number_format($outstanding, 0) }}</div>
                </div>
            </div>
        </x-dashboard.panel>

        <x-dashboard.panel title="Cash-and-cost view" subtitle="Money in vs money out">
            <div class="space-y-3">
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Collected receipts</div>
                        <div class="erp-dash-cost-row__hint">Cash received in period</div>
                    </div>
                    <div class="erp-dash-cost-row__value !text-success-600 dark:!text-success-400">{{ $currencyCode }} {{ number_format($collected, 0) }}</div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Operating expenses</div>
                        <div class="erp-dash-cost-row__hint">Recorded spend in period</div>
                    </div>
                    <div class="erp-dash-cost-row__value">{{ $currencyCode }} {{ number_format($totalExpenses, 0) }}</div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Payroll and allowances</div>
                        <div class="erp-dash-cost-row__hint">Salary distributions</div>
                    </div>
                    <div class="erp-dash-cost-row__value">{{ $currencyCode }} {{ number_format($totalPayroll, 0) }}</div>
                </div>
                <div class="erp-dash-cost-row">
                    <div>
                        <div class="erp-dash-cost-row__label">Cash after costs</div>
                        <div class="erp-dash-cost-row__hint">Receipts minus outflows</div>
                    </div>
                    <div class="erp-dash-cost-row__value {{ $cashGap >= 0 ? '!text-success-600 dark:!text-success-400' : '!text-orange-600 dark:!text-orange-400' }}">{{ $currencyCode }} {{ number_format($cashGap, 0) }}</div>
                </div>
            </div>
        </x-dashboard.panel>
    </div>
</div>
@endsection
