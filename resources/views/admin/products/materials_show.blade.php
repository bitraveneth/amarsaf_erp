@extends('layouts.app')

@section('content')
@php
    use App\Support\ProductUnits;

    $type = $product->product_type ?? 'raw';
    $typeLabels = [
        'raw' => 'Raw Material',
        'service' => 'Service',
        'inhouse' => 'In-house Step',
    ];
    $typeLabel = $typeLabels[$type] ?? ucfirst($type);
    $sourcingLabels = [
        'purchased' => 'Purchased',
        'inhouse' => 'Made in-house',
        'both' => 'Buy + make',
    ];
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
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
                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    {{ $typeLabel }}
                </span>
            </div>
            <p class="mt-2 font-mono text-sm text-gray-500 dark:text-gray-400">{{ $product->sku }}</p>
            @if($product->chemical_name)
                <p class="mt-1 text-sm text-teal-700 dark:text-teal-400">{{ $product->chemical_name }}</p>
            @endif
            @if($product->description)
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $product->description }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.materials.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
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

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $product->materialCategory?->name ?? '—' }}</p>
            @if($product->materialCategory?->group)
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $product->materialCategory->group }}</p>
            @endif
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Unit (UOM)</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $product->uom ? ProductUnits::label($product->uom) : '—' }}
            </p>
            @if($product->uom)
                <p class="mt-1 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $product->uom }}</p>
            @endif
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Standard cost</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $product->standard_cost !== null ? 'BDT ' . number_format($product->standard_cost, 2) : '—' }}
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Sourcing</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                @if($type === 'raw')
                    {{ $sourcingLabels[$product->sourcing ?? 'purchased'] ?? 'Purchased' }}
                @else
                    —
                @endif
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Supplier</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $product->supplier_name ?? '—' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Reorder level</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $product->reorder_level ?? '—' }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Details</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Size / variant</p>
                <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->size ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Chemical name</p>
                <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->chemical_name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Last updated</p>
                <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->updated_at->diffForHumans() }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Manage units</p>
                <a href="{{ route('admin.units.index') }}" class="mt-1 inline-flex text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    Units of measure →
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
