@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
@endphp

<div class="erp-order-page erp-order-page--index screen-po-create">
    <header class="po-create__page-head">
        <div>
            <h1 class="po-create__page-title">Create purchase order</h1>
            <p class="po-create__page-desc">Procure materials and services — saved as draft until approved.</p>
        </div>
        <a href="{{ route('admin.purchase-orders.index') }}" class="po-create__back-btn">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to All purchase orders
        </a>
    </header>

    <form action="{{ route('admin.purchase-orders.store') }}" method="POST"
          x-data="purchaseOrderForm(@js($formState))"
          class="po-create">
        @csrf

        <section class="po-create__flow">
            <x-admin.order-workflow type="purchase" :step="1" variant="procurement" />
        </section>

        @include('admin.purchase_orders.partials.form', [
            'suppliers' => $suppliers,
            'currencyCode' => $currencyCode,
        ])

        <x-admin.order-footer
            submit-label="Create purchase order"
            :cancel-url="route('admin.purchase-orders.index')"
            hint="Saved as draft — approve before creating GRN"
            data-tour="purchase-orders-primary-action"
        />
    </form>
</div>
@endsection
