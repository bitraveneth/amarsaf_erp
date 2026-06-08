@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $totalValue = $order->items->sum(fn ($item) => (float) ($item->line_total ?? 0));
    $orderedQty = $order->items->sum(fn ($item) => (float) $item->quantity);
    $receivedQty = $order->items->sum(fn ($item) => (float) $item->received_quantity);
    $receivedPercent = $orderedQty > 0 ? min(100, round(($receivedQty / $orderedQty) * 100, 1)) : 0;
    $canEdit = $order->status === 'draft' && $order->goodsReceipts->isEmpty();
    $workflowStep = match ($order->status) {
        'draft' => 2,
        'approved' => 3,
        'partial_received' => 3,
        'received' => 4,
        default => 1,
    };
    $grnInProgress = $order->status === 'partial_received';

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
    $statusTone = $statusTones[$order->status] ?? 'neutral';
    $statusLabel = $statusLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status));
    $supplier = $order->supplier;
    $supplierInitial = strtoupper(substr(trim($supplier->name ?? '—'), 0, 1)) ?: '—';
    $pendingGrns = $order->goodsReceipts->where('status', 'pending_approval')->sortByDesc('submitted_at');
    $hasOpenQty = $order->items->contains(fn ($i) => max((float) $i->quantity - (float) $i->received_quantity, 0) > 0);

    $guideSteps = match ($order->status) {
        'draft' => [
            ['label' => 'Approve this PO', 'hint' => 'Procurement locks the order for receiving.', 'state' => 'current', 'action_url' => '#po-approve', 'action_label' => 'Approve below'],
            ['label' => 'Receive goods (GRN)', 'hint' => 'Warehouse or procurement enters actual qty received.', 'state' => 'upcoming'],
            ['label' => 'Dual sign-off', 'hint' => 'Warehouse + procurement approve → stock posts.', 'state' => 'upcoming'],
        ],
        'approved', 'partial_received' => array_values(array_filter([
            ['label' => 'PO approved', 'hint' => 'Order is locked — quantities cannot be edited.', 'state' => 'complete'],
            $hasOpenQty
                ? ['label' => 'Receive goods', 'hint' => 'Inventory → GRN → Awaiting receipt, or use the button below.', 'state' => session('procurement_highlight') === 'receive' ? 'current' : ($pendingGrns->isNotEmpty() ? 'complete' : 'current'), 'action_url' => route('admin.purchase-orders.receive', $order), 'action_label' => 'Receive goods now']
                : null,
            $pendingGrns->isNotEmpty()
                ? ['label' => 'GRN waiting for sign-off', 'hint' => $pendingGrns->count() . ' GRN(s) need warehouse + procurement approval.', 'state' => 'current', 'action_url' => route('admin.goods-receipts.show', $pendingGrns->first()), 'action_label' => 'Open pending GRN']
                : ($hasOpenQty ? ['label' => 'Dual sign-off', 'hint' => 'After submit, both teams approve on the GRN page.', 'state' => 'upcoming'] : null),
            ! $hasOpenQty && $order->status === 'partial_received'
                ? ['label' => 'Receive remaining lines', 'hint' => 'Some lines may still have open quantity.', 'state' => 'current', 'action_url' => route('admin.purchase-orders.receive', $order), 'action_label' => 'Receive more']
                : null,
        ])),
        'received' => [
            ['label' => 'PO approved', 'state' => 'complete'],
            ['label' => 'Goods received', 'state' => 'complete'],
            ['label' => 'Stock posted', 'state' => 'complete'],
        ],
        default => [],
    };
@endphp

<div class="erp-order-page erp-order-page--index erp-po-show">
    <x-admin.order-toolbar
        :title="$order->number"
        :subtitle="'Purchase order · ' . ($supplier->name ?? '—')"
        :back-url="route('admin.purchase-orders.index')"
        back-label="All purchase orders"
    >
        <x-slot:actions>
            <span class="erp-po-status erp-po-status--{{ $statusTone }}">{{ $statusLabel }}</span>
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
                <form method="POST" action="{{ route('admin.purchase-orders.approve', $order) }}" id="po-approve">
                    @csrf
                    <button type="submit" class="erp-order-btn erp-order-btn--success">Approve PO</button>
                </form>
            @endif
            @if(in_array($order->status, ['approved', 'partial_received'], true))
                <a href="{{ route('admin.purchase-orders.receive', $order) }}"
                   class="erp-order-btn erp-order-btn--brand">Receive goods</a>
            @endif
        </x-slot:actions>
    </x-admin.order-toolbar>

    @if(session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->has('purchase_order'))
        <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
            {{ $errors->first('purchase_order') }}
        </div>
    @endif

    <x-admin.procurement-guide :steps="$guideSteps" class="mb-5" />

    <div class="erp-order-form erp-po-show__workflow">
        <div class="erp-order-form__workflow">
            <x-admin.order-workflow type="purchase" :step="$workflowStep" :in-progress="$grnInProgress" variant="hero" />
        </div>
    </div>

    <div class="erp-po-index-stats">
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">PO total</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($totalValue, 0) }}</p>
            <p class="erp-po-index-stat__hint">{{ $order->items->count() }} line {{ Str::plural('item', $order->items->count()) }}</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Order date</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--neutral">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value !text-xl">{{ optional($order->order_date)->format('d M Y') ?? '—' }}</p>
            <p class="erp-po-index-stat__hint">
                Expected {{ optional($order->expected_date)->format('d M Y') ?? '—' }}
            </p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Received</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($receivedPercent, $receivedPercent == floor($receivedPercent) ? 0 : 1) }}%</p>
            <p class="erp-po-index-stat__hint">{{ number_format($receivedQty, 2) }} of {{ number_format($orderedQty, 2) }} units</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Linked GRNs</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $order->goodsReceipts->count() }}</p>
            <p class="erp-po-index-stat__hint">Goods receipt notes</p>
        </div>
    </div>

    <div class="erp-po-show-layout">
        <div class="erp-po-show-main space-y-5">
            <div class="erp-order-list-card erp-po-index-table-card">
                <div class="erp-po-index-table-card__head">
                    <div>
                        <h2 class="erp-po-index-table-card__title">Line items</h2>
                        <p class="erp-po-index-table-card__desc">Materials and services on this purchase order.</p>
                    </div>
                    <span class="erp-po-index-table-card__badge">
                        {{ $order->items->count() }} {{ Str::plural('line', $order->items->count()) }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="erp-order-list-table erp-po-index-table erp-po-show-table">
                        <thead>
                            <tr>
                                <th>Material / service</th>
                                <th>Category</th>
                                <th class="is-right">Ordered</th>
                                <th class="is-right">Received</th>
                                <th class="is-right">Unit price</th>
                                <th class="is-right">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php
                                    $lineOrdered = (float) $item->quantity;
                                    $lineReceived = (float) $item->received_quantity;
                                    $linePercent = $lineOrdered > 0 ? min(100, round(($lineReceived / $lineOrdered) * 100)) : 0;
                                    $lineComplete = $lineOrdered > 0 && $lineReceived >= $lineOrdered;
                                @endphp
                                <tr>
                                    <td>
                                        @if($item->product)
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ $item->product->name }}</p>
                                            @if($item->product->sku)
                                                <p class="text-xs text-gray-500">{{ $item->product->sku }}</p>
                                            @endif
                                            @if($item->description && $item->description !== $item->product->name)
                                                <p class="mt-0.5 text-xs text-gray-500">{{ $item->description }}</p>
                                            @endif
                                        @else
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ $item->description }}</p>
                                            <p class="text-xs text-brand-600 dark:text-brand-400">Custom item</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap">
                                        {{ $item->product?->materialCategory?->name ?? ($item->product ? '—' : 'Custom') }}
                                    </td>
                                    <td class="is-right whitespace-nowrap">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($lineOrdered, 2) }}</span>
                                        <span class="ml-1 text-[10px] font-semibold uppercase text-gray-400">{{ $item->product?->uom ?? $item->uom ?? '' }}</span>
                                    </td>
                                    <td class="is-right">
                                        <div class="erp-po-show-receive">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($lineReceived, 2) }}</span>
                                            <div class="erp-po-show-receive__bar" aria-hidden="true">
                                                <span @class([
                                                    'erp-po-show-receive__fill',
                                                    'is-complete' => $lineComplete,
                                                    'is-partial' => ! $lineComplete && $lineReceived > 0,
                                                ]) style="width: {{ $linePercent }}%"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="is-right whitespace-nowrap">
                                        {{ $item->unit_price !== null ? $currencyCode . ' ' . number_format($item->unit_price, 2) : '—' }}
                                    </td>
                                    <td class="is-right whitespace-nowrap">
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ $item->line_total !== null ? $currencyCode . ' ' . number_format($item->line_total, 2) : '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="erp-po-show-table__total">
                                <td colspan="5" class="!px-5 !py-4 text-right text-sm font-semibold text-gray-600 dark:text-gray-300">PO total</td>
                                <td class="!px-5 !py-4 text-right">
                                    <span class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ $currencyCode }} {{ number_format($totalValue, 2) }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($order->notes)
                <div class="erp-po-show-panel">
                    <h3 class="erp-po-show-panel__title">Notes</h3>
                    <p class="erp-po-show-panel__body">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <aside class="erp-po-show-side space-y-5">
            <div class="erp-po-show-panel">
                <h3 class="erp-po-show-panel__title">Supplier</h3>
                <div class="erp-po-index-supplier mt-3">
                    <span class="erp-po-index-supplier__avatar !h-10 !w-10">{{ $supplierInitial }}</span>
                    <div class="min-w-0">
                        <p class="erp-po-index-supplier__name !text-base">{{ $supplier->name ?? '—' }}</p>
                        @if($supplier?->contact_person)
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $supplier->contact_person }}</p>
                        @endif
                    </div>
                </div>
                <dl class="erp-po-show-meta mt-4">
                    @if($supplier?->phone)
                        <div class="erp-po-show-meta__row">
                            <dt>Phone</dt>
                            <dd>{{ $supplier->phone }}</dd>
                        </div>
                    @endif
                    @if($supplier?->email)
                        <div class="erp-po-show-meta__row">
                            <dt>Email</dt>
                            <dd class="break-all">{{ $supplier->email }}</dd>
                        </div>
                    @endif
                    @if($supplier)
                        <div class="erp-po-show-meta__row">
                            <dt>Profile</dt>
                            <dd>
                                <a href="{{ route('admin.suppliers.show', $supplier) }}" class="text-brand-600 hover:underline dark:text-brand-400">
                                    View supplier
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="erp-po-show-panel">
                <h3 class="erp-po-show-panel__title">Receipt progress</h3>
                <div class="mt-4">
                    <div class="flex items-end justify-between gap-3">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($receivedPercent, 0) }}%</p>
                        <span class="erp-po-status erp-po-status--{{ $statusTone }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="erp-po-show-receive__bar mt-3 !h-2.5" aria-hidden="true">
                        <span @class([
                            'erp-po-show-receive__fill',
                            'is-complete' => $receivedPercent >= 100,
                            'is-partial' => $receivedPercent > 0 && $receivedPercent < 100,
                        ]) style="width: {{ $receivedPercent }}%"></span>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($receivedQty, 2) }} units received of {{ number_format($orderedQty, 2) }} ordered
                    </p>
                </div>
            </div>

            <div class="erp-po-show-panel">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="erp-po-show-panel__title">Linked GRNs</h3>
                    @if(in_array($order->status, ['approved', 'partial_received'], true))
                        <a href="{{ route('admin.purchase-orders.receive', $order) }}"
                           class="text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400">
                            + Receive goods
                        </a>
                    @endif
                </div>

                @if($order->goodsReceipts->isNotEmpty())
                    <ul class="erp-po-show-grn-list mt-3">
                        @foreach($order->goodsReceipts as $grn)
                            <li>
                                <a href="{{ route('admin.goods-receipts.show', $grn) }}" class="erp-po-show-grn-item">
                                    <span>
                                        <span class="erp-po-show-grn-item__number">{{ $grn->grn_number }}</span>
                                        <span class="erp-po-show-grn-item__date">
                                            {{ optional($grn->received_at)->format('d M Y') ?? '—' }}
                                            · {{ ucfirst(str_replace('_', ' ', $grn->status)) }}
                                            @if($grn->status === 'pending_approval')
                                                (WH {{ $grn->warehouse_approved_at ? '✓' : '—' }} / Proc {{ $grn->procurement_approved_at ? '✓' : '—' }})
                                            @endif
                                        </span>
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        No goods receipts yet.
                        @if($order->status === 'draft')
                            Approve this PO first, then create a GRN.
                        @elseif(in_array($order->status, ['approved', 'partial_received'], true))
                            Use <strong>Receive goods</strong> when stock arrives.
                        @endif
                    </p>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection
