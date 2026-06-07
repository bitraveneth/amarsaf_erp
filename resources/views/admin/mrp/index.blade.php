@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="mrp"
        title="MRP Suggestions"
        subtitle="Suggested purchase quantities based on reorder levels and active BOM component requirements."
    />

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <x-admin.table-card title="Below reorder level" description="Products where available stock plus open PO quantity is below the reorder level.">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="is-right">Available</th>
                        <th class="is-right">Reorder</th>
                        <th class="is-right">Suggest PO</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reorderSuggestions as $row)
                        <tr>
                            <td class="font-medium text-gray-900 dark:text-white">{{ $row['product']->name }}</td>
                            <td class="is-right">{{ number_format($row['available'], 2) }}</td>
                            <td class="is-right">{{ number_format($row['reorder_level'], 0) }}</td>
                            <td class="is-right font-semibold text-brand-600 dark:text-brand-400">{{ number_format($row['suggested_qty'], 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-admin.empty-state title="No reorder suggestions" description="Set reorder levels on products or wait until stock falls below those thresholds." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.table-card>

        <x-admin.table-card title="BOM component shortages" description="Active bill-of-materials components that need replenishment.">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Component</th>
                        <th class="is-right">Required</th>
                        <th class="is-right">Available</th>
                        <th class="is-right">Suggest PO</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bomSuggestions as $row)
                        <tr>
                            <td class="font-medium text-gray-900 dark:text-white">{{ $row['product']->name }}</td>
                            <td class="is-right">{{ number_format($row['required_for_bom'], 2) }}</td>
                            <td class="is-right">{{ number_format($row['available'], 2) }}</td>
                            <td class="is-right font-semibold text-brand-600 dark:text-brand-400">{{ number_format($row['suggested_qty'], 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-admin.empty-state title="No BOM shortages" description="All active BOM components have sufficient stock on hand." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.table-card>
    </div>
</div>
@endsection
