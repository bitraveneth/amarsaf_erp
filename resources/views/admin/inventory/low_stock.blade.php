@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="inventory"
        title="Low Stock"
        subtitle="Stock-tracked products currently below their configured reorder level."
    />

    <x-admin.table-card title="Products needing attention" description="Available quantity is less than the reorder level.">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="is-right">Available</th>
                    <th class="is-right">Reorder level</th>
                    <th class="is-right">Shortage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $row['product']->name }}</div>
                            @if($row['product']->sku)
                                <div class="mt-0.5 text-xs text-gray-500">{{ $row['product']->sku }}</div>
                            @endif
                        </td>
                        <td class="is-right">{{ number_format($row['available'], 2) }}</td>
                        <td class="is-right">{{ number_format($row['reorder_level'], 0) }}</td>
                        <td class="is-right font-semibold text-error-600 dark:text-error-400">{{ number_format($row['shortage'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <x-admin.empty-state title="No low-stock products" description="Either all stock levels are healthy or reorder levels have not been configured yet." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.table-card>
</div>
@endsection
