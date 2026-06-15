@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Logistics reports" title="Fleet expense summary" subtitle="Fuel, maintenance, rent, and other vehicle costs." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions :range-action="route('admin.reports.fleet-expenses')" :range="$range" :from="request('from', $from->toDateString())" :to="request('to', $to->toDateString())" :range-options="$rangeOptions" :period-label="$periodLabel">
            <x-report.hub-link category="logistics" />
            <x-report.export-actions module="fleet-expenses" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Total fleet spend" :value="$currencyCode . ' ' . number_format($totals['total'], 2)" :hint="$totals['count'] . ' lines'" />
    </x-slot:kpis>
    <x-dashboard.panel title="By expense type" :subtitle="'From ' . $from->toDateString() . ' to ' . $to->toDateString()">
        <x-report.table>
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <x-report.th>Type</x-report.th>
                    <x-report.th align="right">Count</x-report.th>
                    <x-report.th align="right">Total</x-report.th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-gray-50 dark:border-gray-800/60">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['label'] }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ $row['count'] }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format($row['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8"><x-admin.empty-state title="No fleet expenses in this period" /></td></tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-gray-50 font-semibold dark:bg-gray-800/50">
                        <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">Total</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ $totals['count'] }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($totals['total'], 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </x-report.table>
    </x-dashboard.panel>
</x-report.page>
@endsection
