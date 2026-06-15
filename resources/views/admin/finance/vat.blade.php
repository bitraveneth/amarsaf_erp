@extends('layouts.app')

@section('content')
@php
    $ccy = config('app.currency', 'BDT');
    $vatTone = $vatCollected >= 0 ? 'brand' : 'warning';
@endphp

<x-report.page
    eyebrow="Financial statements"
    title="VAT report"
    subtitle="Output and input VAT summary for filing and reconciliation."
    :period="$month->format('F Y') . ' · ' . $from->toDateString() . ' to ' . $to->toDateString()"
>
    <x-slot:actions>
        <x-report.header-actions>
            <x-report.export-actions module="vat-report" :from="$from" :to="$to" />
            <a href="{{ route('admin.reports.vat.export', ['month' => $month->format('Y-m')]) }}" class="erp-btn-secondary text-sm">Line CSV</a>
            <a href="{{ route('admin.export-center', ['module' => 'tally-xml', 'from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="erp-btn-secondary">Tally XML</a>
            @if(Route::has('admin.vat.summary'))
                <div class="flex items-center gap-1 rounded-lg border border-gray-300 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-800">
                    <a href="{{ route('admin.vat.summary', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}"
                       class="inline-flex h-9 w-9 items-center justify-center rounded-l-lg text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <span class="px-3 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $month->format('M Y') }}</span>
                    <a href="{{ route('admin.vat.summary', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}"
                       class="inline-flex h-9 w-9 items-center justify-center rounded-r-lg text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                <a href="{{ route('admin.vat.summary') }}" class="erp-btn-secondary">Current period</a>
            @endif
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi label="Net VAT payable" :tone="$vatTone" :value="$ccy . ' ' . number_format($vatCollected, 2)" hint="Output VAT less input VAT for {{ $month->format('F Y') }}" />
        <x-dashboard.kpi label="Output VAT" tone="success" :value="$ccy . ' ' . number_format($outputVat, 2)" hint="From issued sales invoices" />
        <x-dashboard.kpi label="Input VAT" tone="warning" :value="$ccy . ' ' . number_format($inputVat, 2)" hint="From recorded purchase bills" />
        <x-dashboard.kpi label="Taxable sales" :value="$ccy . ' ' . number_format($totals['taxable'] ?? 0, 2)" hint="Invoice taxable base in period" />
    </x-slot:kpis>

    <x-dashboard.panel title="Detailed VAT by invoice" :subtitle="'From ' . $from->toDateString() . ' to ' . $to->toDateString()">
        @if($invoiceRows->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No invoices found in this period.</p>
        @else
            <div class="overflow-x-auto">
                <table class="erp-dash-statement__table">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Customer</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Taxable amount</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">VAT</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">VAT rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoiceRows as $row)
                            <tr class="border-b border-gray-50 dark:border-gray-800/60">
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ \Illuminate\Support\Carbon::parse($row['date'])->toDateString() }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-300">{{ $row['number'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $row['customer'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $ccy }} {{ number_format($row['taxable'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $ccy }} {{ number_format($row['vat'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $row['vat_rate'] !== null ? $row['vat_rate'].'%' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-semibold dark:bg-gray-800/50">
                            <td colspan="3" class="px-4 py-3 text-right text-sm">Totals</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $ccy }} {{ number_format($totals['taxable'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $ccy }} {{ number_format($totals['vat'], 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </x-dashboard.panel>

    <x-dashboard.panel title="Input VAT by purchase bill" subtitle="Input VAT recorded on supplier bills in this period.">
        @if($purchaseRows->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No purchase bills found in this period.</p>
        @else
            <div class="overflow-x-auto">
                <table class="erp-dash-statement__table">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Bill</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Supplier</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Taxable amount</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">VAT</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">VAT rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseRows as $row)
                            <tr class="border-b border-gray-50 dark:border-gray-800/60">
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ \Illuminate\Support\Carbon::parse($row['date'])->toDateString() }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-300">{{ $row['number'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $row['supplier'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $ccy }} {{ number_format($row['taxable'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $ccy }} {{ number_format($row['vat'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $row['vat_rate'] !== null ? $row['vat_rate'].'%' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-semibold dark:bg-gray-800/50">
                            <td colspan="3" class="px-4 py-3 text-right text-sm">Totals</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $ccy }} {{ number_format($purchaseTotals['taxable'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $ccy }} {{ number_format($purchaseTotals['vat'], 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </x-dashboard.panel>
</x-report.page>
@endsection
