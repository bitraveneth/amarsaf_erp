@extends('layouts.app')

@section('content')
@php
    $type = $product->product_type ?? 'raw';
    $typeLabels = [
        'raw' => 'Raw Material',
        'service' => 'Service',
        'inhouse' => 'In-house',
    ];
    $typeLabel = $typeLabels[$type] ?? ucfirst($type);
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $product->name }}
                </h1>
                @if($product->is_active)
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
                SKU {{ $product->sku }} · {{ $typeLabel }}
            </p>
            @if($product->description)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $product->description }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.materials.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Materials
            </a>
            <x-admin.action-group>
                <x-admin.action-edit :href="route('admin.materials.edit', $product)" />
                <x-admin.action-delete
                    :action="route('admin.materials.destroy', $product)"
                    confirm="Delete this material? This action cannot be undone."
                />
            </x-admin.action-group>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $product->materialCategory?->name ?? '—' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Unit of measure</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $product->uom ? ucfirst($product->uom) : '—' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Standard cost</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $product->standard_cost !== null ? 'BDT ' . number_format($product->standard_cost, 2) : '—' }}
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Supplier</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $product->supplier_name ?? '—' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Reorder level</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $product->reorder_level ?? '—' }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Additional details</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Packaging</p>
                <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->packagingType->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tax class</p>
                <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">
                    @if($product->taxClass)
                        {{ $product->taxClass->name }} ({{ number_format($product->taxClass->rate, 2) }}%)
                    @else
                        —
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Size</p>
                <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->size ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Last updated</p>
                <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
