@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Receivables & payables" title="Outstanding bills" subtitle="Open supplier balances as of the selected date." :period="'As of ' . $asOf->format('d M Y')">
    <x-slot:actions>
        <x-report.header-actions :as-of-action="route('admin.reports.outstanding-bills')" :as-of="$asOf->toDateString()">
            <x-report.export-actions module="outstanding-bills" :as-of="$asOf" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Total outstanding" tone="warning" :value="$currencyCode . ' ' . number_format($totalOutstanding, 2)" :hint="$rows->count() . ' bills'" />
    </x-slot:kpis>
    <x-dashboard.panel title="Open bills">
        <div class="overflow-x-auto">
            <table class="erp-dash-statement__table w-full text-sm">
                <thead><tr><th>Bill</th><th>Supplier</th><th>Bill date</th><th>Due</th><th class="text-right">Days</th><th class="text-right">Outstanding</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ $row[0] }}</td><td>{{ $row[1] }}</td><td>{{ $row[2] }}</td><td>{{ $row[3] }}</td>
                            <td class="text-right">{{ $row[4] }}</td><td class="text-right tabular-nums font-medium">{{ $row[7] }}</td><td>{{ $row[8] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-dashboard.panel>
</x-report.page>
@endsection
