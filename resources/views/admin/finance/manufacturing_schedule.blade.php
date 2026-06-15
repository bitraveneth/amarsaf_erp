@extends('layouts.app')

@section('content')
<x-report.page
    eyebrow="Inventory reports"
    title="Manufacturing schedule"
    subtitle="Manufacturing account schedule for the selected period."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.manufacturing-schedule')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="operations" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-dashboard.panel :title="$schedule['name'] ?? 'Manufacturing account'">
        <div class="overflow-x-auto">
            <table class="erp-dash-statement__table">
                <tbody>
                    @foreach($schedule['rows'] ?? [] as $row)
                        <tr class="border-b border-gray-50 dark:border-gray-800/60 {{ !empty($row['is_subtotal']) ? 'bg-gray-50/70 font-semibold dark:bg-gray-800/30' : '' }}">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" style="padding-left: {{ 1 + (($row['level'] ?? 0) * 1.25) }}rem">{{ $row['name'] }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums">{{ number_format($row['amount'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold dark:bg-gray-800/50">
                        <td class="px-4 py-3 text-sm">Total manufacturing cost</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums">{{ number_format($schedule['total'] ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-dashboard.panel>
</x-report.page>
@endsection
