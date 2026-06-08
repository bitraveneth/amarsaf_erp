@extends('layouts.app')

@section('content')
@php
    $isReturnOrder = ($order->order_type ?? null) === 'return';
    $currencyCode = config('app.currency', 'BDT');
    $totalValue = (float) $order->total;
    $orderedQty = $order->items->sum(fn ($item) => (float) $item->quantity);
    $agent = $order->agent;
    $agentInitial = strtoupper(substr(trim($agent->name ?? '—'), 0, 1)) ?: '—';
    $primaryDelivery = $order->deliveries->sortByDesc('created_at')->first();
    $canEdit = $order->status === 'draft' && ! $isReturnOrder;

    $guide = $fulfillment ?? [];
    $guideSteps = $guide['guide_steps'] ?? [];
    $fulfillmentPercent = $guide['fulfillment_percent'] ?? 0;
    $lineFulfillmentPercent = $guide['line_fulfillment_percent'] ?? 0;
    $salesWorkflowStep = $guide['workflow_step'] ?? 1;
    $salesInProgress = $guide['in_progress'] ?? false;
    $showYourAction = $guide['show_your_action'] ?? false;
    $canConfirm = $guide['can_confirm'] ?? false;
    $canPick = $guide['can_pick'] ?? false;
    $canPack = $guide['can_pack'] ?? false;
    $canDispatch = $guide['can_dispatch'] ?? false;
    $statusLabel = $guide['status_label'] ?? ucfirst($order->status);
    $statusTone = $guide['status_tone'] ?? 'neutral';
    $pickedAt = $guide['picked_at'] ?? null;
    $packedAt = $guide['packed_at'] ?? null;
    $dispatchedAt = $guide['dispatched_at'] ?? null;
    $deliveredAt = $guide['delivered_at'] ?? null;
    $metrics = $guide['metrics'] ?? null;
    $pickedQty = $metrics['picked_qty'] ?? 0;
    $packedQty = $metrics['packed_qty'] ?? 0;
    $deliveredQty = $metrics['delivered_qty'] ?? 0;
    $pickedPercent = $metrics['picked_percent'] ?? 0;
    $packedPercent = $metrics['packed_percent'] ?? 0;
    $deliveredPercent = $metrics['delivered_percent'] ?? 0;

    $confirmDone = ! in_array($order->status, ['draft'], true);
    $pickDone = in_array($order->status, ['picked', 'packed', 'dispatched', 'delivered'], true);
    $packDone = in_array($order->status, ['packed', 'dispatched', 'delivered'], true);
    $deliveryDone = $order->status === 'delivered';

    $salesPhaseCurrent = match ($order->status) {
        'draft' => 'confirm',
        'confirmed' => 'pick',
        'picked' => 'pack',
        'packed', 'dispatched' => 'delivery',
        default => null,
    };
@endphp

<div class="erp-order-page erp-order-page--index erp-po-show screen-order-view">
    <x-admin.order-toolbar
        :title="($isReturnOrder ? 'Return order' : 'Sales order') . ' #' . $order->id"
        :subtitle="$agent->name . ' · ' . ucfirst($order->order_type ?? 'regular')"
        :back-url="route('admin.orders.index')"
        back-label="All sales orders"
    >
        <x-slot:actions>
            @unless($isReturnOrder)
                <span class="erp-po-status erp-po-status--{{ $statusTone }}">{{ $statusLabel }}</span>
            @endunless
            @if($canEdit)
                <x-admin.action-group>
                    <x-admin.action-edit :href="route('admin.orders.edit', $order)" />
                    <x-admin.action-delete
                        :action="route('admin.orders.destroy', $order)"
                        :confirm="'Delete sales order #' . $order->id . '?'"
                    />
                </x-admin.action-group>
            @endif
            <x-admin.document-actions type="sales-order" :id="$order->id" compact />
            @if($isReturnOrder)
                <a href="{{ route('admin.returns.customer.create') }}?order_id={{ $order->id }}" class="erp-order-btn erp-order-btn--secondary">Record return</a>
            @else
                @if($order->status === 'draft' && $canConfirm)
                    <form method="POST" action="{{ route('admin.orders.status.update', $order) }}" id="so-confirm">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="erp-order-btn erp-order-btn--success">Confirm order</button>
                    </form>
                @endif
                @if($order->status === 'confirmed')
                    <a href="{{ route('admin.orders.picking-list', $order) }}" class="erp-order-btn erp-order-btn--brand">Open picking list</a>
                @endif
                @if($order->status === 'picked' && $canPack)
                    <form method="POST" action="{{ route('admin.orders.status.update', $order) }}" id="so-pack">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="packed">
                        <button type="submit" class="erp-order-btn erp-order-btn--success">Confirm packing</button>
                    </form>
                @endif
                @if($order->status === 'packed')
                    @if($primaryDelivery)
                        <a href="{{ route('admin.deliveries.show', $primaryDelivery) }}" class="erp-order-btn erp-order-btn--brand">Open delivery</a>
                    @elseif($canDispatch)
                        <a href="{{ route('admin.deliveries.create') }}" class="erp-order-btn erp-order-btn--brand">Schedule delivery</a>
                    @endif
                @endif
                @if($order->status === 'dispatched' && $primaryDelivery)
                    <a href="{{ route('admin.deliveries.show', $primaryDelivery) }}" class="erp-order-btn erp-order-btn--brand">Complete POD</a>
                @endif
                @if($order->status === 'delivered' && ! $order->invoice)
                    <form action="{{ route('admin.orders.invoice', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="erp-order-btn erp-order-btn--success">Create invoice</button>
                    </form>
                @endif
            @endif
        </x-slot:actions>
    </x-admin.order-toolbar>

    @include('layouts.partials.fulfillment-inbox-strip')

    @if(session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
            {{ session('error') }}
        </div>
    @endif

    @if($isReturnOrder)
        <div class="mb-5 rounded-2xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-800 dark:border-error-900/40 dark:bg-error-950/20 dark:text-error-200">
            This is a commercial return order. It reduces sales value, but it does not automatically add stock back. Use
            <a href="{{ route('admin.returns.customer.create') }}?order_id={{ $order->id }}" class="font-semibold underline underline-offset-2 hover:no-underline">
                Record stock return
            </a>
            after the returned goods are physically received into inventory.
        </div>
    @else
        @if(count($guideSteps) > 0)
            <x-admin.procurement-guide title="Fulfillment progress" :steps="$guideSteps" class="mb-5" />
        @endif

        @if($showYourAction)
            <section class="erp-grn-approve mb-5">
                <h2 class="erp-grn-approve__title">Your action</h2>
                <div class="erp-grn-approve__grid lg:grid-cols-4">
                    <div @class([
                        'erp-grn-approve__card',
                        'is-done' => $confirmDone,
                        'is-mine' => $salesPhaseCurrent === 'confirm' && $canConfirm,
                    ])>
                        <p class="erp-grn-approve__role">Sales</p>
                        @if($confirmDone)
                            <p class="erp-grn-approve__status">Order confirmed</p>
                            <p class="text-xs text-gray-500">Stock reserved for this order</p>
                        @elseif($canConfirm)
                            <p class="erp-grn-approve__prompt">Confirm the order so warehouse can pick stock.</p>
                            <form action="{{ route('admin.orders.status.update', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="erp-grn-approve__btn erp-grn-approve__btn--sales">Confirm order</button>
                            </form>
                        @else
                            <p class="erp-grn-approve__waiting">Waiting for sales team</p>
                        @endif
                    </div>

                    <div @class([
                        'erp-grn-approve__card',
                        'is-done' => $pickDone,
                        'is-mine' => $salesPhaseCurrent === 'pick' && $canPick,
                    ])>
                        <p class="erp-grn-approve__role">Warehouse — pick</p>
                        @if($pickDone)
                            <p class="erp-grn-approve__status">Picked {{ $pickedAt?->format('d M H:i') ?? '' }}</p>
                        @elseif($canPick && $order->status === 'confirmed')
                            <p class="erp-grn-approve__prompt">Pull reserved stock using the picking list.</p>
                            <a href="{{ route('admin.orders.picking-list', $order) }}" class="erp-grn-approve__btn erp-grn-approve__btn--warehouse inline-flex items-center justify-center">
                                Open picking list
                            </a>
                        @else
                            <p class="erp-grn-approve__waiting">Waiting for warehouse team</p>
                        @endif
                    </div>

                    <div @class([
                        'erp-grn-approve__card',
                        'is-done' => $packDone,
                        'is-mine' => $salesPhaseCurrent === 'pack' && $canPack,
                    ])>
                        <p class="erp-grn-approve__role">Warehouse — pack</p>
                        @if($packDone)
                            <p class="erp-grn-approve__status">Packed {{ $packedAt?->format('d M H:i') ?? '' }}</p>
                        @elseif($canPack && $order->status === 'picked')
                            <p class="erp-grn-approve__prompt">Confirm goods are boxed and ready to ship.</p>
                            <form action="{{ route('admin.orders.status.update', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="packed">
                                <button type="submit" class="erp-grn-approve__btn erp-grn-approve__btn--warehouse">Confirm packing</button>
                            </form>
                        @else
                            <p class="erp-grn-approve__waiting">Waiting for warehouse team</p>
                        @endif
                    </div>

                    <div @class([
                        'erp-grn-approve__card',
                        'is-done' => $deliveryDone,
                        'is-mine' => $salesPhaseCurrent === 'delivery' && $canDispatch,
                    ])>
                        <p class="erp-grn-approve__role">Delivery</p>
                        @if($deliveryDone)
                            <p class="erp-grn-approve__status">Delivered {{ $deliveredAt?->format('d M H:i') ?? '' }}</p>
                        @elseif($order->status === 'dispatched')
                            <p class="erp-grn-approve__prompt">Vehicle is out — capture POD on the delivery screen.</p>
                            @if($primaryDelivery && $canDispatch)
                                <a href="{{ route('admin.deliveries.show', $primaryDelivery) }}" class="erp-grn-approve__btn erp-grn-approve__btn--delivery inline-flex items-center justify-center">
                                    Complete POD
                                </a>
                            @else
                                <p class="erp-grn-approve__waiting">Waiting for delivery team</p>
                            @endif
                        @elseif($order->status === 'packed')
                            <p class="erp-grn-approve__prompt">Schedule dispatch and assign route / vehicle.</p>
                            @if($canDispatch)
                                @if($primaryDelivery)
                                    <a href="{{ route('admin.deliveries.show', $primaryDelivery) }}" class="erp-grn-approve__btn erp-grn-approve__btn--delivery inline-flex items-center justify-center">
                                        Open delivery
                                    </a>
                                @else
                                    <a href="{{ route('admin.deliveries.create') }}" class="erp-grn-approve__btn erp-grn-approve__btn--delivery inline-flex items-center justify-center">
                                        Schedule delivery
                                    </a>
                                @endif
                            @else
                                <p class="erp-grn-approve__waiting">Waiting for delivery team</p>
                            @endif
                        @else
                            <p class="erp-grn-approve__waiting">Waiting for earlier steps</p>
                        @endif
                    </div>
                </div>
                @if(! $canConfirm && ! $canPick && ! $canPack && ! $canDispatch && $salesPhaseCurrent)
                    <p class="erp-grn-approve__note">You can view this order but cannot action the current step. Open it from your role’s inbox or ask the responsible team.</p>
                @endif
            </section>
        @endif
    @endif

    <div class="erp-order-form erp-po-show__workflow">
        <div class="erp-order-form__workflow">
            @if($isReturnOrder)
                <x-admin.order-workflow type="sales" :step="2" variant="hero" label="Return order flow" />
            @else
                <x-admin.order-workflow type="sales" :step="$salesWorkflowStep" :in-progress="$salesInProgress" variant="hero" />
            @endif
        </div>
    </div>

    <div class="erp-po-index-stats">
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Order total</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($totalValue, 0) }}</p>
            <p class="erp-po-index-stat__hint">{{ $order->items->count() }} line {{ Str::plural('item', $order->items->count()) }}</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">{{ $isReturnOrder ? 'Return date' : 'Delivery date' }}</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--neutral">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value !text-xl">{{ optional($order->delivery_date)->format('d M Y') ?? 'TBD' }}</p>
            <p class="erp-po-index-stat__hint">{{ ucfirst(str_replace('_', ' ', $order->payment_mode ?? '—')) }} · {{ $currencyCode }} {{ number_format($order->commission_total ?? 0, 0) }} commission</p>
        </div>

        @unless($isReturnOrder)
            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Fulfillment</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ number_format($fulfillmentPercent, $fulfillmentPercent == floor($fulfillmentPercent) ? 0 : 1) }}%</p>
                <p class="erp-po-index-stat__hint">{{ $statusLabel }} · {{ number_format($orderedQty, 0) }} units ordered</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Linked docs</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ $order->deliveries->count() + 1 }}</p>
                <p class="erp-po-index-stat__hint">Picking list + {{ $order->deliveries->count() }} {{ Str::plural('delivery', $order->deliveries->count()) }}</p>
            </div>
        @endunless
    </div>

    <div class="erp-po-show-layout">
        <div class="erp-po-show-main space-y-5">
            @if(!$isReturnOrder && ($order->delivery_contact_name || $order->delivery_contact_phone || $order->delivery_address))
                <div class="erp-po-show-panel">
                    <h3 class="erp-po-show-panel__title">Delivery information</h3>
                    <dl class="erp-po-show-meta mt-3">
                        @if($order->delivery_contact_name || $order->delivery_contact_phone)
                            <div class="erp-po-show-meta__row">
                                <dt>Contact</dt>
                                <dd>
                                    {{ $order->delivery_contact_name ?? '—' }}
                                    @if($order->delivery_contact_phone)
                                        · {{ $order->delivery_contact_phone }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if($order->delivery_address)
                            <div class="erp-po-show-meta__row">
                                <dt>Address</dt>
                                <dd>{{ $order->delivery_address }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            <div class="erp-order-list-card erp-po-index-table-card">
                <div class="erp-po-index-table-card__head">
                    <div>
                        <h2 class="erp-po-index-table-card__title">Line items</h2>
                        <p class="erp-po-index-table-card__desc">Products on this {{ $isReturnOrder ? 'return' : 'sales' }} order.</p>
                    </div>
                    <span class="erp-po-index-table-card__badge">
                        {{ $order->items->count() }} {{ Str::plural('line', $order->items->count()) }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="erp-order-list-table erp-po-index-table erp-po-show-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Tax class</th>
                                <th class="is-right">Ordered</th>
                                @unless($isReturnOrder)
                                    <th class="is-right">Picked</th>
                                    <th class="is-right">Packed</th>
                                    <th class="is-right">Delivered</th>
                                @endunless
                                <th class="is-right">Unit price</th>
                                <th class="is-right">Line total</th>
                                <th class="is-right">Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php
                                    $lineOrdered = (float) $item->quantity;
                                    $linePicked = (float) ($item->picked_quantity ?? 0);
                                    $linePacked = (float) ($item->packed_quantity ?? 0);
                                    $lineDelivered = \App\Services\Sales\SalesFulfillmentMetrics::deliveredQuantity($item);
                                    $pickedLinePercent = $lineOrdered > 0 ? min(100, (int) round(($linePicked / $lineOrdered) * 100)) : 0;
                                    $packedLinePercent = $lineOrdered > 0 ? min(100, (int) round(($linePacked / $lineOrdered) * 100)) : 0;
                                    $deliveredLinePercent = $lineOrdered > 0 ? min(100, (int) round(($lineDelivered / $lineOrdered) * 100)) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $item->product->name ?? '—' }}</p>
                                        @if($item->product?->sku)
                                            <p class="text-xs text-gray-500">{{ $item->product->sku }}</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap">
                                        @if($item->product && $item->product->taxClass)
                                            {{ $item->product->taxClass->name }} ({{ $item->product->taxClass->rate }}%)
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="is-right whitespace-nowrap">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($lineOrdered, 0) }}</span>
                                    </td>
                                    @unless($isReturnOrder)
                                        <td class="is-right">
                                            <div class="erp-po-show-receive">
                                                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($linePicked, 0) }}</span>
                                                <div class="erp-po-show-receive__bar" aria-hidden="true">
                                                    <span @class([
                                                        'erp-po-show-receive__fill',
                                                        'is-complete' => $pickedLinePercent >= 100,
                                                        'is-partial' => $pickedLinePercent > 0 && $pickedLinePercent < 100,
                                                    ]) style="width: {{ $pickedLinePercent }}%"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="is-right">
                                            <div class="erp-po-show-receive">
                                                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($linePacked, 0) }}</span>
                                                <div class="erp-po-show-receive__bar" aria-hidden="true">
                                                    <span @class([
                                                        'erp-po-show-receive__fill',
                                                        'is-complete' => $packedLinePercent >= 100,
                                                        'is-partial' => $packedLinePercent > 0 && $packedLinePercent < 100,
                                                    ]) style="width: {{ $packedLinePercent }}%"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="is-right">
                                            <div class="erp-po-show-receive">
                                                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($lineDelivered, 0) }}</span>
                                                <div class="erp-po-show-receive__bar" aria-hidden="true">
                                                    <span @class([
                                                        'erp-po-show-receive__fill',
                                                        'is-complete' => $deliveredLinePercent >= 100,
                                                        'is-partial' => $deliveredLinePercent > 0 && $deliveredLinePercent < 100,
                                                    ]) style="width: {{ $deliveredLinePercent }}%"></span>
                                                </div>
                                            </div>
                                        </td>
                                    @endunless
                                    <td class="is-right whitespace-nowrap">
                                        {{ $currencyCode }} {{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="is-right whitespace-nowrap">
                                        <span class="font-semibold {{ $isReturnOrder ? 'text-error-600 dark:text-error-400' : 'text-gray-900 dark:text-white' }}">
                                            {{ $currencyCode }} {{ number_format($item->quantity * $item->unit_price, 2) }}
                                        </span>
                                    </td>
                                    <td class="is-right whitespace-nowrap">
                                        <span class="font-semibold text-success-600 dark:text-success-400">
                                            {{ $currencyCode }} {{ number_format($item->commission_amount ?? 0, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @if(($order->tax_total ?? 0) > 0)
                                <tr>
                                    <td colspan="{{ $isReturnOrder ? 5 : 8 }}" class="!px-5 !py-3 text-right text-sm font-medium text-gray-600 dark:text-gray-300">Tax</td>
                                    <td class="!px-5 !py-3 text-right text-sm font-bold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($order->tax_total, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="erp-po-show-table__total">
                                <td colspan="{{ $isReturnOrder ? 5 : 8 }}" class="!px-5 !py-4 text-right text-sm font-semibold text-gray-600 dark:text-gray-300">Order total</td>
                                <td class="!px-5 !py-4 text-right">
                                    <span class="text-lg font-bold {{ $isReturnOrder ? 'text-error-600 dark:text-error-400' : 'text-brand-600 dark:text-brand-400' }}">
                                        {{ $currencyCode }} {{ number_format($order->total, 2) }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($order->statusHistory->isNotEmpty())
                <div class="erp-po-show-panel">
                    <h3 class="erp-po-show-panel__title">Status history</h3>
                    <ul class="mt-4 space-y-3">
                        @foreach($order->statusHistory->sortByDesc('changed_at') as $entry)
                            <li class="flex items-start justify-between gap-4 text-sm">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ ucfirst($entry->status) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $entry->changed_at->diffForHumans() }}</p>
                                </div>
                                <time class="whitespace-nowrap text-xs text-gray-500 dark:text-gray-400" datetime="{{ $entry->changed_at->format('Y-m-d') }}">
                                    {{ $entry->changed_at->format('d M Y, H:i') }}
                                </time>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($order->notes)
                <div class="erp-po-show-panel">
                    <h3 class="erp-po-show-panel__title">{{ $isReturnOrder ? 'Return notes' : 'Order notes' }}</h3>
                    <p class="erp-po-show-panel__body">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <aside class="erp-po-show-side space-y-5">
            <div class="erp-po-show-panel">
                <h3 class="erp-po-show-panel__title">Agent</h3>
                <div class="erp-po-index-supplier mt-3">
                    <span class="erp-po-index-supplier__avatar !h-10 !w-10">{{ $agentInitial }}</span>
                    <div class="min-w-0">
                        <p class="erp-po-index-supplier__name !text-base">{{ $agent->name ?? '—' }}</p>
                        @if($agent?->zone)
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $agent->zone }}</p>
                        @endif
                    </div>
                </div>
                <dl class="erp-po-show-meta mt-4">
                    @if($order->agent_reference)
                        <div class="erp-po-show-meta__row">
                            <dt>Agent PO ref</dt>
                            <dd>{{ $order->agent_reference }}</dd>
                        </div>
                    @endif
                    @if($agent?->phone)
                        <div class="erp-po-show-meta__row">
                            <dt>Phone</dt>
                            <dd>{{ $agent->phone }}</dd>
                        </div>
                    @endif
                    @if($order->invoice)
                        <div class="erp-po-show-meta__row">
                            <dt>Invoice</dt>
                            <dd>
                                <a href="{{ route('admin.finance.show', $order->invoice) }}" class="text-brand-600 hover:underline dark:text-brand-400">
                                    {{ $order->invoice->number ?? 'View invoice' }}
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            @unless($isReturnOrder)
                <div class="erp-po-show-panel">
                    <h3 class="erp-po-show-panel__title">Fulfillment progress</h3>
                    <div class="mt-4">
                        <div class="flex items-end justify-between gap-3">
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($fulfillmentPercent, 0) }}%</p>
                            <span class="erp-po-status erp-po-status--{{ $statusTone }}">{{ $statusLabel }}</span>
                        </div>
                        <div class="erp-po-show-receive__bar mt-3 !h-2.5" aria-hidden="true">
                            <span @class([
                                'erp-po-show-receive__fill',
                                'is-complete' => $fulfillmentPercent >= 100,
                                'is-partial' => $fulfillmentPercent > 0 && $fulfillmentPercent < 100,
                            ]) style="width: {{ $fulfillmentPercent }}%"></span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Picked {{ number_format($pickedQty, 0) }} · Packed {{ number_format($packedQty, 0) }} · Delivered {{ number_format($deliveredQty, 0) }} of {{ number_format($orderedQty, 0) }} units
                        </p>
                    </div>
                </div>

                <div class="erp-po-show-panel">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="erp-po-show-panel__title">Linked documents</h3>
                        @if(in_array($order->status, ['confirmed', 'picked'], true))
                            <a href="{{ route('admin.orders.picking-list', $order) }}" class="text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400">
                                Picking list
                            </a>
                        @endif
                    </div>

                    <ul class="erp-po-show-grn-list mt-3">
                        <li>
                            <a href="{{ route('admin.orders.picking-list', $order) }}" class="erp-po-show-grn-item">
                                <span>
                                    <span class="erp-po-show-grn-item__number">Picking list</span>
                                    <span class="erp-po-show-grn-item__date">
                                        Order #{{ $order->id }}
                                        · {{ in_array($order->status, ['picked', 'packed', 'dispatched', 'delivered'], true) ? 'Picked' : ($order->status === 'confirmed' ? 'Ready to pick' : ucfirst($order->status)) }}
                                    </span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </a>
                        </li>

                        @foreach($order->deliveries->sortByDesc('created_at') as $delivery)
                            <li>
                                <a href="{{ route('admin.deliveries.show', $delivery) }}" class="erp-po-show-grn-item">
                                    <span>
                                        <span class="erp-po-show-grn-item__number">Delivery #{{ $delivery->id }}</span>
                                        <span class="erp-po-show-grn-item__date">
                                            {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                            @if($delivery->route?->name)
                                                · {{ $delivery->route->name }}
                                            @endif
                                        </span>
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            </li>
                            @if(in_array($order->status, ['packed', 'dispatched', 'delivered'], true))
                                <li>
                                    <a href="{{ route('admin.deliveries.packing-slip', $delivery) }}" target="_blank" rel="noopener" class="erp-po-show-grn-item">
                                        <span>
                                            <span class="erp-po-show-grn-item__number">Packing slip</span>
                                            <span class="erp-po-show-grn-item__date">Delivery #{{ $delivery->id }} · Print</span>
                                        </span>
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                    @if($order->deliveries->isEmpty() && in_array($order->status, ['packed', 'picked'], true))
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                            No delivery scheduled yet.
                            @if($canDispatch)
                                Use <a href="{{ route('admin.deliveries.create') }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">Schedule delivery</a>.
                            @endif
                        </p>
                    @elseif($order->deliveries->isEmpty() && $order->status === 'confirmed')
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                            Pick stock first — delivery is created after packing.
                        </p>
                    @endif
                </div>
            @endunless
        </aside>
    </div>
</div>

<div class="print-only mt-6 text-[12px] leading-relaxed text-gray-900">
    <div class="flex items-start justify-between mb-6">
        <div class="flex items-center gap-3">
            @if(!empty($appLogoUrl))
                <img src="{{ $appLogoUrl }}" alt="{{ $legalCompanyName }}" class="h-10 w-auto max-w-[9rem] object-contain" />
            @else
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-500 text-sm font-semibold text-white">
                    {{ $legalCompanyInitials }}
                </div>
            @endif
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $legalCompanyName }}</h2>
                <p class="mt-1 text-xs text-gray-600">{{ $isReturnOrder ? 'Return Summary' : 'Order &amp; Delivery' }}</p>
            </div>
        </div>
        <div class="text-right space-y-1">
            <h1 class="text-xl font-bold text-gray-900">{{ $isReturnOrder ? 'Return Order' : 'Order' }} #{{ $order->id }}</h1>
            <p class="text-xs text-gray-600">Customer: {{ $order->agent->name }}</p>
            <p class="text-xs text-gray-600">Issued: {{ $order->created_at?->format('d M Y') ?? now()->format('d M Y') }}</p>
            @if($order->delivery_date)
                <p class="text-xs text-gray-600">{{ $isReturnOrder ? 'Return' : 'Delivery' }}: {{ $order->delivery_date->format('d M Y') }}</p>
            @endif
            <p class="text-xs text-gray-600">Status: {{ ucfirst($order->status) }}</p>
        </div>
    </div>

    <div class="flex justify-between mb-4">
        <div>
            <h3 class="font-semibold text-sm">Bill to</h3>
            <p class="mt-1">
                {{ $order->agent->name }}<br>
                @if($order->agent->zone)
                    {{ $order->agent->zone }}<br>
                @endif
                @if($order->agent_reference)
                    Ref: {{ $order->agent_reference }}
                @endif
            </p>

            @if($order->delivery_address)
                <div class="mt-6">
                    <h3 class="font-semibold text-sm uppercase tracking-wide">{{ $isReturnOrder ? 'Return address' : 'Delivery address' }}</h3>
                    <p class="mt-1">{{ $order->delivery_address }}</p>
                </div>
            @endif

            @if($order->notes)
                <div class="mt-6">
                    <h3 class="font-semibold text-sm uppercase tracking-wide">{{ $isReturnOrder ? 'Return notes' : 'Notes' }}</h3>
                    <p class="mt-1">{{ $order->notes }}</p>
                </div>
            @endif
        </div>
        <div class="text-right">
            <p>{{ $isReturnOrder ? 'Return' : 'Order' }} No: <strong>#{{ $order->id }}</strong></p>
            <p>{{ $isReturnOrder ? 'Return type' : 'Order type' }}: {{ $isReturnOrder ? 'Customer return' : ucfirst($order->order_type) }}</p>
            @if($order->payment_mode)
                <p>Payment: {{ ucfirst(str_replace('_', ' ', $order->payment_mode)) }}</p>
            @endif
            @if($order->delivery_contact_name)
                <p>Contact: <strong>{{ $order->delivery_contact_name }}</strong></p>
            @endif
            @if($order->delivery_contact_phone)
                <p>Phone: {{ $order->delivery_contact_phone }}</p>
            @endif
        </div>
    </div>

    <table class="w-full border-collapse text-[11px]">
        <thead>
            <tr>
                <th class="border border-gray-300 px-2 py-1 text-left">#</th>
                <th class="border border-gray-300 px-2 py-1 text-left">Description</th>
                <th class="border border-gray-300 px-2 py-1 text-right">Qty</th>
                <th class="border border-gray-300 px-2 py-1 text-right">Unit Price</th>
                <th class="border border-gray-300 px-2 py-1 text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td class="border border-gray-200 px-2 py-1 text-left">{{ $index + 1 }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-left">
                        {{ $item->product->name ?? '—' }}
                        @if($item->product?->size)
                            <div class="text-[10px] text-gray-500">{{ $item->product->size }}</div>
                        @endif
                        @if($item->product?->sku)
                            <div class="text-[10px] text-gray-500">SKU: {{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td class="border border-gray-200 px-2 py-1 text-right">{{ number_format($item->quantity, 0) }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-right">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4 flex justify-end">
        <table class="text-[11px]">
            <tr>
                <td class="px-3 py-1 text-right">Net total:</td>
                <td class="px-3 py-1 text-right">{{ number_format($order->items->sum(function ($item) { return $item->quantity * $item->unit_price; }), 2) }}</td>
            </tr>
            @if(($order->tax_total ?? 0) > 0)
                <tr>
                    <td class="px-3 py-1 text-right">VAT:</td>
                    <td class="px-3 py-1 text-right">{{ number_format((float) ($order->tax_total ?? 0), 2) }}</td>
                </tr>
            @endif
            @if(($order->commission_total ?? 0) > 0)
                <tr>
                    <td class="px-3 py-1 text-right">Commission:</td>
                    <td class="px-3 py-1 text-right">{{ number_format((float) ($order->commission_total ?? 0), 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="px-3 py-1 text-right font-semibold border-t border-gray-300">Total:</td>
                <td class="px-3 py-1 text-right font-semibold border-t border-gray-300">{{ number_format((float) $order->total, 2) }}</td>
            </tr>
        </table>
    </div>
</div>

@push('styles')
<style media="print">
    @page {
        size: A4;
        margin: 12mm;
    }
</style>

<style>
    @media print {
        body {
            background: #ffffff !important;
        }

        #sidebar,
        header,
        .screen-order-view {
            display: none !important;
        }

        .print-only {
            display: block !important;
        }
    }

    @media screen {
        .print-only {
            display: none !important;
        }
    }
</style>
@endpush
@endsection
