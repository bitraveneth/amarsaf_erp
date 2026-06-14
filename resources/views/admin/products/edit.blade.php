@extends('layouts.app')

@section('content')
@php
    $context = $context ?? (request()->routeIs('admin.materials.*') ? 'materials' : 'products');
    $isMaterials = $context === 'materials';
@endphp

<div class="dash-page pb-4">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1 class="erp-dash-h1">{{ $isMaterials ? 'Edit material' : 'Edit product' }}</h1>
            @if($isMaterials)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update type, UOM, and costing.</p>
            @endif
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            @if(! $isMaterials)
                <a href="{{ route('admin.products.show', $product) }}" class="erp-btn-secondary">View</a>
                <a href="{{ route('admin.products.prices.show', $product) }}" class="erp-btn-secondary">Prices</a>
            @endif
            <a href="{{ $isMaterials ? route('admin.materials.index') : route('admin.products.index') }}" class="erp-btn-secondary">
                Back
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-error-200 bg-error-50 px-4 py-2.5 text-sm text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
            Please fix the highlighted fields.
        </div>
    @endif

    <form action="{{ $isMaterials ? route('admin.materials.update', $product) : route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        @include('admin.products.partials.form', [
            'isMaterials' => $isMaterials,
            'product' => $product,
            'packagingTypes' => $packagingTypes,
            'taxClasses' => $taxClasses,
            'units' => $units ?? collect(),
            'materialCategories' => $materialCategories ?? collect(),
            'groupedCategories' => $groupedCategories ?? collect(),
            'productNameOptions' => $productNameOptions ?? null,
        ])

        @if($isMaterials)
            <div class="mt-6 flex justify-end gap-2 border-t border-gray-200 pt-4 dark:border-gray-800">
                <a href="{{ route('admin.materials.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Save changes</button>
            </div>
        @else
            <x-admin.sticky-form-footer class="mt-4">
                <a href="{{ route('admin.products.show', $product) }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Save changes</button>
            </x-admin.sticky-form-footer>
        @endif
    </form>
</div>
@endsection
