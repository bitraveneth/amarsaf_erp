@extends('layouts.app')

@section('content')
@php
    $totalCategories = $productCategories->count();

    $snapshotCards = [
        [
            'label' => 'Total suppliers',
            'numeric' => number_format($totalSuppliers),
            'caption' => 'Registered vendors',
            'href' => route('admin.suppliers.index') . '#supplier-directory',
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'users',
        ],
        [
            'label' => 'Purchase categories',
            'numeric' => number_format($totalCategories),
            'caption' => 'Master purchase types',
            'href' => route('admin.suppliers.index') . '#purchase-categories',
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
        [
            'label' => 'Categorized',
            'numeric' => number_format($categorizedSuppliers),
            'caption' => 'Vendors tagged with purchases',
            'href' => route('admin.suppliers.index') . '#supplier-directory',
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'Needs categories',
            'numeric' => number_format($uncategorizedSuppliers),
            'caption' => 'Vendors still untagged',
            'href' => route('admin.suppliers.index') . '#supplier-directory',
            'tone' => 'orange',
            'valueTone' => $uncategorizedSuppliers > 0 ? 'warning' : 'neutral',
            'icon' => 'alert',
        ],
    ];
@endphp

<div class="dash-page">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="erp-dash-h1">Suppliers</h1>
        <div class="flex flex-wrap items-center gap-3">
            <a href="#purchase-categories" class="erp-btn-secondary">Manage categories</a>
            <a href="{{ route('admin.suppliers.create') }}"
               data-tour="suppliers-primary-action"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Add Supplier
            </a>
        </div>
    </div>

    <x-dashboard.snapshot-kpis
        class="mt-2"
        :show-header="false"
        :cards="$snapshotCards"
    />

    @if($suppliers->isNotEmpty())
        <section id="supplier-directory" class="mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Directory</h2>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                    {{ number_format($totalSuppliers) }}
                </span>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="data-table w-full min-w-[760px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                            <th class="px-5 py-4 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Supplier</th>
                            <th class="px-5 py-4 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Purchases</th>
                            <th class="px-5 py-4 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Contact</th>
                            <th class="px-5 py-4 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Phone</th>
                            <th class="px-5 py-4 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Tax ID</th>
                            <th class="px-5 py-4 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($suppliers as $supplier)
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                                            {{ strtoupper(substr($supplier->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-theme-sm font-medium text-gray-900 dark:text-white">{{ $supplier->name }}</p>
                                            @if($supplier->email)
                                                <p class="truncate text-theme-xs text-gray-500 dark:text-gray-400">{{ $supplier->email }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($supplier->productCategories->isNotEmpty())
                                        <div class="flex max-w-[220px] flex-wrap gap-1">
                                            @foreach($supplier->productCategories as $category)
                                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-theme-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                    {{ $category->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-theme-xs text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-900 dark:text-white">{{ $supplier->contact_person ?? '—' }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $supplier->phone ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    @if($supplier->tax_id)
                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            {{ $supplier->tax_id }}
                                        </span>
                                    @else
                                        <span class="text-theme-sm text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <x-admin.action-group>
                                        <x-admin.action-view :href="route('admin.suppliers.show', $supplier)" />
                                        <x-admin.action-edit :href="route('admin.suppliers.edit', $supplier)" />
                                        <x-admin.action-delete
                                            :action="route('admin.suppliers.destroy', $supplier)"
                                            confirm="Delete supplier {{ $supplier->name }}? This action cannot be undone."
                                        />
                                    </x-admin.action-group>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($suppliers->hasPages())
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </section>
    @else
        <section class="mt-2 rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto mb-4 h-20 w-20 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                </svg>
            </div>
            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">No suppliers yet</h3>
            <p class="mx-auto mb-6 max-w-md text-theme-sm text-gray-600 dark:text-gray-400">
                Add categories, then create suppliers and tag what you buy from each one.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="#purchase-categories" class="erp-btn-secondary">Add category</a>
                <a href="{{ route('admin.suppliers.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Add Supplier
                </a>
            </div>
        </section>
    @endif

    <div class="mt-2">
        @include('admin.suppliers.partials.product-categories-manager')
    </div>
</div>
@endsection
