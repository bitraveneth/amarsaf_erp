@extends('layouts.app')

@section('content')
<x-report.page
    page-class="tb-page"
    eyebrow="Accounting reports"
    title="Trial balance"
    subtitle="Every account with opening balance, period movement, and closing balance."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.trial-balance')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="accountant" />
            <x-report.export-actions module="trial-balance" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    @if(!$isBalanced)
        <x-slot:alerts>
            <div class="rounded-xl border border-amber-200 bg-amber-50/90 px-4 py-3 text-sm text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100 print:hidden">
                <span class="font-semibold">Books are out of balance</span>
                <span class="mt-0.5 block opacity-90">Period or closing debits do not equal credits — review journals before month-end.</span>
            </div>
        </x-slot:alerts>
    @endif

    @isset($totals)
        <x-slot:kpis>
            <x-dashboard.kpi label="Period debits" :value="number_format($totals['period_debit'], 2)" hint="Movement in range" />
            <x-dashboard.kpi label="Period credits" :value="number_format($totals['period_credit'], 2)" hint="Movement in range" />
            <x-dashboard.kpi
                label="Books status"
                :tone="$isBalanced ? 'success' : 'danger'"
                :value="$isBalanced ? 'Balanced' : 'Out of balance'"
                hint="Debits must equal credits"
            />
            <x-dashboard.kpi label="Closing debits" :value="number_format($totals['closing_debit'], 2)" :hint="'Closing credits: ' . number_format($totals['closing_credit'], 2)" />
        </x-slot:kpis>
    @endisset

    @include('admin.finance.partials.trial-balance-statement', [
        'tree' => $tree,
        'from' => $from,
        'to' => $to,
        'totals' => $totals ?? null,
        'periodLabel' => $periodLabel,
        'currencyCode' => $currencyCode,
    ])
</x-report.page>
@endsection
