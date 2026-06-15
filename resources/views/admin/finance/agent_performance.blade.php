@extends('layouts.app')

@section('content')
@php
    $topAgent = $rows->first();
    $profitTone = ($summary['net_sales'] ?? 0) > 0 && ($summary['collection_rate'] ?? 0) >= 80 ? 'success' : 'warning';
@endphp

<x-report.page
    eyebrow="Sales reports"
    title="Agent performance"
    subtitle="Net sales, collections, and outstanding balance for each selling partner in the period."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.agents')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="sales" />
            <x-report.export-actions module="agent-performance" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    @if($rows->isNotEmpty())
        <x-slot:kpis>
            <x-dashboard.kpi label="Net sales" :value="$currencyCode . ' ' . number_format($summary['net_sales'], 0)" :hint="$summary['agent_count'] . ' agents with activity'" />
            <x-dashboard.kpi label="Collections" tone="success" :value="$currencyCode . ' ' . number_format($summary['receipts'], 0)" :hint="number_format($summary['collection_rate'], 1) . '% collection rate'" />
            <x-dashboard.kpi label="Outstanding" tone="warning" :value="$currencyCode . ' ' . number_format($summary['outstanding'], 0)" hint="Still owed by customers" />
            <x-dashboard.kpi label="Top agent" :tone="$profitTone" :value="$topAgent['agent']->name ?? '—'" :hint="isset($topAgent) ? $currencyCode . ' ' . number_format($topAgent['net_sales'], 0) . ' net sales' : 'No sales'" />
        </x-slot:kpis>

        <div class="erp-dash-layout-split">
            <x-dashboard.panel title="Agent ranking" subtitle="By net sales in period">
                <x-dashboard.rank-list title="Agents" :currency="$currencyCode" :items="$topAgents" />
            </x-dashboard.panel>

            <x-dashboard.panel title="Collection health" subtitle="How much invoiced sales was collected">
                <x-dashboard.progress label="Collection rate" tone="success" :percent="$summary['collection_rate']" hint="Receipts as a share of net sales" />
                <x-dashboard.progress
                    label="Receivable load"
                    tone="warning"
                    :percent="$summary['net_sales'] > 0 ? ($summary['outstanding'] / $summary['net_sales']) * 100 : 0"
                    hint="Outstanding relative to net sales"
                    class="mt-5"
                />
            </x-dashboard.panel>
        </div>

        <x-dashboard.panel title="Agent detail" subtitle="Click through to compare sales vs collections per partner">
            <div class="overflow-x-auto">
                <table class="erp-dash-statement__table">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Agent</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Area / zone</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Orders</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Invoices</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Net sales</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Receipts</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            @php
                                $agent = $row['agent'];
                                $collectionRatio = $row['net_sales'] > 0 ? ($row['receipts'] / $row['net_sales']) * 100 : 0;
                            @endphp
                            <tr class="border-b border-gray-50 dark:border-gray-800/60">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $agent->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $agent->area ?? '—' }} / {{ $agent->zone ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $row['order_count'] }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums">{{ $row['invoice_count'] }}</td>
                                <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums">{{ number_format($row['net_sales'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums text-success-600 dark:text-success-400">
                                    {{ number_format($row['receipts'], 2) }}
                                    <span class="block text-xs text-gray-500">{{ number_format($collectionRatio, 1) }}%</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums {{ $row['outstanding'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-success-600 dark:text-success-400' }}">
                                    {{ number_format($row['outstanding'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50/80 dark:bg-gray-800/40">
                            <td colspan="2" class="px-4 py-3 text-sm font-semibold">Totals</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums">{{ $rows->sum('order_count') }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums">{{ $rows->sum('invoice_count') }}</td>
                            <td class="px-4 py-3 text-right text-sm font-bold tabular-nums">{{ number_format($summary['net_sales'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-success-600 dark:text-success-400">{{ number_format($summary['receipts'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-orange-600 dark:text-orange-400">{{ number_format($summary['outstanding'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-dashboard.panel>
    @else
        <x-dashboard.panel title="No agent activity">
            <x-admin.empty-state
                title="No agent data for this period"
                description="No invoiced sales linked to agents were found. Try a wider date range."
            />
        </x-dashboard.panel>
    @endif
</x-report.page>
@endsection
