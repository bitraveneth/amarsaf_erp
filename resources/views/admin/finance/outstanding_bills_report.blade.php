@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
    $overdueCount = $rows->filter(fn ($row) => (int) ($row[4] ?? 0) > 0)->count();
@endphp

<x-report.page
    eyebrow="Receivables & payables"
    title="Outstanding bills"
    subtitle="Open supplier balances as of the selected date."
    :period="'As of ' . $asOf->format('d M Y')"
>
    <x-slot:actions>
        <x-report.header-actions :as-of-action="route('admin.reports.outstanding-bills')" :as-of="$asOf->toDateString()">
            <x-report.hub-link category="accountant" />
            <x-report.export-actions module="outstanding-bills" :as-of="$asOf" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Total outstanding"
            tone="warning"
            :value="$ccy . ' ' . number_format($totalOutstanding, 2)"
            :hint="$rows->count() . ' open ' . \Illuminate\Support\Str::plural('bill', $rows->count())"
        />
        <x-dashboard.kpi
            label="Past due"
            tone="danger"
            :value="(string) $overdueCount"
            hint="Bills with days past due"
        />
        <x-dashboard.kpi
            label="As of"
            :value="$asOf->format('d M Y')"
            hint="Change date in the header"
        />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">Open bills</h2>
                <p class="erp-caption">Supplier payables still unpaid as of {{ $asOf->format('d M Y') }}</p>
            </div>
            <p class="erp-caption">{{ number_format($rows->count()) }} rows</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>Bill</th>
                        <th>Supplier</th>
                        <th>Bill date</th>
                        <th>Due</th>
                        <th class="is-right">Days</th>
                        <th class="is-right">Outstanding</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $days = (int) ($row[4] ?? 0);
                            $status = strtolower((string) ($row[8] ?? ''));
                            $statusClass = match (true) {
                                str_contains($status, 'part') => 'erp-badge-warning',
                                default => 'erp-badge-brand',
                            };
                        @endphp
                        <tr>
                            <td><span class="font-mono text-sm">{{ $row[0] }}</span></td>
                            <td>{{ $row[1] }}</td>
                            <td class="whitespace-nowrap">{{ $row[2] }}</td>
                            <td class="whitespace-nowrap">{{ $row[3] }}</td>
                            <td class="is-right">
                                @if($days > 0)
                                    <span class="erp-badge-warning">{{ $days }}</span>
                                @else
                                    <span class="erp-table-num">{{ $days }}</span>
                                @endif
                            </td>
                            <td class="is-right"><span class="erp-table-num">{{ $row[7] }}</span></td>
                            <td><span class="{{ $statusClass }}">{{ $row[8] }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state
                                    title="No outstanding bills"
                                    description="Open and partly paid supplier bills will show here once they have a remaining balance."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-report.page>
@endsection
