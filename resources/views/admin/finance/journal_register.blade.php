@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Accounting reports" title="Journal register" subtitle="Posted journal vouchers in the period." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions :range-action="route('admin.reports.journal-register')" :range="$range" :from="request('from', $from->toDateString())" :to="request('to', $to->toDateString())" :range-options="$rangeOptions" :period-label="$periodLabel">
            <x-report.hub-link category="accountant" />
            <x-report.export-actions module="journal-register" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Posted journals" :value="(string) $entries->count()" hint="In selected period" />
    </x-slot:kpis>
    <x-dashboard.panel title="Journals" :subtitle="'From ' . $from->toDateString() . ' to ' . $to->toDateString()">
        <x-report.table>
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <x-report.th>Date</x-report.th>
                    <x-report.th>Journal</x-report.th>
                    <x-report.th>Description</x-report.th>
                    <x-report.th align="right">Debit</x-report.th>
                    <x-report.th align="right">Credit</x-report.th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr class="border-b border-gray-50 dark:border-gray-800/60">
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $entry->entry_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm">
                            <a href="{{ route('admin.journals.show', $entry) }}" class="font-mono text-brand-600 hover:underline dark:text-brand-400">{{ $entry->number ?? $entry->id }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $entry->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($entry->lines->sum('debit'), 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($entry->lines->sum('credit'), 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8"><x-admin.empty-state title="No posted journals in this period" /></td></tr>
                @endforelse
            </tbody>
        </x-report.table>
    </x-dashboard.panel>
</x-report.page>
@endsection
