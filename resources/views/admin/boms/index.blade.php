@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $statusFilter = $statusFilter ?? null;
    $stats = $stats ?? ['total' => 0, 'active' => 0, 'products' => 0, 'components' => 0];

    $filterTabs = [
        ['value' => null, 'label' => 'All recipes'],
        ['value' => 'active', 'label' => 'Active'],
        ['value' => 'inactive', 'label' => 'Inactive'],
    ];
@endphp

<div class="erp-order-page erp-order-page--index screen-boms">
    <x-admin.order-toolbar
        title="Bill of materials"
        subtitle="Manufacturing recipes — how much of each material goes into one finished unit."
        :back-url="route('admin.manufacturing.dashboard')"
        back-label="Manufacturing dashboard"
    >
        <x-slot:actions>
            <a href="{{ route('admin.products.index') }}" class="erp-order-btn erp-order-btn--secondary">Products</a>
            <a href="{{ route('admin.boms.create') }}" class="erp-order-btn erp-order-btn--primary" data-tour="boms-primary-action">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New BOM
            </a>
        </x-slot:actions>
    </x-admin.order-toolbar>

    @if(session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="erp-po-index-stats">
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Total recipes</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($stats['total']) }}</p>
            <p class="erp-po-index-stat__hint">All BOM versions</p>
        </div>

        <a href="{{ route('admin.boms.index', ['status' => 'active']) }}" class="erp-po-index-stat erp-po-index-stat--link">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Active</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($stats['active']) }}</p>
            <p class="erp-po-index-stat__hint">In use for production →</p>
        </a>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Products covered</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--neutral">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($stats['products']) }}</p>
            <p class="erp-po-index-stat__hint">Finished products with a recipe</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Component lines</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($stats['components']) }}</p>
            <p class="erp-po-index-stat__hint">Across all recipes</p>
        </div>
    </div>

    <div class="erp-po-index-filters">
        @foreach($filterTabs as $tab)
            <a href="{{ route('admin.boms.index', $tab['value'] ? ['status' => $tab['value']] : []) }}"
               @class([
                   'erp-po-index-filter',
                   'is-active' => $statusFilter === $tab['value'],
               ])>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <div class="erp-order-list-card erp-po-index-table-card">
        <div class="erp-po-index-table-card__head">
            <div>
                <h2 class="erp-po-index-table-card__title">Recipe register</h2>
                <p class="erp-po-index-table-card__desc">Click a recipe to view components and material cost per unit.</p>
            </div>
            @if($boms->total() > 0)
                <span class="erp-po-index-table-card__badge">
                    {{ number_format($boms->total()) }} {{ Str::plural('recipe', $boms->total()) }}
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="erp-order-list-table erp-po-index-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Recipe</th>
                        <th>Components</th>
                        <th class="is-right">Material / unit</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($boms as $bom)
                        @php
                            $productName = $bom->product?->name ?? '—';
                            $productInitial = strtoupper(substr(trim($productName), 0, 1)) ?: '—';
                            $bomUnitCost = $bom->computedMaterialUnitCost();
                            $componentCount = $bom->items_count ?? $bom->items->count();
                        @endphp
                        <tr>
                            <td>
                                <div class="erp-po-index-supplier">
                                    <span class="erp-po-index-supplier__avatar">{{ $productInitial }}</span>
                                    <div>
                                        <span class="erp-po-index-supplier__name">{{ $productName }}</span>
                                        @if($bom->product?->sku)
                                            <p class="erp-po-index-po-meta">{{ $bom->product->sku }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.boms.show', $bom) }}" class="erp-po-index-po-link">
                                    {{ $bom->recipeCode() }}
                                </a>
                                <p class="erp-po-index-po-meta">{{ $bom->displayName() }}</p>
                            </td>
                            <td>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $componentCount }} {{ Str::plural('line', $componentCount) }}
                                </span>
                            </td>
                            <td class="is-right whitespace-nowrap">
                                @if(! is_null($bomUnitCost))
                                    <span class="erp-table-num font-semibold text-gray-900 dark:text-white">
                                        {{ $currencyCode }} {{ number_format($bomUnitCost, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td>
                                @if($bom->is_active)
                                    <span class="erp-po-status erp-po-status--success">Active</span>
                                @else
                                    <span class="erp-po-status erp-po-status--neutral">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <x-admin.action-group class="justify-end">
                                    <x-admin.action-view :href="route('admin.boms.show', $bom)" />
                                    <x-admin.action-edit :href="route('admin.boms.edit', $bom)" />
                                    <x-admin.action-delete
                                        :action="route('admin.boms.destroy', $bom)"
                                        confirm="Delete this BOM? This cannot be undone."
                                    />
                                </x-admin.action-group>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="!px-6 !py-16 text-center">
                                <div class="erp-po-index-empty">
                                    <div class="erp-po-index-empty__icon" aria-hidden="true">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white">No BOMs defined</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        @if($statusFilter)
                                            No recipes match this filter. Try another status or create a new BOM.
                                        @else
                                            Create your first manufacturing recipe — name and units are filled automatically.
                                        @endif
                                    </p>
                                    <div class="mt-5 flex flex-wrap justify-center gap-2">
                                        <a href="{{ route('admin.products.index') }}" class="erp-order-btn erp-order-btn--secondary">Manage products</a>
                                        <a href="{{ route('admin.boms.create') }}" class="erp-order-btn erp-order-btn--brand">New BOM</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($boms->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $boms->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
