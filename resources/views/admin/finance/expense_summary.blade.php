@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Operating costs" title="Expense summary" subtitle="Operating spend grouped by category for the period." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions :range-action="route('admin.reports.expense-summary')" :range="$range" :from="request('from', $from->toDateString())" :to="request('to', $to->toDateString())" :range-options="$rangeOptions" :period-label="$periodLabel">
            <x-report.hub-link category="costs" />
            <x-report.export-actions module="expense-summary" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Total spend" :value="$currencyCode . ' ' . number_format($total, 2)" hint="{{ $lineCount }} expense lines" />
        <x-dashboard.kpi label="Categories" :value="(string) $rows->count()" hint="Active categories in period" />
    </x-slot:kpis>
    <x-dashboard.panel title="By category" subtitle="Click a row to open filtered expense lines">
        <x-report.table>
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <x-report.th>Category</x-report.th>
                    <x-report.th align="right">Lines</x-report.th>
                    <x-report.th align="right">Total</x-report.th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-gray-50 dark:border-gray-800/60">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                            <a href="{{ $row['drill_url'] }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">{{ $row['name'] }}</a>
                            <span class="ml-2 text-xs text-gray-400">{{ $row['code'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ $row['line_count'] }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format($row['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8"><x-admin.empty-state title="No expenses in this period" /></td></tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-gray-50 font-semibold dark:bg-gray-800/50">
                        <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white" colspan="2">Total</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($total, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </x-report.table>
    </x-dashboard.panel>
</x-report.page>
@endsection
