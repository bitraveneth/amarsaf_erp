@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Cost reports" title="Utilities & electricity" subtitle="Electricity and utility bills recorded under the utilities expense category." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions :range-action="route('admin.reports.utilities')" :range="$range" :from="request('from', $from->toDateString())" :to="request('to', $to->toDateString())" :range-options="$rangeOptions" :period-label="$periodLabel">
            <x-report.hub-link category="costs" />
            <x-report.export-actions module="utilities-report" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Utilities spend" :value="$currencyCode . ' ' . number_format($total, 2)" :hint="$lines->count() . ' bills/lines'" />
    </x-slot:kpis>
    <x-dashboard.panel title="Utility expense lines" :subtitle="'From ' . $from->toDateString() . ' to ' . $to->toDateString()">
        <x-report.table>
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <x-report.th>Date</x-report.th>
                    <x-report.th>Description</x-report.th>
                    <x-report.th>Reference</x-report.th>
                    <x-report.th align="right">Amount</x-report.th>
                    <x-report.th>Status</x-report.th>
                </tr>
            </thead>
            <tbody>
                @forelse($lines as $line)
                    <tr class="border-b border-gray-50 dark:border-gray-800/60">
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $line->date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $line->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-300">{{ $line->reference ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($line->amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ ucfirst($line->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8"><x-admin.empty-state title="No utility expenses in this period" /></td></tr>
                @endforelse
            </tbody>
            @if($lines->isNotEmpty())
                <tfoot>
                    <tr class="bg-gray-50 font-semibold dark:bg-gray-800/50">
                        <td colspan="3" class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">Total</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($total, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </x-report.table>
    </x-dashboard.panel>
</x-report.page>
@endsection
