@extends('layouts.app')

@section('content')
@php
    $totalValue = $order->items->sum(fn ($item) => (float) ($item->line_total ?? 0));
    $canEdit = $order->status === 'draft' && $order->goodsReceipts->isEmpty();
    $workflowStep = match ($order->status) {
        'draft' => 2,
        'approved' => 3,
        'partial_received' => 3,
        'received' => 4,
        default => 1,
    };
    $grnInProgress = $order->status === 'partial_received';
@endphp

<div class="erp-order-page">
    <x-admin.order-toolbar
        :title="$order->number"
        :subtitle="($order->supplier->name ?? '—') . ' · Ordered ' . optional($order->order_date)->format('d M Y') . ($order->expected_date ? ' · Expected ' . $order->expected_date->format('d M Y') : '')"
        :back-url="route('admin.purchase-orders.index')"
        back-label="All purchase orders"
    >
        <x-slot:actions>
            @if($canEdit)
                <x-admin.action-group>
                    <x-admin.action-edit :href="route('admin.purchase-orders.edit', $order)" />
                    <x-admin.action-delete
                        :action="route('admin.purchase-orders.destroy', $order)"
                        confirm="Delete purchase order {{ $order->number }}?"
                    />
                </x-admin.action-group>
            @endif
            <x-admin.document-actions type="purchase-order" :id="$order->id" compact />
            @if($order->status === 'draft')
                <form method="POST" action="{{ route('admin.purchase-orders.approve', $order) }}">
                    @csrf
                    <button type="submit" class="erp-order-btn erp-order-btn--success">Approve PO</button>
                </form>
            @endif
            @if(in_array($order->status, ['approved', 'partial_received'], true))
                <a href="{{ route('admin.goods-receipts.create', ['purchase_order_id' => $order->id]) }}"
                   class="erp-order-btn erp-order-btn--brand">Create GRN</a>
            @endif
        </x-slot:actions>
    </x-admin.order-toolbar>

    <div class="erp-order-form">
        <div class="erp-order-form__workflow">
            <x-admin.order-workflow type="purchase" :step="$workflowStep" :in-progress="$grnInProgress" variant="hero" />
        </div>
    </div>

    <div class="erp-order-show-grid">
        <div class="erp-order-stat">
            <p class="erp-order-stat__label">Supplier</p>
            <p class="erp-order-stat__value !text-lg">{{ $order->supplier->name ?? '—' }}</p>
            @if($order->supplier?->phone)
                <p class="mt-1 text-xs text-gray-500">{{ $order->supplier->phone }}</p>
            @endif
        </div>
        <div class="erp-order-stat">
            <p class="erp-order-stat__label">Line items</p>
            <p class="erp-order-stat__value">{{ $order->items->count() }}</p>
        </div>
        <div class="erp-order-stat">
            <p class="erp-order-stat__label">PO total</p>
            <p class="erp-order-stat__value text-brand-600 dark:text-brand-400">BDT {{ number_format($totalValue, 2) }}</p>
        </div>
        <div class="erp-order-stat">
            <p class="erp-order-stat__label">Linked GRNs</p>
            <p class="erp-order-stat__value">{{ $order->goodsReceipts->count() }}</p>
        </div>
    </div>

    @if($order->notes)
        <div class="erp-order-panel">
            <p class="erp-order-stat__label">Notes</p>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $order->notes }}</p>
        </div>
    @endif

    <div class="erp-order-list-card">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <h2 class="erp-order-panel__title">Line items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-order-list-table">
                <thead>
                    <tr>
                        <th>Material / service</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-right">Ordered</th>
                        <th class="text-right">Received</th>
                        <th class="text-right">Unit price</th>
                        <th class="text-right">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/40">
                            <td>
                                @if($item->product)
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $item->product->name }}</p>
                                    @if($item->product->sku)
                                        <p class="text-xs text-gray-500">{{ $item->product->sku }}</p>
                                    @endif
                                @else
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $item->description }}</p>
                                    <p class="text-xs text-brand-600 dark:text-brand-400">Custom item</p>
                                @endif
                            </td>
                            <td>{{ $item->product?->materialCategory?->name ?? ($item->product ? '—' : 'Custom') }}</td>
                            <td>{{ $item->product ? $item->description : '—' }}</td>
                            <td class="text-right font-medium text-gray-900 dark:text-white">
                                {{ number_format($item->quantity, 2) }}
                                <span class="ml-1 text-xs uppercase text-gray-500">{{ $item->product?->uom ?? $item->uom ?? '' }}</span>
                            </td>
                            <td class="text-right">{{ number_format($item->received_quantity, 2) }}</td>
                            <td class="text-right">{{ $item->unit_price !== null ? 'BDT ' . number_format($item->unit_price, 2) : '—' }}</td>
                            <td class="text-right font-semibold">{{ $item->line_total !== null ? 'BDT ' . number_format($item->line_total, 2) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-900/30">
                    <tr>
                        <td colspan="6" class="px-5 py-4 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Total</td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-lg font-bold text-brand-600 dark:text-brand-400">BDT {{ number_format($totalValue, 2) }}</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($order->goodsReceipts->isNotEmpty())
        <div class="erp-order-list-card">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="erp-order-panel__title">Linked GRNs</h2>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($order->goodsReceipts as $grn)
                    <li class="flex items-center justify-between px-5 py-3">
                        <a href="{{ route('admin.goods-receipts.show', $grn) }}" class="font-mono text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                            {{ $grn->number ?? 'GRN #' . $grn->id }}
                        </a>
                        <span class="text-xs text-gray-500">{{ optional($grn->receipt_date)->format('d M Y') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
