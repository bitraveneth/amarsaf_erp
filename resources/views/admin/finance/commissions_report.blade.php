@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
    $totalSales = $rows->sum('sales');
    $totalCommission = $rows->sum('commission');
    $avgRate = $totalSales > 0 ? round(($totalCommission / $totalSales) * 100, 2) : 0;
@endphp

<x-report.page
    eyebrow="Sales reports"
    title="Commission summary"
    subtitle="Agent sales and commission for the selected period."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.commissions')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="sales" />
            <x-report.export-actions module="commission-summary" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Total sales"
            :value="$ccy . ' ' . number_format($totalSales, 0)"
            :hint="$rows->count() . ' ' . Str::plural('agent', $rows->count())"
        />
        <x-dashboard.kpi
            label="Total commission"
            tone="brand"
            :value="$ccy . ' ' . number_format($totalCommission, 0)"
            :hint="'For ' . $periodLabel"
        />
        <x-dashboard.kpi
            label="Avg. rate"
            :value="$avgRate . '%'"
            hint="Commission ÷ sales"
        />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">By agent</h2>
                <p class="erp-caption">{{ $periodLabel }}</p>
            </div>
            <p class="erp-caption">{{ number_format($rows->count()) }} agents</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th class="is-right">Sales</th>
                        <th class="is-right">Commission</th>
                        <th class="is-right">Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="erp-body-strong">{{ $row['agent']->name }}</td>
                            <td class="is-right"><span class="erp-table-num">{{ $ccy }} {{ number_format($row['sales'], 2) }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ $ccy }} {{ number_format($row['commission'], 2) }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($row['rate'], 2) }}%</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-admin.empty-state
                                    title="No commission activity"
                                    description="Try another period, or confirm delivered orders with invoices in range."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td class="erp-body-strong">Totals</td>
                            <td class="is-right"><span class="erp-table-num">{{ $ccy }} {{ number_format($totalSales, 2) }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ $ccy }} {{ number_format($totalCommission, 2) }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($avgRate, 2) }}%</span></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-report.page>
@endsection
