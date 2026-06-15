@extends('layouts.app')

@section('content')
@php
    $totalSales = $rows->sum('sales');
    $totalCommission = $rows->sum('commission');
@endphp

<x-report.page eyebrow="Sales reports" title="Commission summary" subtitle="Agent sales and commission for the selected month." :period="$month->format('F Y')">
    <x-slot:actions>
        <x-report.header-actions>
            <x-report.hub-link category="sales" />
            <form method="GET" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="erp-input !h-9 !w-auto" onchange="this.form.submit()">
            </form>
            <x-report.export-actions module="commission-summary" :export-query="['month' => $month->format('Y-m')]" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Total sales" :value="number_format($totalSales, 2)" :hint="$rows->count() . ' agents'" />
        <x-dashboard.kpi label="Total commission" tone="brand" :value="number_format($totalCommission, 2)" hint="For {{ $month->format('F Y') }}" />
    </x-slot:kpis>
    <x-dashboard.panel title="By agent" :subtitle="'Commission month: ' . $month->format('F Y')">
        <x-report.table>
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <x-report.th>Agent</x-report.th>
                    <x-report.th align="right">Sales</x-report.th>
                    <x-report.th align="right">Commission</x-report.th>
                    <x-report.th align="right">Rate</x-report.th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-gray-50 dark:border-gray-800/60">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $row['agent']->name }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['sales'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format($row['commission'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ $row['rate'] }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8"><x-admin.empty-state title="No commission activity for this month" /></td></tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-gray-50 font-semibold dark:bg-gray-800/50">
                        <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">Totals</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($totalSales, 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($totalCommission, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </x-report.table>
    </x-dashboard.panel>
</x-report.page>
@endsection
