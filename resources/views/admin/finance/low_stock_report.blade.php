@extends('layouts.app')

@section('content')
@php
    $critical = $rows->filter(fn ($row) => $row['available'] <= 0)->count();
    $totalGap = $rows->sum('gap');
@endphp

<x-report.page
    eyebrow="Inventory reports"
    title="Low stock alert"
    subtitle="Products at or below reorder level — prioritise replenishment."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions>
            <x-report.hub-link category="operations" />
            <a href="{{ route('admin.inventory.low-stock') }}" class="erp-btn-secondary">Inventory low stock</a>
            <x-report.export-actions module="low-stock-report" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="SKUs below reorder"
            tone="warning"
            :value="(string) $count"
            hint="Needs replenishment"
        />
        <x-dashboard.kpi
            label="Out of stock"
            :tone="$critical > 0 ? 'danger' : 'success'"
            :value="(string) $critical"
            hint="Available qty is zero"
        />
        <x-dashboard.kpi
            label="Total shortage"
            tone="warning"
            :value="number_format($totalGap, 0)"
            hint="Units below reorder across SKUs"
        />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">Products to reorder</h2>
                <p class="erp-caption">Sorted by biggest shortage first</p>
            </div>
            <p class="erp-caption">{{ number_format($count) }} SKUs</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th class="is-right">Available</th>
                        <th class="is-right">Reorder</th>
                        <th class="is-right">Shortage</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $out = $row['available'] <= 0;
                            $badge = $out ? 'erp-badge-warning' : 'erp-badge-brand';
                            $label = $out ? 'Out of stock' : 'Below reorder';
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.products.show', $row['product']) }}" class="erp-table-link">{{ $row['product']->name }}</a>
                            </td>
                            <td class="erp-table-cell">{{ $row['product']->sku ?: '—' }}</td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($row['available'], 0) }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($row['reorder_level'], 0) }}</span></td>
                            <td class="is-right">
                                <span class="erp-table-num text-error-600 dark:text-error-400">{{ number_format($row['gap'], 0) }}</span>
                            </td>
                            <td><span class="{{ $badge }}">{{ $label }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state
                                    title="All products above reorder level"
                                    description="Nothing urgent right now. Revisit after the next sales or production cycle."
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
