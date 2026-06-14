@extends('layouts.app')

@section('content')
<div class="dash-page space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="erp-dash-h1">{{ $packagingType->name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($packagingType->unit)
                    Unit: {{ $packagingType->unit }}
                @else
                    No unit set
                @endif
                @if($packagingType->units_per_pack)
                    · {{ $packagingType->units_per_pack }} per pack
                @endif
            </p>
            @if($packagingType->description)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $packagingType->description }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.packaging.index') }}" class="erp-btn-secondary">Back to packaging</a>
            <x-admin.action-edit :href="route('admin.packaging.edit', $packagingType)" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Linked products</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($packagingType->products_count) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Source</p>
            <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                {{ $packagingType->is_system ? 'Standard SAF type' : 'Custom packaging type' }}
            </p>
        </div>
    </div>
</div>
@endsection
