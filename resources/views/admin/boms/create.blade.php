@extends('layouts.app')

@section('content')
<div class="erp-order-page erp-order-page--index screen-bom-create">
    <header class="po-create__page-head">
        <div>
            <h1 class="po-create__page-title">New recipe setup</h1>
            <p class="po-create__page-desc">Define the BOM once — production runs reuse it automatically.</p>
        </div>
        <a href="{{ route('admin.boms.index') }}" class="po-create__back-btn">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to all BOMs
        </a>
    </header>

    @if($products->isEmpty())
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3.5 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            No finished products found.
            <a href="{{ route('admin.products.create') }}" class="font-semibold underline">Add a finished product</a>
            first, then create the recipe.
        </div>
    @endif

    @if($materials->isEmpty())
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3.5 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            No raw materials or services yet.
            <a href="{{ route('admin.products.create') }}" class="font-semibold underline">Add materials</a>
            before building a BOM.
        </div>
    @endif

    <form action="{{ route('admin.boms.store') }}" method="POST"
          x-data="bomForm(@js($formState))"
          class="po-create">
        @csrf

        <section class="po-create__flow">
            <x-admin.order-workflow type="bom" :step="1" variant="procurement" />
        </section>

        @include('admin.boms.partials.form')

        <x-admin.order-footer
            submit-label="Save & activate"
            :cancel-url="route('admin.boms.index')"
            hint="Active recipe is used for all future production — you do not create a BOM each run"
            data-tour="boms-primary-action"
        >
            <x-slot:actions>
                <button type="submit" name="save_mode" value="draft" class="erp-order-btn erp-order-btn--secondary">
                    Save as draft
                </button>
            </x-slot:actions>
        </x-admin.order-footer>
    </form>
</div>
@endsection
