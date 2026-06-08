@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $statusFilter = $statusFilter ?? null;
    $stats = $stats ?? ['total' => 0, 'draft' => 0, 'awaiting_grn' => 0, 'received' => 0, 'pipeline_value' => 0];

    $poWorkflowStep = fn (string $status) => match ($status) {
        'draft' => 2,
        'approved' => 3,
        'partial_received' => 3,
        'received' => 4,
        default => 1,
    };

    $statusLabels = [
        'draft' => 'Draft',
        'approved' => 'Approved',
        'partial_received' => 'Partial GRN',
        'received' => 'Received',
    ];

    $statusTones = [
        'draft' => 'neutral',
        'approved' => 'brand',
        'partial_received' => 'warning',
        'received' => 'success',
    ];

    $filterTabs = [
        ['value' => null, 'label' => 'All POs'],
        ['value' => 'draft', 'label' => 'Draft'],
        ['value' => 'approved', 'label' => 'Approved'],
        ['value' => 'partial_received', 'label' => 'Partial GRN'],
        ['value' => 'received', 'label' => 'Received'],
    ];
@endphp

<div class="erp-order-page erp-order-page--index screen-purchase-orders">
    <x-admin.order-toolbar
        title="Purchase orders"
        subtitle="Procurement from draft through approval and goods receipt (GRN)."
        :back-url="route('admin.dashboard')"
        back-label="Dashboard"
    >
        <x-slot:actions>
            <a href="{{ route('admin.purchase-orders.create') }}"
               data-tour="purchase-orders-primary-action"
               class="erp-order-btn erp-order-btn--primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New purchase order
            </a>
        </x-slot:actions>
    </x-admin.order-toolbar>

    @include('layouts.partials.procurement-inbox-strip')

    <div class="erp-po-index-stats">
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Total POs</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($stats['total']) }}</p>
            <p class="erp-po-index-stat__hint">All purchase orders</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Draft</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--neutral">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($stats['draft']) }}</p>
            <p class="erp-po-index-stat__hint">Awaiting approval</p>
        </div>

        <a href="{{ route('admin.goods-receipts.index', ['tab' => 'awaiting']) }}" class="erp-po-index-stat erp-po-index-stat--link">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Awaiting GRN</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($stats['awaiting_grn']) }}</p>
            <p class="erp-po-index-stat__hint">Tap to open receive queue →</p>
        </a>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Pipeline value</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($stats['pipeline_value'], 0) }}</p>
            <p class="erp-po-index-stat__hint">Open PO value</p>
        </div>
    </div>

    <div class="erp-po-index-filters">
        @foreach($filterTabs as $tab)
            <a href="{{ route('admin.purchase-orders.index', $tab['value'] ? ['status' => $tab['value']] : []) }}"
               @class([
                   'erp-po-index-filter',
                   'is-active' => $statusFilter === $tab['value'],
               ])>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <div class="erp-order-list-card erp-po-index-table-card">
        <div class="erp-po-index-table-card__head">
            <div>
                <h2 class="erp-po-index-table-card__title">Purchase order register</h2>
                <p class="erp-po-index-table-card__desc">Track supplier POs from draft to goods receipt.</p>
            </div>
            @if($orders->total() > 0)
                <span class="erp-po-index-table-card__badge">
                    {{ number_format($orders->total()) }} {{ Str::plural('order', $orders->total()) }}
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="erp-order-list-table erp-po-index-table">
                <thead>
                    <tr>
                        <th>PO number</th>
                        <th>Supplier</th>
                        <th>Order date</th>
                        <th>Expected</th>
                        <th class="is-right">Est. total</th>
                        <th>Status</th>
                        <th class="min-w-[12rem]">Progress</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $canEdit = $order->status === 'draft' && ($order->goods_receipts_count ?? 0) === 0;
                            $step = $poWorkflowStep($order->status);
                            $inProgress = $order->status === 'partial_received';
                            $tone = $statusTones[$order->status] ?? 'neutral';
                            $supplierName = $order->supplier->name ?? '—';
                            $supplierInitial = strtoupper(substr(trim($supplierName), 0, 1)) ?: '—';
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.purchase-orders.show', $order) }}" class="erp-po-index-po-link">
                                    {{ $order->number }}
                                </a>
                                <p class="erp-po-index-po-meta">{{ $order->items->count() }} {{ Str::plural('line', $order->items->count()) }}</p>
                            </td>
                            <td>
                                <div class="erp-po-index-supplier">
                                    <span class="erp-po-index-supplier__avatar">{{ $supplierInitial }}</span>
                                    <span class="erp-po-index-supplier__name">{{ $supplierName }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">{{ optional($order->order_date)->format('d M Y') ?? '—' }}</td>
                            <td class="whitespace-nowrap">{{ optional($order->expected_date)->format('d M Y') ?? '—' }}</td>
                            <td class="is-right whitespace-nowrap">
                                <span class="erp-table-num font-semibold text-gray-900 dark:text-white">
                                    {{ $currencyCode }} {{ number_format((float) ($order->total_value ?? 0), 0) }}
                                </span>
                            </td>
                            <td>
                                <span class="erp-po-status erp-po-status--{{ $tone }}">
                                    {{ $statusLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="erp-po-index-progress">
                                    <x-admin.order-workflow type="purchase" :step="$step" :in-progress="$inProgress" variant="dash" />
                                </div>
                            </td>
                            <td class="text-right">
                                <x-admin.action-group>
                                    <x-admin.action-view :href="route('admin.purchase-orders.show', $order)" />
                                    @if($canEdit)
                                        <x-admin.action-edit :href="route('admin.purchase-orders.edit', $order)" />
                                        <x-admin.action-delete
                                            :action="route('admin.purchase-orders.destroy', $order)"
                                            confirm="Delete purchase order {{ $order->number }}?"
                                        />
                                    @endif
                                </x-admin.action-group>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="!px-6 !py-16 text-center">
                                <div class="erp-po-index-empty">
                                    <div class="erp-po-index-empty__icon" aria-hidden="true">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white">No purchase orders found</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        @if($statusFilter)
                                            No POs match this filter. Try another status or create a new order.
                                        @else
                                            Create your first purchase order to start procurement.
                                        @endif
                                    </p>
                                    <a href="{{ route('admin.purchase-orders.create') }}" class="erp-order-btn erp-order-btn--brand mt-5">
                                        Create purchase order
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
