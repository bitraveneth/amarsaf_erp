@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
    $avgProgress = $rows->isNotEmpty() ? round($rows->avg('progress'), 1) : 0;
    $totalTarget = $rows->sum('target_amount');
    $totalAchieved = $rows->sum('achieved');
    $hitCount = $rows->filter(fn ($row) => $row['progress'] >= 100)->count();
@endphp

<x-report.page
    eyebrow="Sales reports"
    title="Sales target vs actual"
    subtitle="Target achievement by agent or employee for the selected period."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.sales-targets')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="sales" />
            <a href="{{ route('admin.sales-targets.index') }}" class="erp-btn-secondary">Target board</a>
            <x-report.export-actions module="sales-targets" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Targets in view"
            :value="(string) $rows->count()"
            hint="Overlapping this period"
        />
        <x-dashboard.kpi
            label="Avg. achievement"
            :tone="$avgProgress >= 100 ? 'success' : ($avgProgress >= 70 ? 'brand' : 'warning')"
            :value="$avgProgress . '%'"
            hint="Across listed targets"
        />
        <x-dashboard.kpi
            label="On / above target"
            tone="success"
            :value="(string) $hitCount"
            hint="Reached 100% or more"
        />
        <x-dashboard.kpi
            label="Achieved"
            :value="$ccy . ' ' . number_format($totalAchieved, 0)"
            :hint="'of ' . $ccy . ' ' . number_format($totalTarget, 0) . ' target'"
        />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">Targets</h2>
                <p class="erp-caption">{{ $periodLabel }}</p>
            </div>
            <p class="erp-caption">{{ number_format($rows->count()) }} targets</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th class="is-right">Target</th>
                        <th class="is-right">Achieved</th>
                        <th class="is-right">Gap</th>
                        <th class="is-right">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $progress = min(100, max(0, (float) $row['progress']));
                            $hit = $row['progress'] >= 100;
                        @endphp
                        <tr>
                            <td class="erp-body-strong">{{ $row['name'] }}</td>
                            <td class="is-right"><span class="erp-table-num">{{ $ccy }} {{ number_format($row['target_amount'], 2) }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ $ccy }} {{ number_format($row['achieved'], 2) }}</span></td>
                            <td class="is-right">
                                <span @class(['erp-table-num', 'text-error-600 dark:text-error-400' => $row['gap'] > 0, 'text-success-600 dark:text-success-400' => $row['gap'] <= 0])>
                                    {{ $ccy }} {{ number_format($row['gap'], 2) }}
                                </span>
                            </td>
                            <td class="is-right">
                                <div class="inline-flex min-w-[7.5rem] flex-col items-end gap-1">
                                    <span @class(['text-sm font-semibold tabular-nums', 'text-success-600 dark:text-success-400' => $hit])>
                                        {{ number_format($row['progress'], 1) }}%
                                    </span>
                                    <span class="h-1.5 w-28 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                        <span
                                            @class(['block h-full rounded-full', 'bg-success-500' => $hit, 'bg-brand-500' => ! $hit])
                                            style="width: {{ $progress }}%"
                                        ></span>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-admin.empty-state
                                    title="No targets overlap this period"
                                    description="Create sales targets on the board, or widen the date range."
                                >
                                    <x-slot:action>
                                        <a href="{{ route('admin.sales-targets.index') }}" class="erp-btn-primary">Open target board</a>
                                    </x-slot:action>
                                </x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-report.page>
@endsection
