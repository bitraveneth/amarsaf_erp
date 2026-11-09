@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
    $overdueCount = $rows->filter(fn ($row) => (int) ($row[4] ?? 0) > 0)->count();
@endphp

<x-report.page
    eyebrow="Sales reports"
    title="Outstanding invoices"
    subtitle="Open customer balances as of the selected date."
    :period="'As of ' . $asOf->format('d M Y')"
>
    <x-slot:actions>
        <x-report.header-actions
            :as-of-action="route('admin.reports.outstanding-invoices')"
            :as-of="$asOf->toDateString()"
        >
            <x-report.hub-link category="sales" />
            <a href="{{ route('admin.finance.index') }}" class="erp-btn-secondary">Customer invoices</a>
            <x-report.export-actions module="outstanding-invoices" :as-of="$asOf" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Total outstanding"
            tone="warning"
            :value="$ccy . ' ' . number_format($totalOutstanding, 0)"
            :hint="$rows->count() . ' open ' . Str::plural('invoice', $rows->count())"
        />
        <x-dashboard.kpi
            label="Overdue invoices"
            :tone="$overdueCount > 0 ? 'danger' : 'success'"
            :value="(string) $overdueCount"
            hint="Past due date as of snapshot"
        />
        <x-dashboard.kpi
            label="As of"
            :value="$asOf->format('d M Y')"
            hint="Change date in the header calendar"
        />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">Open invoices</h2>
                <p class="erp-caption">As of {{ $asOf->format('d M Y') }}</p>
            </div>
            <p class="erp-caption">{{ number_format($rows->count()) }} invoices</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Agent</th>
                        <th>Issued</th>
                        <th>Due</th>
                        <th class="is-right">Days past due</th>
                        <th class="is-right">Outstanding</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $days = (int) ($row[4] ?? 0);
                            $overdue = $days > 0;
                            $status = strtolower((string) ($row[8] ?? ''));
                            $badge = match (true) {
                                $overdue => 'erp-badge-warning',
                                $status === 'adjusted' => 'erp-badge-brand',
                                default => 'erp-badge-neutral',
                            };
                        @endphp
                        <tr @class(['bg-error-50/40 dark:bg-error-500/5' => $overdue])>
                            <td class="erp-table-cell font-medium">{{ $row[0] }}</td>
                            <td class="erp-body-strong">{{ $row[1] }}</td>
                            <td class="erp-table-cell whitespace-nowrap">{{ $row[2] }}</td>
                            <td class="erp-table-cell whitespace-nowrap">{{ $row[3] }}</td>
                            <td class="is-right">
                                <span @class(['erp-table-num', 'text-error-600 dark:text-error-400' => $overdue])>
                                    {{ $row[4] }}
                                </span>
                            </td>
                            <td class="is-right"><span class="erp-table-num">{{ $row[7] }}</span></td>
                            <td>
                                <span class="{{ $badge }}">
                                    {{ $overdue ? 'Overdue' : $row[8] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state
                                    title="No outstanding invoices"
                                    description="All customer invoices are settled as of this date."
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
