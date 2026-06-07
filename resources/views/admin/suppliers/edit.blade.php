@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-(--breakpoint-2xl) space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit supplier</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $supplier->name }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.suppliers.show', $supplier) }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                View supplier
            </a>
            <a href="{{ route('admin.suppliers.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Back to list
            </a>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST" class="p-6">
            @csrf
            @method('PATCH')

            @include('admin.suppliers.partials.form', ['supplier' => $supplier])

            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.suppliers.show', $supplier) }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
