@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
    $totalGross = $rows->sum(fn ($row) => (float) str_replace(',', '', $row[7] ?? 0));
    $totalVat = $rows->sum(fn ($row) => (float) str_replace(',', '', $row[5] ?? 0));
@endphp

<x-report.page
    eyebrow="Sales reports"
    title="Sales register"
    subtitle="Customer invoices issued in the selected period."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.sales-register')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="sales" />
            <a href="{{ route('admin.finance.index') }}" class="erp-btn-secondary">Customer invoices</a>
            <x-report.export-actions module="sales-register" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Invoices"
            :value="(string) $rows->count()"
            hint="Issued in period"
        />
        <x-dashboard.kpi
            label="Net total"
            :value="$ccy . ' ' . number_format($totalNet, 0)"
            hint="Before VAT"
        />
        <x-dashboard.kpi
            label="VAT"
            :value="$ccy . ' ' . number_format($totalVat, 0)"
            hint="Output VAT on these invoices"
        />
        <x-dashboard.kpi
            label="Gross"
            tone="brand"
            :value="$ccy . ' ' . number_format($totalGross, 0)"
            hint="Net + VAT − withholding"
        />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">Invoice register</h2>
                <p class="erp-caption">{{ $periodLabel }}</p>
            </div>
            <p class="erp-caption">{{ number_format($rows->count()) }} invoices</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice</th>
                        <th>Order</th>
                        <th>Agent</th>
                        <th class="is-right">Net</th>
                        <th class="is-right">VAT</th>
                        <th class="is-right">Gross</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $status = strtolower((string) ($row[8] ?? ''));
                            $badge = match ($status) {
                                'paid', 'settled' => 'erp-badge-success',
                                'issued', 'adjusted' => 'erp-badge-brand',
                                'cancelled', 'void' => 'erp-badge-warning',
                                default => 'erp-badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td class="erp-table-cell whitespace-nowrap">{{ $row[0] }}</td>
                            <td class="erp-table-cell font-medium">{{ $row[1] }}</td>
                            <td class="erp-table-cell">{{ $row[2] }}</td>
                            <td class="erp-body-strong">{{ $row[3] }}</td>
                            <td class="is-right"><span class="erp-table-num">{{ $row[4] }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ $row[5] }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ $row[7] }}</span></td>
                            <td><span class="{{ $badge }}">{{ $row[8] }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-admin.empty-state
                                    title="No invoices in this period"
                                    description="Widen the date range or issue invoices from delivered orders."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-report.page>
@endsection
