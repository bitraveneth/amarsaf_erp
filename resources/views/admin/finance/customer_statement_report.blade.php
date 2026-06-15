@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Accounting reports" title="Customer statement" subtitle="Invoice and receipt activity for one agent." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions :range-action="route('admin.reports.customer-statement')" :range="$range" :from="request('from', $from->toDateString())" :to="request('to', $to->toDateString())" :range-options="$rangeOptions" :period-label="$periodLabel">
            <x-report.hub-link category="accountant" />
            <x-report.export-actions module="customer-statement" :from="$from" :to="$to" :export-query="array_filter(['agent_id' => $agentId])" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:filters>
        <form method="GET" class="flex flex-wrap items-end gap-3 print:hidden">
            @foreach(request()->only(['range', 'from', 'to']) as $key => $value)
                @if($value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
            @endforeach
            <div class="erp-field min-w-[16rem]">
                <label class="erp-label" for="cs-agent">Agent</label>
                <select id="cs-agent" name="agent_id" class="erp-select" onchange="this.form.submit()">
                    <option value="">Select agent</option>
                    @foreach($agents as $a)
                        <option value="{{ $a->id }}" @selected($agentId === $a->id)>{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </x-slot:filters>

    <x-slot:kpis>
        @if($agent)
            <x-dashboard.kpi label="Opening" :value="$currencyCode . ' ' . number_format($opening, 2)" hint="Before period" />
            <x-dashboard.kpi label="Closing" :value="$currencyCode . ' ' . number_format($closing, 2)" hint="End of period" />
        @endif
    </x-slot:kpis>

    @if($agent)
        <x-dashboard.panel :title="$agent->name" :subtitle="'From ' . $from->toDateString() . ' to ' . $to->toDateString()">
            <x-report.table>
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-report.th>Date</x-report.th>
                        <x-report.th>Type</x-report.th>
                        <x-report.th>Reference</x-report.th>
                        <x-report.th align="right">Debit</x-report.th>
                        <x-report.th align="right">Credit</x-report.th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="border-b border-gray-50 dark:border-gray-800/60">
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $row['date'] instanceof \Illuminate\Support\Carbon ? $row['date']->format('d M Y') : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['type'] }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-300">{{ $row['reference'] }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8"><x-admin.empty-state title="No activity in period" /></td></tr>
                    @endforelse
                </tbody>
            </x-report.table>
        </x-dashboard.panel>
    @else
        <x-admin.empty-state title="Select an agent" description="Choose a customer agent to view their statement." />
    @endif
</x-report.page>
@endsection
