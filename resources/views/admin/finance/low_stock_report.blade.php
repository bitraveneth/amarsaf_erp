@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Inventory & production" title="Low stock alert" subtitle="Products at or below reorder level." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions>
            <x-report.hub-link category="operations" />
            <x-report.export-actions module="low-stock-report" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="SKUs below reorder" tone="warning" :value="(string) $count" hint="Needs replenishment" />
    </x-slot:kpis>
    <x-dashboard.panel title="Products">
        <x-report.table>
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <x-report.th>Product</x-report.th>
                    <x-report.th>SKU</x-report.th>
                    <x-report.th align="right">Available</x-report.th>
                    <x-report.th align="right">Reorder</x-report.th>
                    <x-report.th align="right">Shortage</x-report.th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-gray-50 dark:border-gray-800/60">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $row['product']->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $row['product']->sku }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['available'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['reorder_level'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-error-600">{{ number_format($row['gap'], 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8"><x-admin.empty-state title="All products above reorder level" /></td></tr>
                @endforelse
            </tbody>
        </x-report.table>
    </x-dashboard.panel>
</x-report.page>
@endsection
