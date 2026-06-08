@extends('layouts.app')

@section('content')
<div class="erp-order-page erp-order-page--index screen-so-create">
    <header class="so-create__page-head">
        <div>
            <h1 class="so-create__page-title">Create sales order</h1>
            <p class="so-create__page-desc">Sell finished products to agents — saved as draft until confirmed.</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="so-create__back-btn">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to All sales orders
        </a>
    </header>

    <form action="{{ route('admin.orders.store') }}" method="POST" class="so-create">
        @csrf

        <section class="so-create__flow">
            <x-admin.order-workflow type="sales" :step="1" variant="procurement" />
        </section>

        @include('admin.orders.partials.create-form', [
            'agents' => $agents,
            'products' => $products,
            'availability' => $availability,
            'priceLists' => $priceLists,
        ])

        <x-admin.order-footer
            submit-label="Create sales order"
            :cancel-url="route('admin.orders.index')"
            hint="Order starts as draft until confirmed"
            data-tour="orders-primary-action"
        />
    </form>
</div>
@endsection
