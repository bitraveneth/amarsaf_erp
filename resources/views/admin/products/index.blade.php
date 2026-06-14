@extends('layouts.app')

@section('content')
@php
    use App\Support\ProductUnits;

    $snapshotCards = [
        [
            'label' => 'All SKUs',
            'numeric' => number_format($stats['total']),
            'caption' => 'Finished goods catalog',
            'href' => route('admin.products.index'),
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'Active',
            'numeric' => number_format($stats['active']),
            'caption' => 'Available on orders',
            'href' => route('admin.products.index', ['filter' => 'active']),
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
        [
            'label' => 'Missing barcode',
            'numeric' => number_format($stats['missing_barcode']),
            'caption' => 'Needs EAN / code',
            'href' => route('admin.products.index', ['filter' => 'missing_barcode']),
            'tone' => 'error',
            'valueTone' => ($stats['missing_barcode'] ?? 0) > 0 ? 'danger' : 'neutral',
            'icon' => 'alert',
        ],
        [
            'label' => 'Incomplete specs',
            'numeric' => number_format($stats['incomplete_specs']),
            'caption' => 'Volume or UOM missing',
            'href' => route('admin.products.index', ['filter' => 'incomplete_specs']),
            'tone' => 'orange',
            'valueTone' => ($stats['incomplete_specs'] ?? 0) > 0 ? 'danger' : 'neutral',
            'icon' => 'production',
        ],
    ];

    $filterLabels = [
        'active' => 'Active only',
        'inactive' => 'Inactive only',
        'missing_barcode' => 'Missing barcode',
        'incomplete_specs' => 'Incomplete specs',
    ];
@endphp

<div class="dash-page space-y-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="erp-dash-h1">Product catalog</h1>
            <p class="mt-1 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                Master data for finished goods — identity, specs, and compliance. Pricing is managed on the price list.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.products.prices.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Price list
            </a>
            <a href="{{ route('admin.products.create') }}"
               data-tour="products-primary-action"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600">
                Add product
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">
            {{ session('status') }}
        </div>
    @endif

    <x-dashboard.snapshot-kpis size="lg" :show-header="false" :cards="$snapshotCards" />

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Finished goods</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        @if($hasActiveFilters)
                            Filtered view
                            @if(isset($filterLabels[$filter ?? '']))
                                · {{ $filterLabels[$filter] }}
                            @endif
                        @else
                            Fixed product line · {{ $products->count() }} SKUs
                        @endif
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.products.index') }}" class="mt-4 flex flex-wrap items-center gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search SKU, name, brand, barcode…"
                       class="w-full min-w-[12rem] flex-1 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:max-w-xs">
                <select name="filter" onchange="this.form.submit()"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All products</option>
                    @foreach($filterLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('filter') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-gray-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-gray-700">Apply</button>
                @if($hasActiveFilters)
                    <a href="{{ route('admin.products.index') }}" class="text-sm font-medium text-brand-600 dark:text-brand-400">Clear</a>
                @endif
            </form>
        </div>

        @if($products->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">No products match your filters.</p>
                <a href="{{ route('admin.products.create') }}" class="mt-4 inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Add product</a>
            </div>
        @else
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[960px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-left dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6">SKU</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">UOM</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Barcode</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Trade price</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($products as $product)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30">
                                <td class="px-5 py-3 font-mono text-sm text-gray-900 dark:text-white sm:px-6">{{ $product->sku }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.products.show', $product) }}"
                                       class="text-sm font-medium text-gray-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-400">
                                        {{ $product->name }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $product->size ?: ($product->volume_ml ? (int) $product->volume_ml . 'ml' : $product->catalogCategoryLabel()) }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $product->uom ? ProductUnits::label($product->uom) : '—' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">
                                    @if(filled($product->barcode))
                                        {{ $product->barcode }}
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm tabular-nums text-gray-900 dark:text-white">
                                    @if((float) ($product->base_price ?? 0) > 0)
                                        {{ number_format($product->base_price, 2) }}
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($product->is_active)
                                        <span class="inline-flex rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-400">Active</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right sm:px-6">
                                    <x-admin.action-group>
                                        <x-admin.action-view :href="route('admin.products.show', $product)" />
                                        <x-admin.action-edit :href="route('admin.products.edit', $product)" />
                                        <x-admin.action-delete
                                            :action="route('admin.products.destroy', $product)"
                                            confirm="Delete this product?"
                                        />
                                    </x-admin.action-group>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
