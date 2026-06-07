@extends('layouts.app')

@section('content')
@php
    $poWorkflowStep = fn (string $status) => match ($status) {
        'draft' => 2,
        'approved' => 3,
        'partial_received' => 3,
        'received' => 4,
        default => 1,
    };
@endphp

<div class="erp-order-page">
    <x-admin.order-toolbar
        title="Purchase orders"
        subtitle="Raw materials, packaging and services — approve then receive via GRN."
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

    @if($orders->isNotEmpty())
        <div class="erp-order-show-grid !grid-cols-1 sm:!grid-cols-3">
            <div class="erp-order-stat">
                <p class="erp-order-stat__label">Total POs</p>
                <p class="erp-order-stat__value">{{ $orders->total() }}</p>
            </div>
            <div class="erp-order-stat">
                <p class="erp-order-stat__label">Draft</p>
                <p class="erp-order-stat__value">{{ $orders->getCollection()->where('status', 'draft')->count() }}</p>
            </div>
            <div class="erp-order-stat">
                <p class="erp-order-stat__label">Awaiting GRN</p>
                <p class="erp-order-stat__value">{{ $orders->getCollection()->whereIn('status', ['approved', 'partial_received'])->count() }}</p>
            </div>
        </div>
    @endif

    <div class="erp-order-list-card">
        <div class="overflow-x-auto">
            <table class="erp-order-list-table">
                <thead>
                    <tr>
                        <th>PO number</th>
                        <th>Supplier</th>
                        <th>Order date</th>
                        <th>Est. total</th>
                        <th class="min-w-[220px]">Progress</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $canEdit = $order->status === 'draft' && ($order->goods_receipts_count ?? 0) === 0;
                            $step = $poWorkflowStep($order->status);
                            $inProgress = $order->status === 'partial_received';
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-900/40">
                            <td>
                                <a href="{{ route('admin.purchase-orders.show', $order) }}" class="font-mono text-sm font-bold text-gray-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-400">
                                    {{ $order->number }}
                                </a>
                                <p class="text-xs capitalize text-gray-500">{{ str_replace('_', ' ', $order->status) }}</p>
                            </td>
                            <td>{{ $order->supplier->name ?? '—' }}</td>
                            <td>{{ optional($order->order_date)->format('d M Y') }}</td>
                            <td class="font-semibold text-gray-900 dark:text-white">BDT {{ number_format((float) ($order->total_value ?? 0), 2) }}</td>
                            <td>
                                <x-admin.order-workflow type="purchase" :step="$step" :in-progress="$inProgress" variant="mini" />
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
                            <td colspan="6" class="px-4 py-16 text-center">
                                <p class="text-sm text-gray-500">No purchase orders yet.</p>
                                <a href="{{ route('admin.purchase-orders.create') }}" class="erp-order-btn erp-order-btn--brand mt-4">
                                    Create your first PO
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
