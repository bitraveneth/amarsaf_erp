@extends('layouts.app')

@section('content')
@php
    use App\Support\ProductUnits;

    $sectionLabelClass = 'text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400';
    $definitionRows = [
        ['Volume', $product->size ?: ($product->volume_ml ? (int) $product->volume_ml . 'ml' : '—')],
        ['Packaging', $product->packagingLabel() ?? '—'],
        ['UOM', $product->uom ? ProductUnits::label($product->uom) : '—'],
        ['Barcode', filled($product->barcode) ? $product->barcode : '—', true],
        ['Shelf life', $product->shelf_life_months ? $product->shelf_life_months . ' mo' : '—'],
        ['VAT', $product->taxClass ? $product->taxClass->name . ' (' . $product->taxClass->rate . '%)' : 'Not applicable'],
        ['Status', $product->is_active ? 'Active' : 'Inactive'],
    ];
@endphp

<div class="dash-page space-y-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="erp-dash-h1 truncate">{{ $product->name }}</h1>
                @if($product->isCatalogReady())
                    <span class="inline-flex rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-400">Ready</span>
                @elseif($product->is_active)
                    <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">Incomplete</span>
                @else
                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                @endif
            </div>
            <p class="mt-1 font-mono text-sm text-gray-500 dark:text-gray-400">{{ $product->sku }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.products.index') }}" class="erp-btn-secondary">Back</a>
            <a href="{{ route('admin.products.prices.show', $product) }}" class="erp-btn-secondary">Prices</a>
            <a href="{{ route('admin.products.edit', $product) }}" class="erp-btn-primary">Edit</a>
            <x-admin.action-delete :action="route('admin.products.destroy', $product)" confirm="Delete this product?" />
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">
            {{ session('status') }}
        </div>
    @endif

    @include('admin.products.partials.catalog_readiness', ['product' => $product])

    @if(in_array($product->product_type, [null, 'finished'], true))
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-gray-800 sm:px-6">
                <div>
                    <p class="{{ $sectionLabelClass }}">Manufacturing recipe</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Setup once — every production run uses the active BOM automatically.</p>
                </div>
                @if($activeBom)
                    <a href="{{ route('admin.boms.show', $activeBom) }}" class="erp-btn-secondary !py-1.5 !text-xs">View recipe</a>
                @else
                    <a href="{{ route('admin.boms.create', ['product_id' => $product->id]) }}" class="erp-btn-primary !py-1.5 !text-xs">Create recipe</a>
                @endif
            </div>
            <div class="p-5 sm:p-6">
                @if($activeBom)
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $activeBom->displayName() }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $activeBom->recipeCode() }} · {{ $activeBom->items_count }} {{ Str::plural('component', $activeBom->items_count) }} · active for production
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.boms.create', ['product_id' => $product->id, 'from_bom' => $activeBom->id]) }}" class="erp-btn-secondary !py-1.5 !text-xs">New version</a>
                            <a href="{{ route('admin.production.create') }}?product_id={{ $product->id }}" class="erp-btn-primary !py-1.5 !text-xs">New production run</a>
                            <a href="{{ route('admin.boms.edit', $activeBom) }}" class="erp-btn-secondary !py-1.5 !text-xs">Edit recipe</a>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        No active recipe yet. Production can be recorded, but materials will not auto-deduct until a BOM is defined.
                    </p>
                    <a href="{{ route('admin.boms.create', ['product_id' => $product->id]) }}" class="erp-btn-primary mt-4 inline-flex">Create BOM for this product</a>
                @endif
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-gray-800 sm:px-6">
            <p class="{{ $sectionLabelClass }}">Product master</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.products.edit', $product) }}" class="erp-btn-secondary !py-1.5 !text-xs">Edit</a>
                <a href="{{ route('admin.products.prices.show', $product) }}" class="erp-btn-secondary !py-1.5 !text-xs">Prices</a>
            </div>
        </div>
        <dl class="grid gap-x-6 gap-y-4 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
            @foreach($definitionRows as $row)
                @php
                    [$label, $value] = $row;
                    $mono = $row[2] ?? false;
                @endphp
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                    <dd class="mt-0.5 text-sm text-gray-900 dark:text-white {{ $mono ? 'font-mono' : '' }}">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
        <div class="grid grid-cols-3 gap-4 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-800">
            <div>
                <p class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Trade</p>
                <p class="mt-1 text-sm font-semibold tabular-nums text-gray-900 dark:text-white">
                    @if($product->hasPricingSet())
                        BDT {{ number_format($product->base_price, 2) }}
                    @else
                        <span class="text-amber-600 dark:text-amber-400">Not set</span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">MRP</p>
                <p class="mt-1 text-sm font-semibold tabular-nums text-gray-900 dark:text-white">
                    @if($product->mrp && (float) $product->mrp > 0)
                        BDT {{ number_format($product->mrp, 2) }}
                    @else
                        <span class="text-amber-600 dark:text-amber-400">Not set</span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Std cost</p>
                <p class="mt-1 text-sm font-semibold tabular-nums text-gray-900 dark:text-white">
                    BDT {{ number_format($product->standard_cost ?? 0, 2) }}
                </p>
            </div>
        </div>
    </div>

    @if($product->mineral_source || $product->ph || $product->tds || $product->certifications)
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-2.5 dark:border-gray-800 sm:px-6">
                <p class="{{ $sectionLabelClass }}">Water composition</p>
            </div>
            <dl class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4 sm:p-6">
                @foreach([
                    ['Mineral source', $product->mineral_source ?? '—'],
                    ['pH', $product->ph ?? '—'],
                    ['TDS', $product->tds ?? '—'],
                    ['Certifications', $product->certifications ?? '—'],
                ] as [$label, $value])
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                        <dd class="mt-0.5 text-sm text-gray-800 dark:text-gray-200">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endif

    @if($product->batches->isNotEmpty())
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-2.5 dark:border-gray-800 sm:px-6">
                <p class="{{ $sectionLabelClass }}">Batches</p>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[640px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-left dark:border-gray-800 dark:bg-gray-800/30">
                            <th class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6">Batch</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Production</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500">Expiry</th>
                            <th class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 sm:px-6">QC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($product->batches as $batch)
                            <tr>
                                <td class="px-5 py-3 text-sm text-gray-800 dark:text-gray-200 sm:px-6">{{ $batch->batch_code }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $batch->production_date ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $batch->expiry_date ?? '—' }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300 sm:px-6">{{ ucfirst($batch->qc_status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
