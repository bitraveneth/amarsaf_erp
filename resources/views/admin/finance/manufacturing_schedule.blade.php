@extends('layouts.app')

@section('content')
@php
    $ccy = config('app.currency', 'BDT');
    $total = (float) ($schedule['total'] ?? 0);
    $lineCount = collect($schedule['rows'] ?? [])->reject(fn ($row) => ! empty($row['is_subtotal']))->count();
@endphp

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
            <a href="{{ route('admin.production.index') }}" class="erp-btn-secondary">Production</a>
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Total manufacturing cost"
            tone="brand"
            :value="$ccy . ' ' . number_format($total, 0)"
            :hint="$periodLabel"
        />
        <x-dashboard.kpi
            label="Schedule lines"
            :value="(string) $lineCount"
            hint="Accounts in this schedule"
        />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">{{ $schedule['name'] ?? 'Manufacturing account' }}</h2>
                <p class="erp-caption">Posted manufacturing costs in {{ $periodLabel }}</p>
            </div>
            <p class="erp-caption">{{ $ccy }} {{ number_format($total, 2) }}</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th class="is-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($schedule['rows'] ?? []) as $row)
                        <tr @class(['font-semibold' => ! empty($row['is_subtotal'])])>
                            <td>
                                <span style="padding-left: {{ (($row['level'] ?? 0) * 1.1) }}rem" class="inline-block">
                                    {{ $row['name'] }}
                                </span>
                            </td>
                            <td class="is-right">
                                <span class="erp-table-num">{{ number_format($row['amount'] ?? 0, 2) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">
                                <x-admin.empty-state
                                    title="No manufacturing costs in this period"
                                    description="Confirm production or post manufacturing journals, then widen the date range if needed."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(! empty($schedule['rows']))
                    <tfoot>
                        <tr>
                            <td class="erp-body-strong">Total manufacturing cost</td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($total, 2) }}</span></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-report.page>
@endsection
