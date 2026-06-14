@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
@endphp

<div class="erp-order-page erp-order-page--index screen-logistics-bill-create">
    <header class="po-create__page-head">
        <div>
            <h1 class="po-create__page-title">New logistics bill</h1>
            <p class="po-create__page-desc">Record a carrier invoice for hired truck, courier, or freight.</p>
        </div>
        <a href="{{ route('admin.logistics-bills.index') }}" class="po-create__back-btn">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to all logistics bills
        </a>
    </header>

    @if($carriers->isEmpty())
        <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            No transport carriers yet.
            <a href="{{ route('admin.logistics.carriers.create') }}" class="font-semibold underline">Add a carrier</a>
            first, then record the bill.
        </div>
    @endif

    @if(!empty($suggestion))
        <div class="mb-5 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-900 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-200">
            <strong>Suggested freight</strong> from rate card: {{ $suggestion['description'] }} — {{ $currencyCode }} {{ number_format($suggestion['amount'], 2) }}.
        </div>
    @endif

    <form action="{{ route('admin.logistics-bills.store') }}" method="POST"
          x-data="logisticsBillForm(@js($formState))"
          class="po-create">
        @csrf
        <input type="hidden" name="status" value="open">

        @include('admin.logistics.bills.partials.form', [
            'currencyCode' => $currencyCode,
            'serviceTypes' => $serviceTypes,
        ])

        <x-admin.order-footer
            submit-label="Save logistics bill"
            :cancel-url="route('admin.logistics-bills.index')"
            hint="Posts expense and carrier payable to the ledger"
        />
    </form>
</div>
@endsection
