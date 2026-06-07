@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-(--breakpoint-2xl) space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $supplier->name }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Vendor master profile and procurement activity.</p>
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
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Purchase orders</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $supplier->purchase_orders_count }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Goods receipts</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $supplier->goods_receipts_count }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Purchase bills</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $supplier->bills_count }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tax ID / BIN</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $supplier->tax_id ?? '—' }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Contact details</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
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
            <div class="sm:col-span-2">
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Address</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-line">{{ $supplier->address ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
