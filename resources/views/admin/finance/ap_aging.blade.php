@extends('layouts.app')

@section('content')
@php
    $ccy = config('app.currency', 'BDT');
    $varianceTone = abs($variance) < 0.01 ? 'success' : 'warning';
    $overdueTotal = ($buckets['1_30'] ?? 0) + ($buckets['31_60'] ?? 0) + ($buckets['61_90'] ?? 0) + ($buckets['90_plus'] ?? 0);
@endphp

<x-report.page
    eyebrow="Receivables & payables"
    title="AP aging"
    subtitle="Suppliers you still need to pay — grouped by how overdue each bill is."
    :period="'As of ' . $asOf->format('d M Y')"
>
    <x-slot:actions>
        <x-report.header-actions
            :as-of-action="route('admin.reports.ap-aging')"
            :as-of="$asOf->toDateString()"
        >
            <x-report.export-actions module="ap-aging" :as-of="$asOf" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi label="Total outstanding" tone="warning" :value="$ccy . ' ' . number_format($subledgerTotal, 2)" hint="Unpaid supplier bills" />
        <x-dashboard.kpi label="Overdue amount" tone="danger" :value="$ccy . ' ' . number_format($overdueTotal, 2)" hint="Past due buckets combined" />
        <x-dashboard.kpi label="GL payables" :value="$ccy . ' ' . number_format($glBalance, 2)" hint="Posted trade creditors balance" />
        @if(abs($variance) >= 0.01)
            <x-dashboard.kpi label="Variance" :tone="$varianceTone" :value="($variance >= 0 ? '+' : '') . number_format($variance, 2)" hint="Sub-ledger vs GL — reconcile if large" />
        @endif
    </x-slot:kpis>

    <x-dashboard.panel title="Aging summary" subtitle="How long bills have been unpaid">
        <div class="overflow-x-auto">
            <table class="erp-dash-statement__table">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Bucket</th>
                        @foreach($bucketLabels as $label)
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</th>
                        @endforeach
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">All open bills</td>
                        @foreach($bucketLabels as $key => $label)
                            <td class="px-4 py-3 text-right text-sm tabular-nums">{{ number_format($buckets[$key] ?? 0, 2) }}</td>
                        @endforeach
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums">{{ number_format($subledgerTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-dashboard.panel>

    <x-dashboard.panel title="By supplier" subtitle="Outstanding balance per vendor">
        <div class="overflow-x-auto">
            <table class="erp-dash-statement__table">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Supplier</th>
                        @foreach($bucketLabels as $label)
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</th>
                        @endforeach
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bySupplier as $group)
                        <tr class="border-b border-gray-50 dark:border-gray-800/60">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $group['name'] }}</td>
                            @foreach($bucketLabels as $key => $label)
                                <td class="px-4 py-3 text-right text-sm tabular-nums">{{ number_format($group['buckets'][$key] ?? 0, 2) }}</td>
                            @endforeach
                            <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums">{{ number_format($group['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($bucketLabels) + 2 }}" class="px-4 py-8"><x-admin.empty-state title="No open payables" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-dashboard.panel>

    <x-dashboard.panel title="Open bills" subtitle="Bill-level detail for payment planning">
        <div class="overflow-x-auto">
            <table class="erp-dash-statement__table">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Bill</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Supplier</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Due</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Days past due</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Bucket</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="border-b border-gray-50 dark:border-gray-800/60">
                            <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-white">{{ $row['bill']->number }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['supplier']?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['due_at']?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums">{{ max(0, $row['days_past_due']) }}</td>
                            <td class="px-4 py-3 text-sm">{{ $bucketLabels[$row['bucket']] ?? $row['bucket'] }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums">{{ number_format($row['outstanding'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8"><x-admin.empty-state title="No open payables" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-dashboard.panel>
</x-report.page>
@endsection
