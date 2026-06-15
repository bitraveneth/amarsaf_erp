@extends('layouts.app')

@section('content')
@php
    $totalLogistics = $rows->sum('logistics_cost');
    $totalMargin = $rows->sum('margin_after_logistics');
    $marginTone = $totalMargin >= 0 ? 'success' : 'danger';
@endphp

<x-report.page eyebrow="Logistics reports" title="Route cost vs sales" subtitle="Fleet and carrier spend compared to approximate zone revenue." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions :range-action="route('admin.reports.route-costs')" :range="$range" :from="request('from', $from->toDateString())" :to="request('to', $to->toDateString())" :range-options="$rangeOptions" :period-label="$periodLabel">
            <x-report.hub-link category="logistics" />
            <x-report.export-actions module="route-costs" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Total logistics cost" :value="number_format($totalLogistics, 2)" :hint="$rows->count() . ' routes'" />
        <x-dashboard.kpi label="Zone sales (approx.)" :value="number_format($rows->sum('zone_revenue'), 2)" hint="Order totals by agent zone" />
        <x-dashboard.kpi label="After logistics" :tone="$marginTone" :value="number_format($totalMargin, 2)" hint="Zone sales minus logistics" />
    </x-slot:kpis>
    <x-dashboard.panel title="By delivery route" subtitle="Zone sales are approximate — order totals from agents in the same zone.">
        <x-report.table>
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <x-report.th>Route</x-report.th>
                    <x-report.th>Zone</x-report.th>
                    <x-report.th align="right">Fleet</x-report.th>
                    <x-report.th align="right">Carrier</x-report.th>
                    <x-report.th align="right">Total logistics</x-report.th>
                    <x-report.th align="right">Zone sales</x-report.th>
                    <x-report.th align="right">After logistics</x-report.th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-gray-50 dark:border-gray-800/60">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $row['route']->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $row['route']->zone ?: '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['fleet_cost'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['carrier_cost'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format($row['logistics_cost'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['zone_revenue'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums {{ $row['margin_after_logistics'] < 0 ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-400' }}">{{ number_format($row['margin_after_logistics'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8"><x-admin.empty-state title="No route data in this period" /></td></tr>
                @endforelse
            </tbody>
        </x-report.table>
    </x-dashboard.panel>
</x-report.page>
@endsection
