@extends('layouts.app')

@section('content')
<div class="erp-order-page">
    <x-admin.order-toolbar
        title="Create purchase order"
        subtitle="Procure raw materials, packaging and services from your suppliers."
        :back-url="route('admin.purchase-orders.index')"
        back-label="All purchase orders"
    />

    <form action="{{ route('admin.purchase-orders.store') }}" method="POST"
          x-data="purchaseOrderForm(@js($formState))"
          class="erp-order-form">
        @csrf

        <div class="erp-order-form__workflow">
            <x-admin.order-workflow type="purchase" :step="1" variant="hero" />
        </div>

        <div class="erp-order-form__body">
            @include('admin.purchase_orders.partials.form', ['suppliers' => $suppliers])
        </div>

        <x-admin.order-footer
            submit-label="Create purchase order"
            :cancel-url="route('admin.purchase-orders.index')"
            hint="Saved as draft — approve before creating GRN"
            data-tour="purchase-orders-primary-action"
        />
    </form>
</div>
@endsection
