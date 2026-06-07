@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="inventory"
        title="Inventory Valuation"
        subtitle="Weighted-average stock value compared with posted inventory GL balance."
    >
        <x-slot:actions>
            @include('admin.finance.partials.print_button')
        </x-slot:actions>
    </x-admin.page-header>

    <div class="erp-stat-grid">
        <x-admin.stat-card label="Operational value" :value="number_format($operationalValue, 2)" hint="Qty × avg cost from inventory valuations" />
        <x-admin.stat-card label="GL inventory balance" :value="number_format($ledgerValue, 2)" hint="Raw materials + finished goods + WIP" />
        <x-admin.stat-card
            label="Variance"
            :value="($variance >= 0 ? '+' : '') . number_format($variance, 2)"
            hint="Operational minus GL"
            :tone="abs($variance) < 0.01 ? 'success' : 'warning'"
        />
    </div>

    <x-admin.table-card title="Stock valuation detail">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Warehouse</th>
                    <th>GL account</th>
                    <th class="is-right">Qty on hand</th>
                    <th class="is-right">Avg unit cost</th>
                    <th class="is-right">Total value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="font-medium text-gray-900 dark:text-white">{{ $row['product']?->name ?? '—' }}</td>
                        <td>{{ $row['warehouse']?->name ?? '—' }}</td>
                        <td>{{ $row['inventory_account'] }}</td>
                        <td class="is-right">{{ number_format($row['quantity'], 2) }}</td>
                        <td class="is-right">{{ number_format($row['avg_unit_cost'], 4) }}</td>
                        <td class="is-right font-medium">{{ number_format($row['total_value'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-admin.empty-state
                                title="No inventory valuations yet"
                                description="Post a GRN with unit cost or confirm production to build valuation records."
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
                <tfoot class="bg-gray-50 font-semibold dark:bg-gray-800/50">
                    <tr>
                        <td colspan="5">Total operational value</td>
                        <td class="is-right">{{ number_format($operationalValue, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </x-admin.table-card>
</div>
@endsection
