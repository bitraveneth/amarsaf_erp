@extends('layouts.app')

@section('content')
@php
    $context = $context ?? (request()->routeIs('admin.materials.*') ? 'materials' : 'products');
    $isMaterials = $context === 'materials';
@endphp

<div class="mx-auto max-w-(--breakpoint-2xl) space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ $isMaterials ? 'Add Material' : 'Add Product' }}
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                @if($isMaterials)
                    Define raw materials, services, or in-house steps used in BOMs.
                @else
                    Capture SKU details, packaging linkage, and pricing.
                @endif
            </p>
        </div>
        <a href="{{ $isMaterials ? route('admin.materials.index') : route('admin.products.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ $isMaterials ? 'Back to Materials' : 'Back to Catalog' }}
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form
            action="{{ $isMaterials ? route('admin.materials.store') : route('admin.products.store') }}"
            method="POST"
            class="space-y-8 p-6"
            enctype="multipart/form-data"
        >
            @csrf

            @include('admin.products.partials.form', [
                'isMaterials' => $isMaterials,
                'packagingTypes' => $packagingTypes,
                'taxClasses' => $taxClasses,
                'materialCategories' => $materialCategories ?? collect(),
            ])

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <button
                    type="submit"
                    data-tour="product-form-submit"
                    class="inline-flex items-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $isMaterials ? 'Save Material' : 'Save Product' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
