@extends('layouts.app')

@section('content')
@php
    $avgProgress = $rows->isNotEmpty() ? round($rows->avg('progress'), 1) : 0;
@endphp

<x-report.page eyebrow="Sales reports" title="Sales target vs actual" subtitle="Target achievement by agent or employee." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions :range-action="route('admin.reports.sales-targets')" :range="$range" :from="request('from', $from->toDateString())" :to="request('to', $to->toDateString())" :range-options="$rangeOptions" :period-label="$periodLabel">
            <x-report.hub-link category="sales" />
            <x-report.export-actions module="sales-targets" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Targets in view" :value="(string) $rows->count()" hint="Overlapping this period" />
        <x-dashboard.kpi label="Avg. achievement" :value="$avgProgress . '%'" :hint="$rows->count() ? 'Across listed targets' : 'No targets'" />
    </x-slot:kpis>
    <x-dashboard.panel title="Targets" :subtitle="'From ' . $from->toDateString() . ' to ' . $to->toDateString()">
        <x-report.table>
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <x-report.th>Name</x-report.th>
                    <x-report.th align="right">Target</x-report.th>
                    <x-report.th align="right">Achieved</x-report.th>
                    <x-report.th align="right">Gap</x-report.th>
                    <x-report.th align="right">%</x-report.th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-gray-50 dark:border-gray-800/60">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['target_amount'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($row['achieved'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['gap'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ $row['progress'] }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8"><x-admin.empty-state title="No targets overlap this period" /></td></tr>
                @endforelse
            </tbody>
        </x-report.table>
    </x-dashboard.panel>
</x-report.page>
@endsection
