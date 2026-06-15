@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Sales reports" title="Outstanding invoices" subtitle="Open customer balances as of the selected date." :period="'As of ' . $asOf->format('d M Y')">
    <x-slot:actions>
        <x-report.header-actions :as-of-action="route('admin.reports.outstanding-invoices')" :as-of="$asOf->toDateString()">
            <x-report.hub-link category="sales" />
            <x-report.export-actions module="outstanding-invoices" :as-of="$asOf" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Total outstanding" tone="warning" :value="$currencyCode . ' ' . number_format($totalOutstanding, 2)" :hint="$rows->count() . ' invoices'" />
    </x-slot:kpis>
    <x-dashboard.panel title="Open invoices" :subtitle="'As of ' . $asOf->format('d M Y')">
        <x-report.table>
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <x-report.th>Invoice</x-report.th>
                    <x-report.th>Agent</x-report.th>
                    <x-report.th>Issued</x-report.th>
                    <x-report.th>Due</x-report.th>
                    <x-report.th align="right">Days</x-report.th>
                    <x-report.th align="right">Outstanding</x-report.th>
                    <x-report.th>Status</x-report.th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-gray-50 dark:border-gray-800/60">
                        <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-300">{{ $row[0] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row[1] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $row[2] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $row[3] }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ $row[4] }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ $row[7] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $row[8] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8"><x-admin.empty-state title="No outstanding invoices" /></td></tr>
                @endforelse
            </tbody>
        </x-report.table>
    </x-dashboard.panel>
</x-report.page>
@endsection
