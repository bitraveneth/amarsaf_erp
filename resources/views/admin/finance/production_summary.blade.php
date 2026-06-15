@extends('layouts.app')

@section('content')
@php
    $margin = $salesTotal > 0 ? round(($approxProfit / $salesTotal) * 100, 1) : 0;
@endphp

<x-report.page
    eyebrow="Inventory reports"
    title="Production reports"
    subtitle="Factory output, estimated material cost, and how production compares to sales in the period."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.production')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="operations" />
            <x-report.export-actions module="production-summary" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi label="Production runs" :value="number_format($totalRuns, 0)" hint="Completed runs in period" />
        <x-dashboard.kpi label="Units produced" :value="number_format($totalQuantity, 0)" hint="Total finished quantity" />
        <x-dashboard.kpi label="Est. material cost" tone="warning" :value="$currencyCode . ' ' . number_format($materialCostTotal, 0)" hint="From BOM and run costing" />
        <x-dashboard.kpi label="Net sales (period)" :value="$currencyCode . ' ' . number_format($salesTotal, 0)" hint="For context vs output" />
    </x-slot:kpis>

    <div class="erp-dash-layout-split">
        <x-dashboard.panel title="Period economics" subtitle="Rough view of sales vs production cost and operating spend">
            <div class="space-y-4">
                <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Net sales</span>
                    <span class="text-sm font-semibold tabular-nums">{{ number_format($salesTotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Est. material cost</span>
                    <span class="text-sm font-semibold tabular-nums text-orange-600 dark:text-orange-400">-{{ number_format($materialCostTotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Operating expenses</span>
                    <span class="text-sm font-semibold tabular-nums text-orange-600 dark:text-orange-400">-{{ number_format($expensesTotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/40">
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Approx. result</span>
                    <span class="text-sm font-bold tabular-nums {{ $approxProfit >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-500' }}">
                        {{ $approxProfit >= 0 ? '+' : '' }}{{ number_format($approxProfit, 2) }}
                    </span>
                </div>
            </div>
        </x-dashboard.panel>

        <x-dashboard.panel title="Approximate margin" subtitle="Sales less estimated material and operating spend">
            <div class="flex flex-col items-center justify-center py-6 text-center">
                <p class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">Margin on net sales</p>
                <p class="mt-2 text-4xl font-bold tabular-nums {{ $approxProfit >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-500' }}">
                    {{ $margin }}%
                </p>
                <p class="mt-2 max-w-sm text-sm text-gray-500 dark:text-gray-400">
                    {{ $approxProfit >= 0 ? 'Positive rough margin for the period' : 'Costs exceed sales in this rough view' }}
                </p>
            </div>
            <div class="mt-2 space-y-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                <x-dashboard.progress
                    label="Material cost share"
                    tone="warning"
                    :percent="$salesTotal > 0 ? min(($materialCostTotal / $salesTotal) * 100, 100) : 0"
                    hint="Estimated production cost vs sales"
                />
                <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">
                    This is an operational estimate only. Use the
                    <a href="{{ route('admin.reports.pl', request()->only(['range', 'from', 'to'])) }}" class="font-medium text-brand-600 underline dark:text-brand-400">income statement</a>
                    for formal profit.
                </p>
            </div>
        </x-dashboard.panel>
    </div>

    <x-dashboard.panel title="Production by product" subtitle="Runs, quantity, and estimated material cost per finished good">
        @if($byProduct->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="erp-dash-statement__table">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Runs</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Quantity</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Est. unit cost</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Est. total cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byProduct as $row)
                            <tr class="border-b border-gray-50 dark:border-gray-800/60">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $row['product']?->name ?? 'Unknown product' }}
                                    @if($row['product']?->sku)
                                        <span class="ml-2 text-xs text-gray-500">{{ $row['product']->sku }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $row['runs'] }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums font-semibold">{{ number_format($row['quantity']) }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums">
                                    {{ ! is_null($row['unit_cost']) ? number_format($row['unit_cost'], 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums">
                                    {{ ! is_null($row['total_cost']) ? number_format($row['total_cost'], 2) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50/80 dark:bg-gray-800/40">
                            <td class="px-4 py-3 text-sm font-semibold">Totals</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums">{{ $totalRuns }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums">{{ number_format($totalQuantity) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-500">—</td>
                            <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-brand-600 dark:text-brand-400">{{ number_format($materialCostTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <x-admin.empty-state
                title="No production runs"
                description="No production runs were recorded for the selected period."
            />
        @endif
    </x-dashboard.panel>
</x-report.page>
@endsection
