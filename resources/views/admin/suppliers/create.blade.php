@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-(--breakpoint-2xl) space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Add Supplier</h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    Vendor Master
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Capture vendor details and what you purchase from them.
            </p>
        </div>
        <a href="{{ route('admin.suppliers.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Suppliers
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('admin.suppliers.store') }}" method="POST" class="p-6 sm:p-8">
            @csrf
            @include('admin.suppliers.partials.form')

            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.suppliers.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Save Supplier
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
