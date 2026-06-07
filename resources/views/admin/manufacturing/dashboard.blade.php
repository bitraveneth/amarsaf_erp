@extends('layouts.app')

@section('content')
<div class="erp-dash-page">
    <x-dashboard.hero
        eyebrow="Manufacturing"
        title="Manufacturing dashboard"
        subtitle="Production runs, approved output, batch activity, and QC backlog for the selected period."
        :period="$periodLabel"
    />

    <x-dashboard.period-filter
        :action="route('admin.manufacturing.dashboard')"
        :range="$range"
        :from="request('from', $from->toDateString())"
        :to="request('to', $to->toDateString())"
        :range-options="$rangeOptions"
    />

    <div class="erp-dash-kpi-grid">
        <x-dashboard.kpi label="Production runs" :value="number_format($totalRuns)" hint="Created in period" />
        <x-dashboard.kpi label="Total quantity" :value="number_format($totalQuantity, 0)" :hint="'Approved ' . number_format($approvedQuantity, 0)" />
        <x-dashboard.kpi label="Batches produced" :value="number_format($batchCount, 0)" :hint="$expiringSoon->count() . ' expiring in 60 days'" />
        <x-dashboard.kpi label="Pending QC" tone="warning" :value="number_format($pendingQcCount, 0)" hint="Runs awaiting approval" />
    </div>

    <div class="erp-dash-layout-split">
        <x-dashboard.panel title="Line output chart" subtitle="Quantity produced per line">
            <x-dashboard.bar-chart
                :labels="$chartLabels"
                :values="$chartValues"
                primary-label="Quantity"
            />
        </x-dashboard.panel>

        <x-dashboard.panel title="Top products" subtitle="Highest output in period">
            <x-dashboard.rank-list title="Products" :items="$topProducts" empty="No production runs for this period." />
        </x-dashboard.panel>
    </div>

    <div class="erp-dash-layout-split">
        <x-dashboard.panel title="Output by line" subtitle="Detailed breakdown">
            @if($byLine->isEmpty())
                <p class="erp-body text-gray-500 dark:text-gray-400">No line data recorded.</p>
            @else
                <ul class="space-y-3">
                    @foreach($byLine as $line => $qty)
                        <li class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                            <span class="erp-body">Line {{ $line ?: '—' }}</span>
                            <span class="erp-table-num">{{ number_format($qty, 0) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>

        <x-dashboard.panel title="Output by shift" subtitle="Shift-level totals">
            @if($byShift->isEmpty())
                <p class="erp-body text-gray-500 dark:text-gray-400">No shift data recorded.</p>
            @else
                <ul class="space-y-3">
                    @foreach($byShift as $shift => $qty)
                        <li class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                            <span class="erp-body">Shift {{ $shift ?: '—' }}</span>
                            <span class="erp-table-num">{{ number_format($qty, 0) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>
    </div>
</div>
@endsection
