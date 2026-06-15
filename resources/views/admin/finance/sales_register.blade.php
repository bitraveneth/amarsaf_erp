@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Sales reports" title="Sales register" subtitle="Customer invoices issued in the period." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions :range-action="route('admin.reports.sales-register')" :range="$range" :from="request('from', $from->toDateString())" :to="request('to', $to->toDateString())" :range-options="$rangeOptions" :period-label="$periodLabel">
            <x-report.hub-link category="sales" />
            <x-report.export-actions module="sales-register" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Invoices" :value="(string) $rows->count()" hint="Issued in period" />
        <x-dashboard.kpi label="Net total" :value="$currencyCode . ' ' . number_format($totalNet, 2)" hint="Before VAT" />
    </x-slot:kpis>
    <x-dashboard.panel title="Invoice register" :subtitle="'From ' . $from->toDateString() . ' to ' . $to->toDateString()">
        <x-report.table>
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <x-report.th>Date</x-report.th>
                    <x-report.th>Invoice</x-report.th>
                    <x-report.th>Order</x-report.th>
                    <x-report.th>Agent</x-report.th>
                    <x-report.th align="right">Net</x-report.th>
                    <x-report.th align="right">VAT</x-report.th>
                    <x-report.th align="right">Gross</x-report.th>
                    <x-report.th>Status</x-report.th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-gray-50 dark:border-gray-800/60">
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $row[0] }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-300">{{ $row[1] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $row[2] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row[3] }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ $row[4] }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ $row[5] }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ $row[7] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $row[8] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8"><x-admin.empty-state title="No invoices in this period" /></td></tr>
                @endforelse
            </tbody>
        </x-report.table>
    </x-dashboard.panel>
</x-report.page>
@endsection
