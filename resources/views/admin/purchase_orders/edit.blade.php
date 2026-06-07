@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-(--breakpoint-2xl) space-y-4">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Edit purchase order</h1>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $order->number }} · draft only</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.purchase-orders.show', $order) }}"
               class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                View
            </a>
            <a href="{{ route('admin.purchase-orders.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                ← Back
            </a>
        </div>
    </div>

    <form action="{{ route('admin.purchase-orders.update', $order) }}" method="POST"
          x-data="purchaseOrderForm(@js($formState))"
          class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        @csrf
        @method('PATCH')

        <div class="p-4">
            @include('admin.purchase_orders.partials.form', [
                'order' => $order,
                'suppliers' => $suppliers,
            ])
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-4 py-3 dark:border-gray-800">
            <a href="{{ route('admin.purchase-orders.show', $order) }}"
               class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">
                Cancel
            </a>
            <button type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-600">
                Save changes
            </button>
        </div>
    </form>
</div>
@endsection
