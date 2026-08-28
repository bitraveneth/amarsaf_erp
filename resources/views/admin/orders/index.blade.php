@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $typeFilter = $orderTypeFilter ?? 'all';

    $filterTabs = [
        ['value' => 'all', 'label' => 'All orders'],
        ['value' => 'sales', 'label' => 'Sales orders'],
        ['value' => 'return', 'label' => 'Return orders'],
    ];

    $totalOrders = $orders instanceof \Illuminate\Pagination\LengthAwarePaginator ? $orders->total() : $orders->count();
    $onPageCount = $orders->count();
    $totalRevenue = $orders->sum('total');
    $totalCommission = $orders->sum('commission_total');
    $pendingOrders = $orders->where('status', 'pending')->count();
    $processingOrders = $orders->where('status', 'processing')->count();

    $statusColors = [
        'draft' => 'neutral',
        'pending' => 'warning',
        'confirmed' => 'success',
        'picked' => 'brand',
        'packed' => 'brand',
        'dispatched' => 'brand',
        'processing' => 'brand',
        'shipped' => 'brand',
        'delivered' => 'success',
        'cancelled' => 'neutral',
    ];

    $statusLabels = [
        'draft' => 'Draft',
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'picked' => 'Picked',
        'packed' => 'Packed',
        'dispatched' => 'Dispatched',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];
@endphp

<div class="erp-order-page erp-order-page--index screen-sales-orders">
    <x-admin.order-toolbar
        title="Sales orders"
        subtitle="Track agent orders from draft through delivery."
        :back-url="route('admin.dashboard')"
        back-label="Dashboard"
    >
        <x-slot:actions>
            <a href="{{ route('admin.orders.create') }}"
               data-tour="orders-primary-action"
               class="erp-order-btn erp-order-btn--primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New sales order
            </a>
        </x-slot:actions>
    </x-admin.order-toolbar>

    @include('layouts.partials.fulfillment-inbox-strip')

    @if(session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if($orders->isNotEmpty())
        <div class="erp-po-index-stats">
            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Total orders</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ number_format($totalOrders) }}</p>
                <p class="erp-po-index-stat__hint">{{ number_format($onPageCount) }} on this page · {{ number_format($totalOrders) }} total</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Revenue</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value {{ $totalRevenue < 0 ? 'erp-po-index-stat__value--negative' : '' }}">{{ $currencyCode }} {{ number_format($totalRevenue, 0) }}</p>
                <p class="erp-po-index-stat__hint">Current page total</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Commission</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($totalCommission, 0) }}</p>
                <p class="erp-po-index-stat__hint">Agent commission</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">In progress</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ $pendingOrders + $processingOrders }}</p>
                <p class="erp-po-index-stat__hint">{{ $pendingOrders }} pending · {{ $processingOrders }} processing</p>
            </div>
        </div>
    @endif

    <div class="erp-po-index-filters">
        @foreach($filterTabs as $tab)
            <a href="{{ route('admin.orders.index', ['type' => $tab['value']]) }}"
               @class([
                   'erp-po-index-filter',
                   'is-active' => $typeFilter === $tab['value'],
               ])>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <div class="erp-order-list-card erp-po-index-table-card">
        <div class="erp-po-index-table-card__head">
            <div>
                <h2 class="erp-po-index-table-card__title">
                    {{ $typeFilter === 'sales' ? 'Sales order register' : ($typeFilter === 'return' ? 'Return order register' : 'All orders') }}
                </h2>
                <p class="erp-po-index-table-card__desc">Agent orders from draft through fulfillment and delivery.</p>
            </div>
            @if($totalOrders > 0)
                <span class="erp-po-index-table-card__badge">
                    {{ $orders->count() }} of {{ number_format($totalOrders) }}
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="erp-order-list-table erp-so-index-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Type</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th class="is-right">Total</th>
                        <th class="is-right">Commission</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $tone = $statusColors[$order->status] ?? 'neutral';
                            $agentName = $order->agent->name ?? '—';
                            $agentInitial = strtoupper(substr(trim($agentName), 0, 1)) ?: '?';
                        @endphp
                        <tr>
                            <td>
                                <div class="erp-po-index-supplier">
                                    <span class="erp-po-index-supplier__avatar">{{ $agentInitial }}</span>
                                    <div>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="erp-po-index-supplier__name hover:text-brand-600 dark:hover:text-brand-400">
                                            {{ $agentName }}
                                        </a>
                                        <p class="erp-po-index-po-meta">#{{ $order->id }} · {{ $order->agent->zone ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span @class([
                                    'erp-po-status',
                                    'erp-po-status--neutral' => $order->order_type !== 'return',
                                    'erp-po-status--warning' => $order->order_type === 'return',
                                ])>
                                    {{ $order->order_type === 'return' ? 'Return' : ucfirst($order->order_type) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap">
                                @if($order->delivery_date)
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $order->delivery_date->format('d M Y') }}</span>
                                    <p class="erp-po-index-po-meta">{{ $order->delivery_date->diffForHumans() }}</p>
                                @else
                                    <span class="text-gray-400">TBD</span>
                                @endif
                            </td>
                            <td>
                                <span class="erp-po-status erp-po-status--{{ $tone }}">
                                    {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="is-right whitespace-nowrap">
                                <span @class([
                                    'erp-table-num font-semibold',
                                    'text-error-600 dark:text-error-400' => $order->order_type === 'return',
                                    'text-gray-900 dark:text-white' => $order->order_type !== 'return',
                                ])>
                                    {{ $currencyCode }} {{ number_format($order->total, 2) }}
                                </span>
                            </td>
                            <td class="is-right whitespace-nowrap">
                                <span class="erp-table-num font-semibold text-success-600 dark:text-success-400">
                                    {{ $currencyCode }} {{ number_format($order->commission_total ?? 0, 2) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <x-admin.action-group>
                                    <x-admin.action-view :href="route('admin.orders.show', $order)" />
                                    <x-admin.action-edit :href="route('admin.orders.edit', $order)" />
                                    <x-admin.action-delete
                                        :action="route('admin.orders.destroy', $order)"
                                        :confirm="'Delete order #'.$order->id.'?'"
                                    />
                                </x-admin.action-group>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="!px-6 !py-16 text-center">
                                <div class="erp-po-index-empty">
                                    <div class="erp-po-index-empty__icon" aria-hidden="true">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white">No orders yet</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Create your first sales order to start tracking agent fulfillment.
                                    </p>
                                    <a href="{{ route('admin.orders.create') }}" class="erp-order-btn erp-order-btn--brand mt-5">
                                        New sales order
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($orders, 'hasPages') && $orders->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
