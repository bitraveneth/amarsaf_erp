@extends('layouts.app')

@section('content')
@php
    $is = $incomeStatement ?? [];
    $summary = $is['summary'] ?? [];
    $trust = $is['trust'] ?? [];
    $queryParams = request()->only(['range', 'from', 'to', 'compare']);
    $ccy = $is['currencyCode'] ?? $currencyCode;
    $netRevenue = (float) ($netRevenue ?? $netSales ?? 0);
    $grossProfitVal = (float) ($grossProfit ?? 0);
    $operatingProfitVal = (float) ($operatingProfit ?? 0);
    $netProfitVal = (float) ($netProfit ?? 0);
    $grossMargin = $netRevenue > 0 ? round($grossProfitVal / $netRevenue * 100, 1) : 0;
    $operatingMargin = $netRevenue > 0 ? round($operatingProfitVal / $netRevenue * 100, 1) : 0;
    $netMargin = $summary['net_margin'] ?? ($netRevenue > 0 ? round($netProfitVal / $netRevenue * 100, 1) : 0);
    $profitTone = $netProfitVal >= 0 ? 'success' : 'danger';
    $grossTone = $grossProfitVal >= 0 ? 'success' : 'danger';
    $operatingTone = $operatingProfitVal >= 0 ? 'brand' : 'danger';
@endphp

<x-report.page
    page-class="is-page"
    eyebrow="Finance reports"
    title="Income statement"
    subtitle="Summary KPIs and line-by-line detail from posted ledger accounts."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.pl')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.export-actions module="profit-loss" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    @if(!empty($unposted))
        <x-slot:alerts>
            <div class="rounded-2xl border border-amber-200 bg-amber-50/90 px-5 py-4 text-sm text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100 print:hidden">
                <p class="font-semibold">Some data is not in the ledger yet</p>
                <p class="mt-1 text-sm opacity-90">New invoices, receipts, GRNs, and expenses usually post automatically. These items still need attention before the statement is complete:</p>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach($unposted as $item)
                        <li class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <a href="{{ $item['href'] }}" class="font-medium underline">{{ $item['label'] }} ({{ $item['count'] }})</a>
                                @if(!empty($item['hint']))
                                    <span class="mt-0.5 block text-xs opacity-80">{{ $item['hint'] }}</span>
                                @endif
                            </div>
                            @if(!empty($item['can_sync']))
                                <form method="POST" action="{{ route('admin.expenses.sync-ledger') }}">
                                    @csrf
                                    <input type="hidden" name="from" value="{{ $from->toDateString() }}">
                                    <input type="hidden" name="to" value="{{ $to->toDateString() }}">
                                    <input type="hidden" name="range" value="{{ $range }}">
                                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                                    <button type="submit" class="text-xs font-semibold underline">Sync missing expenses</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </x-slot:alerts>
    @endif

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Net revenue"
            tone="brand"
            :value="$ccy . ' ' . number_format($netRevenue, 0)"
            hint="Gross sales less returns plus other income"
        />
        <x-dashboard.kpi
            label="Gross profit"
            :tone="$grossTone"
            :value="$ccy . ' ' . number_format($grossProfitVal, 0)"
            :hint="'Revenue less COGS · ' . $grossMargin . '% margin'"
        />
        <x-dashboard.kpi
            label="Operating profit"
            :tone="$operatingTone"
            :value="$ccy . ' ' . number_format($operatingProfitVal, 0)"
            :hint="'After admin & selling costs · ' . $operatingMargin . '% margin'"
        />
        <x-dashboard.kpi
            label="Net profit"
            :tone="$profitTone"
            :value="$ccy . ' ' . number_format($netProfitVal, 0)"
            :hint="'Bottom line · ' . $netMargin . '% net margin'"
        />
    </x-slot:kpis>

    @include('admin.finance.partials.income-statement', [
        'incomeStatement' => $incomeStatement,
        'from' => $from,
        'to' => $to,
        'range' => $range,
        'rangeOptions' => $rangeOptions,
        'periodLabel' => $periodLabel,
        'compareMode' => $compareMode ?? 'prior',
        'queryParams' => $queryParams,
        'trust' => $trust,
        'currencyCode' => $ccy,
    ])
</x-report.page>
@endsection
