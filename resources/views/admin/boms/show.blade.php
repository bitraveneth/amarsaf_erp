@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $bom->name ?: 'Default BOM' }}
                </h1>
                @if($bom->is_active)
                    <span class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400">
                        Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Inactive
                    </span>
                @endif
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ $bom->product?->name ?? 'Unknown product' }}
                @if($bom->product?->sku)
                    · SKU {{ $bom->product->sku }}
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.boms.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to BOMs
            </a>
            <x-admin.action-group>
                <x-admin.action-edit :href="route('admin.boms.edit', $bom)" />
                <x-admin.action-delete
                    :action="route('admin.boms.destroy', $bom)"
                    confirm="Delete this BOM? This cannot be undone."
                />
            </x-admin.action-group>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Components</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $bom->items->count() }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Material cost / unit</p>
            <p class="mt-1 text-2xl font-semibold text-brand-600 dark:text-brand-400">
                @if(! is_null($bomUnitCost))
                    BDT {{ number_format($bomUnitCost, 2) }}
                @else
                    —
                @endif
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Created</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $bom->created_at?->format('d M Y') ?? '—' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Last updated</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $bom->updated_at?->diffForHumans() ?? '—' }}</p>
        </div>
    </div>

    @if($bom->notes)
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Notes</p>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $bom->notes }}</p>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Components</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Material</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">SKU</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Qty per unit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Unit cost</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Line cost</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($bom->items as $item)
                        @php
                            $lineCost = ! is_null($item->unit_cost) ? (float) $item->unit_cost * (float) $item->quantity : null;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $item->component?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $item->component?->sku ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                {{ rtrim(rtrim(number_format($item->quantity, 4), '0'), '.') }} {{ $item->unit }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                {{ ! is_null($item->unit_cost) ? 'BDT ' . number_format($item->unit_cost, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                {{ ! is_null($lineCost) ? 'BDT ' . number_format($lineCost, 2) : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No components defined.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
