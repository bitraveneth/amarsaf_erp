@extends('layouts.app')

@section('content')
@php
    $snapshotCards = [
        [
            'label' => 'All SKUs',
            'numeric' => number_format($stats['total']),
            'caption' => 'Active sellable products',
            'href' => route('admin.products.prices.index'),
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'MRP set',
            'numeric' => number_format($stats['with_mrp']),
            'caption' => 'Label price defined',
            'href' => route('admin.products.prices.index'),
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
        [
            'label' => 'Missing MRP',
            'numeric' => number_format($stats['missing_mrp']),
            'caption' => 'Needs label price',
            'href' => route('admin.products.prices.index', ['filter' => 'missing_mrp']),
            'tone' => 'error',
            'valueTone' => ($stats['missing_mrp'] ?? 0) > 0 ? 'danger' : 'neutral',
            'icon' => 'alert',
        ],
        [
            'label' => 'Special prices',
            'numeric' => number_format($stats['with_special_prices']),
            'caption' => $stats['special_price_rows'] . ' agent SKU prices',
            'href' => route('admin.products.prices.index', ['filter' => 'special_prices']),
            'tone' => 'purple',
            'valueTone' => 'neutral',
            'icon' => 'users',
        ],
    ];

    $filterLabels = [
        'missing_mrp' => 'Missing MRP',
        'special_prices' => 'Special agent prices',
        'base_above_mrp' => 'Trade above MRP',
    ];

    $priceListQuery = array_filter(request()->only(['q', 'filter']));
@endphp

<div class="dash-page space-y-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="erp-dash-h1">Price lists</h1>
            <p class="mt-1 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                Set MRP and trade prices per SKU. Special agent prices override trade price on sales orders.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.products.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Product catalog
            </a>
            <a href="{{ route('admin.agents.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Agent pricing
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
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Product prices</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        @if($hasActiveFilters)
                            Filtered view
                            @if(isset($filterLabels[$filter ?? '']))
                                · {{ $filterLabels[$filter] }}
                            @endif
                        @else
                            Fixed product price list · click Edit to update MRP and trade price
                        @endif
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.products.prices.index') }}" class="mt-4 flex flex-wrap items-center gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search SKU or name…"
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
                    <a href="{{ route('admin.products.prices.index') }}" class="text-sm font-medium text-brand-600 dark:text-brand-400">Clear</a>
                @endif
            </form>
        </div>

        @if($products->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">No products match your filters.</p>
            </div>
        @else
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[960px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-left dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6">SKU</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">MRP</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Trade</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Discount</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Special prices</th>
                            <th class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($products as $product)
                            @php
                                $mrp = $product->mrp;
                                $base = $product->base_price;
                                $discount = ($mrp && $mrp > 0 && $base !== null)
                                    ? round((($mrp - $base) / $mrp) * 100, 1)
                                    : null;
                                $tradeAboveMrp = $mrp && $base > $mrp;
                            @endphp
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30" x-data="{ editing: false }">
                                <td x-show="!editing" class="px-5 py-3 font-mono text-sm text-gray-900 dark:text-white sm:px-6">{{ $product->sku }}</td>
                                <td x-show="!editing" class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</div>
                                    @if($product->size)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $product->size }}</div>
                                    @endif
                                </td>
                                <td x-show="!editing" class="px-4 py-3 text-sm tabular-nums text-gray-900 dark:text-white">
                                    @if($mrp)
                                        {{ number_format($mrp, 2) }}
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400">—</span>
                                    @endif
                                </td>
                                <td x-show="!editing" class="px-4 py-3 text-sm tabular-nums font-medium text-gray-900 dark:text-white">
                                    {{ number_format($base ?? 0, 2) }}
                                    @if($tradeAboveMrp)
                                        <span class="ml-1 text-xs text-error-600" title="Trade price exceeds MRP">!</span>
                                    @endif
                                </td>
                                <td x-show="!editing" class="px-4 py-3 text-sm tabular-nums text-gray-600 dark:text-gray-300">
                                    @if($discount !== null)
                                        {{ $discount }}%
                                    @else
                                        —
                                    @endif
                                </td>
                                <td x-show="!editing" class="px-4 py-3">
                                    @if($product->agent_price_lists_count > 0)
                                        <a href="{{ route('admin.products.prices.show', $product) }}"
                                           class="inline-flex rounded-full bg-purple-50 px-2.5 py-0.5 text-xs font-medium text-purple-700 hover:bg-purple-100 dark:bg-purple-500/15 dark:text-purple-300">
                                            {{ $product->agent_price_lists_count }} {{ Str::plural('agent', $product->agent_price_lists_count) }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Uses trade price</span>
                                    @endif
                                </td>
                                <td x-show="!editing" class="px-5 py-3 text-right sm:px-6">
                                    <div class="flex justify-end gap-1">
                                        <button type="button" @click="editing = true" class="erp-btn-action">Edit</button>
                                        @if($product->agent_price_lists_count > 0)
                                            <a href="{{ route('admin.products.prices.show', $product) }}" class="erp-btn-action">Agents</a>
                                        @endif
                                    </div>
                                </td>
                                <td x-show="editing" x-cloak colspan="7" class="px-5 py-3 sm:px-6">
                                    <form method="POST" action="{{ route('admin.products.prices.update', $product) . ($priceListQuery ? '?' . http_build_query($priceListQuery) : '') }}" class="flex flex-wrap items-end gap-3">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label class="mb-1 block text-[10px] font-medium uppercase text-gray-500">MRP (BDT)</label>
                                            <input type="number" step="0.01" min="0" name="mrp" value="{{ $product->mrp }}"
                                                   class="w-32 rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[10px] font-medium uppercase text-gray-500">Trade price (BDT)</label>
                                            <input type="number" step="0.01" min="0" name="base_price" value="{{ $product->base_price }}" required
                                                   class="w-32 rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        </div>
                                        <button type="submit" class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600">Save</button>
                                        <button type="button" @click="editing = false" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                                    </form>
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
