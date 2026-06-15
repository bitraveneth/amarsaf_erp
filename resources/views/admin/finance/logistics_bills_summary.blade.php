@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Logistics reports" title="Logistics bills summary" subtitle="Carrier transport invoices and payment status for the period." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions :range-action="route('admin.reports.logistics-bills')" :range="$range" :from="request('from', $from->toDateString())" :to="request('to', $to->toDateString())" :range-options="$rangeOptions" :period-label="$periodLabel">
            <x-report.hub-link category="logistics" />
            <x-report.export-actions module="logistics-bills" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Billed" :value="$currencyCode . ' ' . number_format($totals['gross'], 2)" :hint="$totals['bill_count'] . ' bills'" />
        <x-dashboard.kpi label="Unpaid" tone="warning" :value="$currencyCode . ' ' . number_format($totals['unpaid'], 2)" hint="Outstanding carrier balances" />
        <x-dashboard.kpi label="Paid" tone="success" :value="$currencyCode . ' ' . number_format($totals['paid'], 2)" hint="Settled in period" />
    </x-slot:kpis>
    <x-dashboard.panel title="By carrier" :subtitle="'From ' . $from->toDateString() . ' to ' . $to->toDateString()">
        <x-report.table>
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <x-report.th>Carrier</x-report.th>
                    <x-report.th align="right">Bills</x-report.th>
                    <x-report.th align="right">Gross</x-report.th>
                    <x-report.th align="right">Unpaid</x-report.th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-gray-50 dark:border-gray-800/60">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['carrier'] }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ $row['bill_count'] }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($row['gross_total'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['unpaid_total'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8"><x-admin.empty-state title="No logistics bills in this period" /></td></tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-gray-50 font-semibold dark:bg-gray-800/50">
                        <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">Totals</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ $totals['bill_count'] }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($totals['gross'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($totals['unpaid'], 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </x-report.table>
    </x-dashboard.panel>
</x-report.page>
@endsection
