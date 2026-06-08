@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
@endphp

<div class="erp-order-page erp-order-page--index screen-po-create">
    <header class="po-create__page-head">
        <div>
            <h1 class="po-create__page-title">Edit {{ $order->number }}</h1>
            <p class="po-create__page-desc">Update draft purchase order before approval.</p>
        </div>
        <a href="{{ route('admin.purchase-orders.show', $order) }}" class="po-create__back-btn">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to {{ $order->number }}
        </a>
    </header>

    <form action="{{ route('admin.purchase-orders.update', $order) }}" method="POST"
          x-data="purchaseOrderForm(@js($formState))"
          class="po-create">
        @csrf
        @method('PATCH')

        <section class="po-create__flow">
            <x-admin.order-workflow type="purchase" :step="1" variant="procurement" />
        </section>

        @include('admin.purchase_orders.partials.form', [
            'order' => $order,
            'suppliers' => $suppliers,
            'currencyCode' => $currencyCode,
        ])

        <x-admin.order-footer
            submit-label="Save changes"
            :cancel-url="route('admin.purchase-orders.show', $order)"
            hint="Only draft POs without receipts can be edited"
        />
    </form>
</div>
@endsection
