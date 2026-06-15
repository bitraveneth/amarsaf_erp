@extends('layouts.app')

@section('content')
@php
    $netTone = $net >= 0 ? 'success' : 'danger';
    $ratioTone = $ratio >= 1.5 ? 'success' : ($ratio >= 1 ? 'brand' : 'danger');
    $avgIn = $days > 0 ? $cashIn / $days : 0;
    $avgOut = $days > 0 ? $cashOut / $days : 0;
    $avgNet = $days > 0 ? $net / $days : 0;
@endphp

<x-report.page
    eyebrow="Financial statements"
    title="Cash flow"
    subtitle="Cash received and paid through bank and cash accounts from posted journal entries."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.cashflow')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.export-actions module="cash-flow" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi label="Opening cash" :value="$currencyCode . ' ' . number_format($openingBalance, 2)" hint="Bank & cash balance before period start" />
        <x-dashboard.kpi label="Cash in" tone="success" :value="$currencyCode . ' ' . number_format($cashIn, 2)" hint="Debits to bank/cash accounts" />
        <x-dashboard.kpi label="Cash out" tone="warning" :value="$currencyCode . ' ' . number_format($cashOut, 2)" hint="Credits from bank/cash accounts" />
        <x-dashboard.kpi label="Closing cash" :tone="$netTone" :value="$currencyCode . ' ' . number_format($closingBalance, 2)" hint="Opening + net movement" />
    </x-slot:kpis>

    <div class="erp-dash-layout-split">
        <x-dashboard.panel title="Net movement" subtitle="Cash in minus cash out for the selected period">
            <div class="flex flex-col items-center justify-center py-6 text-center">
                <p class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">Net cash change</p>
                <p class="mt-2 text-4xl font-bold tabular-nums {{ $net >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-500' }}">
                    {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 2) }}
                </p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ $net >= 0 ? 'More cash came in than went out' : 'More cash went out than came in' }}
                </p>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-dashboard.progress label="Inflow share" tone="success" :percent="$cashIn + $cashOut > 0 ? ($cashIn / ($cashIn + $cashOut)) * 100 : 0" hint="Receipts vs total movement" />
                <x-dashboard.progress label="Coverage ratio" :tone="$ratioTone" :percent="min($ratio * 50, 100)" :hint="'Inflows : outflows = ' . number_format($ratio, 2)" />
            </div>
        </x-dashboard.panel>

        <x-dashboard.panel title="Daily averages" subtitle="Movement spread across {{ $days }} day(s)">
            <div class="space-y-4">
                <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Average daily inflow</span>
                    <span class="text-sm font-semibold tabular-nums text-success-600 dark:text-success-400">+{{ number_format($avgIn, 2) }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Average daily outflow</span>
                    <span class="text-sm font-semibold tabular-nums text-orange-600 dark:text-orange-400">-{{ number_format($avgOut, 2) }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/40">
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Average net per day</span>
                    <span class="text-sm font-bold tabular-nums {{ $avgNet >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-error-600 dark:text-error-500' }}">
                        {{ $avgNet >= 0 ? '+' : '' }}{{ number_format($avgNet, 2) }}
                    </span>
                </div>
            </div>
        </x-dashboard.panel>
    </div>

    @if($weekly->isNotEmpty())
        <x-dashboard.panel title="Weekly movement" subtitle="Cash in and out by week">
            <x-dashboard.bar-chart
                :labels="$weekly->pluck('label')->all()"
                :values="$weekly->pluck('cash_in')->all()"
                :secondary="$weekly->pluck('cash_out')->all()"
                primary-label="In"
                secondary-label="Out"
                :currency="$currencyCode"
            />
        </x-dashboard.panel>
    @endif

    @if($byAccount->isNotEmpty())
        <x-dashboard.panel title="By account" subtitle="Which bank or cash account moved">
            <div class="overflow-x-auto">
                <table class="erp-dash-statement__table">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Account</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">In</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Out</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byAccount as $row)
                            <tr class="border-b border-gray-50 dark:border-gray-800/60">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $row['account']->code }} — {{ $row['account']->name }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums text-success-600 dark:text-success-400">+{{ number_format($row['cash_in'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums text-orange-600 dark:text-orange-400">-{{ number_format($row['cash_out'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums {{ $row['net'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-error-600 dark:text-error-500' }}">
                                    {{ $row['net'] >= 0 ? '+' : '' }}{{ number_format($row['net'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-dashboard.panel>
    @endif
</x-report.page>
@endsection
