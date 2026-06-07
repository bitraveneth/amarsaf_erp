@extends('layouts.app')

@section('content')
@php
    $deliveryStatusColors = [
        'scheduled' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
        'in_transit' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
        'delivered' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
        'exception' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
    ];
    $deliveryStatusColor = $deliveryStatusColors[$delivery->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="erp-page-title">Delivery #{{ $delivery->id }}</h1>
            <p class="erp-page-subtitle mt-1">
                Order #{{ $delivery->order_id }}
                · {{ $delivery->order?->agent?->name ?? 'No agent' }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.deliveries.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to deliveries
            </a>
            <x-admin.action-group>
                <x-admin.action-edit :href="route('admin.deliveries.edit', $delivery)" />
                <x-admin.action-delete
                    :action="route('admin.deliveries.destroy', $delivery)"
                    :confirm="'Delete delivery #' . $delivery->id . ' for order #' . $delivery->order_id . '? This action cannot be undone.'"
                />
            </x-admin.action-group>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Delivery status</p>
            <span class="mt-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $deliveryStatusColor }}">
                {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
            </span>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Route</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $delivery->route?->name ?? '—' }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Vehicle</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $delivery->vehicle?->name ?? '—' }}</p>
            @if($delivery->vehicle?->license_plate)
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $delivery->vehicle->license_plate }}</p>
            @endif
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">POD</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $delivery->pod_photo ? 'Uploaded' : 'Pending' }}
            </p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
        <h3 class="erp-h3">Documents</h3>
        <p class="erp-caption mt-1">Print or download delivery paperwork.</p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('admin.deliveries.packing-slip', $delivery) }}" target="_blank" rel="noopener" class="erp-btn-secondary">
                Packing slip
            </a>
            @if(Route::has('admin.documents.preview'))
                <a href="{{ route('admin.documents.preview', ['type' => 'delivery-challan', 'id' => $delivery->id]) }}" target="_blank" rel="noopener" class="erp-btn-secondary">
                    Delivery challan
                </a>
            @endif
            <a href="{{ route('admin.deliveries.pod-pdf', $delivery) }}" class="erp-btn-secondary">
                POD PDF
            </a>
        </div>
    </div>

    @if($delivery->items->isNotEmpty())
        <div class="erp-table-card">
            <div class="erp-table-wrap">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="is-right">Dispatched</th>
                            <th class="is-right">Delivered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($delivery->items as $item)
                            <tr>
                                <td>{{ $item->product?->name ?? '—' }}</td>
                                <td class="is-right"><span class="erp-table-num">{{ number_format((float) $item->qty_dispatched, 0) }}</span></td>
                                <td class="is-right"><span class="erp-table-num">{{ number_format((float) $item->qty_delivered, 0) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
