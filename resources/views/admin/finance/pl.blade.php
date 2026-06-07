@extends('layouts.app')

@section('content')
@php
    $grossMargin = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;
    $netMargin = $netSales > 0 ? ($profit / $netSales) * 100 : 0;
    $operatingRatio = $netSales > 0 ? ((($cogs ?? 0) + $commissions + ($otherExpenses ?? 0) + $payroll) / $netSales) * 100 : 0;
    $profitTone = $profit >= 0 ? 'success' : 'danger';
@endphp

<div class="erp-dash-page">
    <x-dashboard.hero
        eyebrow="Profit & loss"
        title="Profit &amp; loss"
        subtitle="Income statement based on ledger activity and operating expenses for the selected period."
        :period="$periodLabel"
    >
        <x-slot:actions>
            @include('admin.finance.partials.print_button', ['label' => 'Print statement'])
        </x-slot:actions>
    </x-dashboard.hero>

    <x-dashboard.period-filter
        :action="route('admin.reports.pl')"
        :range="$range"
        :from="request('from', $from->toDateString())"
        :to="request('to', $to->toDateString())"
        :range-options="$rangeOptions"
    />

    <div class="erp-dash-kpi-grid">
        <x-dashboard.kpi label="Net sales" :value="$currencyCode . ' ' . number_format($netSales, 0)" hint="Sales revenue less returns" />
        <x-dashboard.kpi label="Gross profit" :tone="($grossProfit ?? 0) >= 0 ? 'success' : 'danger'" :value="$currencyCode . ' ' . number_format($grossProfit ?? 0, 0)" hint="Net sales less COGS" />
        <x-dashboard.kpi label="Net profit" :tone="$profitTone" :value="$currencyCode . ' ' . number_format($profit, 0)" :hint="$profit < 0 ? 'Loss for the period' : 'Profit for the period'" />
        <x-dashboard.kpi label="Net margin" :tone="$profitTone" :value="number_format($netMargin, 1) . '%'" hint="Net profit as share of net sales" />
    </div>

    <div class="erp-dash-statement">
        <div class="erp-dash-statement__head">
            <h2 class="erp-dash-panel__title">Income statement</h2>
            <p class="erp-dash-panel__subtitle">For the period ending {{ $to->format('d M Y') }}</p>
        </div>

        <table class="erp-dash-statement__table">
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <tr class="erp-dash-statement__section">
                    <td colspan="2">Revenue</td>
                </tr>
                <tr class="erp-dash-statement__row">
                    <td>Sales revenue</td>
                    <td class="text-success-600 dark:text-success-400">+{{ number_format($sales, 2) }}</td>
                </tr>
                <tr class="erp-dash-statement__row">
                    <td>Sales returns</td>
                    <td class="text-error-600 dark:text-error-400">-{{ number_format($returns, 2) }}</td>
                </tr>
                <tr class="erp-dash-statement__total">
                    <td>Net sales</td>
                    <td class="text-gray-900 dark:text-white">{{ number_format($netSales, 2) }}</td>
                </tr>

                <tr class="erp-dash-statement__section">
                    <td colspan="2">Cost of goods sold</td>
                </tr>
                <tr class="erp-dash-statement__row">
                    <td>
                        @if(($cogsSource ?? 'estimated') === 'gl')
                            Cost of goods sold (GL)
                        @else
                            Material costs (estimated)
                        @endif
                    </td>
                    <td class="text-error-600 dark:text-error-400">-{{ number_format($cogs ?? 0, 2) }}</td>
                </tr>

                <tr class="erp-dash-statement__total">
                    <td>Gross profit</td>
                    <td class="{{ ($grossProfit ?? 0) >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                        {{ ($grossProfit ?? 0) >= 0 ? '+' : '-' }}{{ number_format(abs($grossProfit ?? 0), 2) }}
                    </td>
                </tr>

                <tr class="erp-dash-statement__section">
                    <td colspan="2">Operating expenses</td>
                </tr>
                <tr class="erp-dash-statement__row">
                    <td>Commission expense</td>
                    <td class="text-error-600 dark:text-error-400">-{{ number_format($commissions, 2) }}</td>
                </tr>
                <tr class="erp-dash-statement__row">
                    <td>Other operating expenses</td>
                    <td class="text-error-600 dark:text-error-400">-{{ number_format($otherExpenses ?? 0, 2) }}</td>
                </tr>
                <tr class="erp-dash-statement__row">
                    <td>Payroll and allowances</td>
                    <td class="text-error-600 dark:text-error-400">-{{ number_format($payroll, 2) }}</td>
                </tr>

                <tr class="erp-dash-statement__total">
                    <td>Net profit {{ $profit < 0 ? '(Loss)' : '' }}</td>
                    <td class="{{ $profit >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                        {{ $profit >= 0 ? '+' : '-' }}{{ number_format(abs($profit), 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="erp-dash-kpi-grid md:grid-cols-3">
        <x-dashboard.kpi label="Gross margin" :tone="($grossProfit ?? 0) >= 0 ? 'success' : 'danger'" :value="number_format($grossMargin, 1) . '%'" hint="Of net sales" />
        <x-dashboard.kpi label="Operating ratio" tone="warning" :value="number_format($operatingRatio, 1) . '%'" hint="Costs relative to net sales" />
        <x-dashboard.kpi label="COGS source" tone="brand" :value="($cogsSource ?? 'estimated') === 'gl' ? 'General ledger' : 'Estimated'" hint="How COGS was calculated" />
    </div>

    <x-dashboard.panel title="Ledger detail" subtitle="Account activity for this period">
        @if(isset($accountRows) && $accountRows->isNotEmpty())
            <div class="erp-table-wrap">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th class="is-right">Debits</th>
                            <th class="is-right">Credits</th>
                            <th class="is-right">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accountRows as $row)
                            <tr>
                                <td>{{ $row['account'] }}</td>
                                <td class="is-right erp-table-num">{{ number_format($row['debit'], 2) }}</td>
                                <td class="is-right erp-table-num">{{ number_format($row['credit'], 2) }}</td>
                                <td class="is-right erp-table-num {{ $row['net'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                                    {{ $row['net'] >= 0 ? '+' : '-' }}{{ number_format(abs($row['net']), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="erp-body text-gray-500 dark:text-gray-400">No ledger entries found for this period.</p>
        @endif
    </x-dashboard.panel>
</div>
@endsection
