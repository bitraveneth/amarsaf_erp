@extends('layouts.app')

@section('content')
@php
    $varianceTone = abs($totals['variance']) < 0.01 ? 'success' : 'warning';
@endphp

<x-report.page
    eyebrow="Inventory reports"
    title="Production variance"
    subtitle="Compare standard BOM unit cost against actual material cost for confirmed production runs."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.production-variance')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="operations" />
            <x-report.export-actions module="production-variance" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi label="Standard cost" :value="number_format($totals['standard'], 2)" hint="Expected material cost from BOM standards" />
        <x-dashboard.kpi label="Actual cost" :value="number_format($totals['actual'], 2)" hint="Recorded material cost on confirmed runs" />
        <x-dashboard.kpi label="Variance" :tone="$varianceTone" :value="number_format($totals['variance'], 2)" hint="Actual minus standard for the selected period" />
    </x-slot:kpis>

    <x-dashboard.panel title="Run-level variance" subtitle="Sorted by absolute variance impact.">
        <div class="overflow-x-auto">
            <table class="erp-dash-statement__table">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Run</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Product</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Std unit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actual unit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Variance total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="border-b border-gray-50 dark:border-gray-800/60">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['run']->order_number ?? '#'.$row['run']->id }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['product']?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums">{{ number_format($row['standard_unit_cost'], 4) }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums">{{ number_format($row['actual_unit_cost'], 4) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums {{ $row['variance_total'] >= 0 ? 'text-orange-600 dark:text-orange-400' : 'text-success-600 dark:text-success-400' }}">
                                {{ number_format($row['variance_total'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8">
                                <x-admin.empty-state title="No confirmed production runs" description="Confirm production stock in the selected date range to see variance analysis." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-dashboard.panel>
</x-report.page>
@endsection
