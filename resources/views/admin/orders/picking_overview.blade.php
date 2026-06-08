@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $totalOrders = $orders instanceof \Illuminate\Pagination\LengthAwarePaginator ? $orders->total() : $orders->count();
    $toPick = $orders->where('status', 'confirmed')->count();
    $toPack = $orders->where('status', 'picked')->count();
    $totalValue = $orders->sum('total');
@endphp

<div class="erp-order-page erp-order-page--index">
    <x-admin.order-toolbar
        title="Picking lists"
        subtitle="Warehouse queue — confirmed orders to pick, picked orders awaiting pack."
        :back-url="route('admin.orders.index')"
        back-label="Sales orders"
    />

    @include('layouts.partials.fulfillment-inbox-strip')

    @if($orders->isNotEmpty())
        <div class="erp-po-index-stats">
            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Queue</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ number_format($totalOrders) }}</p>
                <p class="erp-po-index-stat__hint">{{ $toPick }} to pick · {{ $toPack }} to pack</p>
            </div>
            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Order value</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($totalValue, 0) }}</p>
                <p class="erp-po-index-stat__hint">On this page</p>
            </div>
        </div>

        <div class="erp-order-list-card erp-po-index-table-card">
            <div class="erp-po-index-table-card__head">
                <div>
                    <h2 class="erp-po-index-table-card__title">Warehouse queue</h2>
                    <p class="erp-po-index-table-card__desc">Open a picking list to pull stock and confirm quantities.</p>
                </div>
                <span class="erp-po-index-table-card__badge">{{ $orders->count() }} of {{ number_format($totalOrders) }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="erp-order-list-table erp-po-index-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Agent</th>
                            <th>Delivery</th>
                            <th>Status</th>
                            <th class="is-right">Fulfillment</th>
                            <th class="is-right">Total</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            @php
                                $metrics = \App\Services\Sales\SalesFulfillmentMetrics::summarize($order);
                                $tone = $order->status === 'picked' ? 'warning' : 'success';
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-gray-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-400">
                                        #{{ $order->id }}
                                    </a>
                                    @if($order->agent_reference)
                                        <p class="text-xs text-gray-500">Ref: {{ $order->agent_reference }}</p>
                                    @endif
                                </td>
                                <td>{{ $order->agent->name ?? '—' }}</td>
                                <td class="whitespace-nowrap">
                                    {{ optional($order->delivery_date)->format('d M Y') ?? 'TBD' }}
                                </td>
                                <td>
                                    <span class="erp-po-status erp-po-status--{{ $tone }}">{{ ucfirst($order->status) }}</span>
                                </td>
                                <td class="is-right">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($metrics['fulfillment_percent'], 0) }}%</span>
                                    <p class="text-xs text-gray-500">Picked {{ number_format($metrics['picked_qty'], 0) }}/{{ number_format($metrics['ordered_qty'], 0) }}</p>
                                </td>
                                <td class="is-right whitespace-nowrap">
                                    {{ $currencyCode }} {{ number_format($order->total, 2) }}
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.orders.picking-list', $order) }}" class="erp-order-btn erp-order-btn--brand !inline-flex !px-3 !py-1.5 !text-xs">
                                        {{ $order->status === 'picked' ? 'View order' : 'Picking list' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($orders, 'links'))
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="erp-po-show-panel text-center py-12">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">No pending picks</h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">All confirmed orders are picked or there is nothing in the warehouse queue.</p>
            <a href="{{ route('admin.orders.index') }}" class="erp-order-btn erp-order-btn--secondary mt-6 inline-flex">View sales orders</a>
        </div>
    @endif
</div>
@endsection
