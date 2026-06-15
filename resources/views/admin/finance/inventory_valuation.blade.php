@extends('layouts.app')

@section('content')
@php
    $ccy = config('app.currency', 'BDT');
    $varianceTone = abs($variance) < 0.01 ? 'success' : 'warning';
    $categoryTones = [
        'raw_materials' => 'brand',
        'finished_goods' => 'success',
        'wip' => 'warning',
    ];
@endphp

<x-report.page
    eyebrow="Inventory reports"
    title="Inventory valuation"
    subtitle="How much money is tied up in stock — split by raw materials, work in progress, and finished goods."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions>
            <x-report.hub-link category="operations" />
            <x-report.export-actions module="inventory-valuation" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Total inventory worth"
            :value="$ccy . ' ' . number_format($operationalValue, 2)"
            hint="Operational value from qty × average cost"
        />
        <x-dashboard.kpi
            label="Raw materials"
            :tone="$categoryTones['raw_materials']"
            :value="$ccy . ' ' . number_format($categories['raw_materials']['operational'] ?? 0, 2)"
            hint="Purchased inputs still on hand"
        />
        <x-dashboard.kpi
            label="Finished goods"
            :tone="$categoryTones['finished_goods']"
            :value="$ccy . ' ' . number_format($categories['finished_goods']['operational'] ?? 0, 2)"
            hint="Sellable stock ready to ship"
        />
        <x-dashboard.kpi
            label="Work in progress"
            :tone="$categoryTones['wip']"
            :value="$ccy . ' ' . number_format($categories['wip']['operational'] ?? 0, 2)"
            hint="Material cost in open production"
        />
    </x-slot:kpis>

    <div class="erp-dash-layout-thirds">
        @foreach($categories as $key => $category)
            @php
                $share = $operationalValue > 0 ? round(($category['operational'] / $operationalValue) * 100, 1) : 0;
            @endphp
            <x-dashboard.panel :title="$category['label']" :subtitle="$share . '% of total stock value'">
                <div class="space-y-3">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Operational</p>
                            <p class="text-2xl font-bold tabular-nums text-gray-900 dark:text-white">{{ number_format($category['operational'], 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">GL balance</p>
                            <p class="text-sm font-semibold tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($category['gl'], 2) }}</p>
                        </div>
                    </div>
                    <x-dashboard.progress
                        :label="'Share of inventory'"
                        :tone="$categoryTones[$key] ?? 'brand'"
                        :percent="$share"
                        :hint="$category['rows']->count() . ' product lines on hand'"
                    />
                </div>
            </x-dashboard.panel>
        @endforeach
    </div>

    <x-dashboard.panel
        title="Ledger reconciliation"
        subtitle="Operational stock value compared with posted inventory GL accounts"
    >
        <div class="erp-dash-kpi-grid !grid-cols-1 sm:!grid-cols-3">
            <x-dashboard.kpi label="Operational total" :value="number_format($operationalValue, 2)" hint="Sum of valuation records" />
            <x-dashboard.kpi label="GL inventory balance" :value="number_format($ledgerValue, 2)" hint="Raw materials + finished goods + WIP" />
            <x-dashboard.kpi
                label="Variance"
                :tone="$varianceTone"
                :value="($variance >= 0 ? '+' : '') . number_format($variance, 2)"
                hint="Operational minus GL — investigate if large"
            />
        </div>
    </x-dashboard.panel>

    @foreach($categories as $category)
        @if($category['rows']->isNotEmpty())
            <x-dashboard.panel :title="$category['label'] . ' detail'" subtitle="Product lines contributing to this bucket">
                <div class="overflow-x-auto">
                    <table class="erp-dash-statement__table">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Warehouse</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Avg cost</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category['rows'] as $row)
                                <tr class="border-b border-gray-50 dark:border-gray-800/60">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $row['product']?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $row['warehouse']?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-800 dark:text-gray-100">{{ number_format($row['quantity'], 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-600 dark:text-gray-300">{{ number_format($row['avg_unit_cost'], 4) }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format($row['total_value'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50/80 dark:bg-gray-800/40">
                                <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Subtotal</td>
                                <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-gray-900 dark:text-white">{{ number_format($category['operational'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-dashboard.panel>
        @endif
    @endforeach

    @if($rows->isEmpty())
        <x-dashboard.panel title="No stock on hand">
            <x-admin.empty-state
                title="No inventory valuations yet"
                description="Post a GRN with unit cost or confirm production to build valuation records."
            />
        </x-dashboard.panel>
    @endif
</x-report.page>
@endsection
