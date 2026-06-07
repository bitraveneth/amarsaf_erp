@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $taxClass->name }}
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    {{ number_format($taxClass->rate, 2) }}%
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                HSN/SAC and local tax codes used on product master records.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.tax-classes.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Tax Classes
            </a>
            <x-admin.action-group>
                <x-admin.action-edit :href="route('admin.tax-classes.edit', $taxClass)" />
                <x-admin.action-delete
                    :action="route('admin.tax-classes.destroy', $taxClass)"
                    confirm="Delete tax class {{ $taxClass->name }}? This action cannot be undone."
                />
            </x-admin.action-group>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Rate</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($taxClass->rate, 2) }}%</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">HSN / SAC</p>
            <p class="mt-1 text-sm font-semibold font-mono text-gray-900 dark:text-white">{{ $taxClass->hsn_code ?? '—' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Local tax code</p>
            <p class="mt-1 text-sm font-semibold font-mono text-gray-900 dark:text-white">{{ $taxClass->local_tax_code ?? '—' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Linked products</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $taxClass->products()->count() }}</p>
        </div>
    </div>

    @if($products->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Products using this tax class</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Name</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($products as $product)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 font-mono text-sm text-gray-900 dark:text-white">{{ $product->sku }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-right">
                                    <x-admin.action-view :href="route('admin.products.show', $product)" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-400">No products are assigned to this tax class yet.</p>
        </div>
    @endif

    <div class="rounded-lg bg-blue-light-50 p-4 border border-blue-light-100 dark:bg-blue-light-500/10 dark:border-blue-light-500/20">
        <p class="text-sm text-blue-light-800 dark:text-blue-light-300">
            Created {{ $taxClass->created_at->format('d M Y') }} · Last updated {{ $taxClass->updated_at->diffForHumans() }}
        </p>
    </div>
</div>
@endsection
