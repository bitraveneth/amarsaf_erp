@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Inventory reports" title="Delivery performance" subtitle="Dispatch activity and delivered status in the period." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions :range-action="route('admin.reports.delivery-performance')" :range="$range" :from="request('from', $from->toDateString())" :to="request('to', $to->toDateString())" :range-options="$rangeOptions" :period-label="$periodLabel">
            <x-report.hub-link category="operations" />
            <x-report.export-actions module="delivery-performance" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Deliveries" :value="(string) $totals['count']" hint="Created in period" />
        <x-dashboard.kpi label="Delivered rate" :value="$totals['pod_rate'] . '%'" :hint="$totals['pod_complete'] . ' marked delivered'" />
    </x-slot:kpis>
    <x-dashboard.panel title="Recent deliveries">
        <table class="erp-dash-statement__table w-full text-sm">
            <thead><tr><th>Date</th><th>Order</th><th>Agent</th><th>Route</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($rows as $delivery)
                    <tr>
                        <td>{{ $delivery->created_at?->format('d M Y') }}</td>
                        <td>{{ $delivery->order?->number ?? $delivery->order_id }}</td>
                        <td>{{ $delivery->order?->agent?->name ?? '—' }}</td>
                        <td>{{ $delivery->route?->name ?? '—' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $delivery->status)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-dashboard.panel>
</x-report.page>
@endsection
