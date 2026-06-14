@extends('layouts.app')

@section('content')
<div class="erp-order-page erp-order-page--index screen-bom-edit">
    <header class="po-create__page-head">
        <div>
            <h1 class="po-create__page-title">Edit recipe setup</h1>
            <p class="po-create__page-desc">
                {{ $bom->product?->sku ? $bom->product->sku . ' – ' : '' }}{{ $bom->product?->name ?? 'Recipe' }}
                · updated {{ $bom->updated_at?->diffForHumans() }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.boms.show', $bom) }}" class="po-create__back-btn">
                View recipe
            </a>
            <a href="{{ route('admin.boms.index') }}" class="po-create__back-btn">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                All BOMs
            </a>
        </div>
    </header>

    <form action="{{ route('admin.boms.update', $bom) }}" method="POST"
          x-data="bomForm(@js($formState))"
          class="po-create">
        @csrf
        @method('PATCH')

        <section class="po-create__flow">
            <x-admin.order-workflow
                type="bom"
                :step="$bom->recipeWorkflowStep()"
                :in-progress="$bom->recipeWorkflowInProgress()"
                variant="procurement"
            />
        </section>

        @include('admin.boms.partials.form', ['bom' => $bom])

        <x-admin.order-footer
            submit-label="Save changes"
            :cancel-url="route('admin.boms.show', $bom)"
            hint="Only one active recipe per product is allowed"
        />
    </form>

    <div class="mt-6 overflow-hidden rounded-2xl border border-error-200 bg-white shadow-theme-sm dark:border-error-800/30 dark:bg-gray-900">
        <div class="border-b border-error-100 px-6 py-4 dark:border-error-800/20">
            <h3 class="text-base font-semibold text-error-700 dark:text-error-400">Delete recipe</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Removes this BOM and all component lines permanently.</p>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4">
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $bom->displayName() }}</p>
            <form action="{{ route('admin.boms.destroy', $bom) }}" method="POST"
                  onsubmit="return confirm('Delete this BOM? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-error-600 focus:outline-none focus:ring-2 focus:ring-error-500/50">
                    Delete BOM
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
