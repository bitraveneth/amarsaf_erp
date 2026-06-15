@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
    $balanceTone = $closingBalance >= 0 ? 'brand' : 'warning';
@endphp

<x-report.page
    page-class="gl-page"
    eyebrow="Accounting reports"
    title="General ledger"
    subtitle="Posted journal lines for one account — debits, credits, and running balance."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.general-ledger')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="accountant" />
            <x-report.export-actions module="general-ledger" :from="$from" :to="$to" :export-query="array_filter(['account_id' => optional($selectedAccount)->id])" />
        </x-report.header-actions>
    </x-slot:actions>

    @if($selectedAccount)
        <x-slot:kpis>
            <x-dashboard.kpi label="Opening balance" :value="$ccy . ' ' . number_format($openingBalance, 2)" hint="Before period start" />
            <x-dashboard.kpi label="Period debits" :value="$ccy . ' ' . number_format($periodDebit, 2)" hint="Posted in range" />
            <x-dashboard.kpi label="Period credits" :value="$ccy . ' ' . number_format($periodCredit, 2)" hint="Posted in range" />
            <x-dashboard.kpi label="Closing balance" :tone="$balanceTone" :value="$ccy . ' ' . number_format($closingBalance, 2)" :hint="$lines->count() . ' line' . ($lines->count() === 1 ? '' : 's')" />
        </x-slot:kpis>
    @endif

    @include('admin.finance.partials.general-ledger-statement', [
        'selectedAccount' => $selectedAccount,
        'accountOptions' => $accountOptions ?? collect(),
        'activeAccounts' => $activeAccounts ?? collect(),
        'lines' => $lines,
        'from' => $from,
        'to' => $to,
        'periodLabel' => $periodLabel,
        'currencyCode' => $ccy,
        'openingBalance' => $openingBalance,
        'periodDebit' => $periodDebit,
        'periodCredit' => $periodCredit,
        'closingBalance' => $closingBalance,
        'glAction' => route('admin.reports.general-ledger'),
    ])
</x-report.page>
@endsection
