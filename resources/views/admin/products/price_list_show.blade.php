@extends('layouts.app')

@section('content')
@php
    $mrp = $product->mrp;
    $base = $product->base_price;
    $discount = ($mrp && $mrp > 0 && $base !== null)
        ? round((($mrp - $base) / $mrp) * 100, 1)
        : null;
@endphp

<div class="dash-page space-y-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="erp-dash-h1">{{ $product->name }}</h1>
            <p class="mt-1 font-mono text-sm text-gray-500 dark:text-gray-400">{{ $product->sku }}</p>
            @if($product->packagingType || $product->size)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $product->packagingType->name ?? $product->size }}
                </p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.products.prices.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Back to price list
            </a>
            <a href="{{ route('admin.products.edit', $product) }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Edit product
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">MRP</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-gray-900 dark:text-white">
                @if($mrp)
                    BDT {{ number_format($mrp, 2) }}
                @else
                    <span class="text-lg text-amber-600 dark:text-amber-400">Not set</span>
                @endif
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Label / max retail</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Trade price</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-gray-900 dark:text-white">
                BDT {{ number_format($base ?? 0, 2) }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default on sales orders</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Trade discount</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-gray-900 dark:text-white">
                {{ $discount !== null ? $discount . '%' : '—' }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Off MRP</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Standard cost</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-gray-900 dark:text-white">
                BDT {{ number_format($product->standard_cost ?? 0, 2) }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">From product master</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Special agent prices</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    Override trade price for specific agents · blank removes override
                </p>
            </div>
            @if($agentPrices->isNotEmpty())
                <span class="rounded-full bg-purple-50 px-2.5 py-1 text-xs font-medium text-purple-700 dark:bg-purple-500/15 dark:text-purple-300">
                    {{ $agentPrices->count() }} {{ Str::plural('agent', $agentPrices->count()) }}
                </span>
            @endif
        </div>

        @if($agentPrices->isEmpty())
            <div class="px-6 py-14 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">No special prices — all agents use the trade price.</p>
                <a href="{{ route('admin.agents.index') }}" class="mt-4 inline-flex text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    Set prices on agent commercial terms →
                </a>
            </div>
        @else
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[720px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-left dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6">Agent</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Area</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Special price</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">vs trade</th>
                            <th class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($agentPrices as $row)
                            @php
                                $diff = $row->price - ($base ?? 0);
                                $pct = ($base ?? 0) > 0 ? round(($diff / $base) * 100, 1) : 0;
                            @endphp
                            <tr x-data="{ editing: false }">
                                <td x-show="!editing" class="px-5 py-3 sm:px-6">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $row->agent->name ?? '—' }}</p>
                                    @if($row->agent?->code)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $row->agent->code }}</p>
                                    @endif
                                </td>
                                <td x-show="!editing" class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $row->agent->area ?? '—' }}
                                    @if($row->agent?->zone)
                                        · {{ $row->agent->zone }}
                                    @endif
                                </td>
                                <td x-show="!editing" class="px-4 py-3 text-sm font-semibold tabular-nums text-gray-900 dark:text-white">
                                    BDT {{ number_format($row->price, 2) }}
                                </td>
                                <td x-show="!editing" class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                        {{ $diff > 0 ? 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400' :
                                           ($diff < 0 ? 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400' :
                                           'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                                        {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }} ({{ $pct }}%)
                                    </span>
                                </td>
                                <td x-show="!editing" class="px-5 py-3 text-right sm:px-6">
                                    <x-admin.action-group>
                                        <button type="button" @click="editing = true" class="erp-btn-action">Edit</button>
                                        <form action="{{ route('admin.products.prices.overrides.destroy', $row) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Remove special price for this agent?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="erp-btn-action-danger">Remove</button>
                                        </form>
                                    </x-admin.action-group>
                                </td>
                                <td x-show="editing" x-cloak colspan="5" class="px-5 py-4 sm:px-6">
                                    <form method="POST" action="{{ route('admin.products.prices.overrides.update', $row) }}" class="flex flex-wrap items-end gap-3">
                                        @csrf
                                        @method('PATCH')
                                        <div class="min-w-[140px] flex-1">
                                            <label class="mb-1 block text-xs font-medium text-gray-500">Agent</label>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $row->agent->name ?? '—' }}</p>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-gray-500">Price (BDT)</label>
                                            <input type="number" step="0.01" min="0" name="price" value="{{ $row->price }}" required
                                                   class="w-32 rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        </div>
                                        <div class="min-w-[180px] flex-1">
                                            <label class="mb-1 block text-xs font-medium text-gray-500">Notes</label>
                                            <input type="text" name="notes" value="{{ $row->notes }}"
                                                   class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                   placeholder="Optional">
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
