@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-(--breakpoint-2xl) space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $supplier->name }}</h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    Vendor Master
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Vendor profile and procurement activity.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.suppliers.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Back to suppliers
            </a>
            <x-admin.action-group>
                <x-admin.action-edit :href="route('admin.suppliers.edit', $supplier)" />
                <x-admin.action-delete
                    :action="route('admin.suppliers.destroy', $supplier)"
                    confirm="Delete supplier {{ $supplier->name }}? This cannot be undone."
                />
            </x-admin.action-group>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Purchase orders</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $supplier->purchase_orders_count }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Goods receipts</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $supplier->goods_receipts_count }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Purchase bills</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $supplier->bills_count }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Purchase categories</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $supplier->productCategories->count() }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Contact details</h2>
        </div>
        <div class="grid gap-6 p-6 sm:grid-cols-2">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Contact person</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $supplier->contact_person ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $supplier->email ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Phone</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $supplier->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tax ID / BIN</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $supplier->tax_id ?? '—' }}</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Address</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-line">{{ $supplier->address ?? '—' }}</p>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-6 py-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Purchase categories</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Products and services purchased from this supplier.</p>
            </div>
            <a href="{{ route('admin.suppliers.edit', $supplier) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit categories
            </a>
        </div>
        @if($supplier->productCategories->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[480px]">
                    <thead class="border-b border-gray-100 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/50">
                        <tr>
                            <th class="w-12 px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($supplier->productCategories as $category)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                                <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                                <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $category->name }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $category->description ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                No categories assigned yet.
            </p>
        @endif
    </div>
</div>
@endsection
