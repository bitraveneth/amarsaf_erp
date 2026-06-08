@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $canConfirmPicking = ($fulfillment['can_pick'] ?? false) && $order->status === 'confirmed';
    $guideSteps = [
        ['label' => 'Order confirmed', 'state' => 'complete'],
        ['label' => 'Pick stock', 'hint' => 'Use batch suggestions below, then enter picked quantities.', 'state' => $order->status === 'confirmed' ? 'current' : 'complete'],
        ['label' => 'Confirm packing', 'hint' => 'Back on the sales order after picking is complete.', 'state' => $order->status === 'picked' ? 'current' : 'upcoming'],
    ];
    if ($order->status === 'picked') {
        $guideSteps[1]['state'] = 'complete';
        $guideSteps[2]['state'] = 'current';
        $guideSteps[2]['action_url'] = route('admin.orders.show', $order);
        $guideSteps[2]['action_label'] = 'Confirm packing on order';
    }
@endphp

<div class="erp-order-page erp-order-page--index erp-po-show screen-picking-list">
    <x-admin.order-toolbar
        :title="'Picking list · Order #' . $order->id"
        :subtitle="$order->agent->name . ' · Delivery ' . (optional($order->delivery_date)->format('d M Y') ?? 'TBD')"
        :back-url="route('admin.orders.show', $order)"
        back-label="Back to order"
    >
        <x-slot:actions>
            <span class="erp-po-status erp-po-status--{{ $order->status === 'confirmed' ? 'success' : 'brand' }}">{{ ucfirst($order->status) }}</span>
            <x-admin.document-actions type="picking-list" :id="$order->id" compact />
            <button type="button" onclick="window.print()" class="erp-order-btn erp-order-btn--secondary print-hidden">Print</button>
        </x-slot:actions>
    </x-admin.order-toolbar>

    @include('layouts.partials.fulfillment-inbox-strip')

    @if(session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-5 rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">{{ session('error') }}</div>
    @endif

    <x-admin.procurement-guide title="Picking progress" :steps="$guideSteps" class="mb-5" />

    <div class="erp-po-index-stats mb-5">
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Ordered</span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($metrics['ordered_qty'] ?? 0, 0) }}</p>
            <p class="erp-po-index-stat__hint">{{ $order->items->count() }} lines</p>
        </div>
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Picked</span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($metrics['picked_percent'] ?? 0, 0) }}%</p>
            <p class="erp-po-index-stat__hint">{{ number_format($metrics['picked_qty'] ?? 0, 0) }} of {{ number_format($metrics['ordered_qty'] ?? 0, 0) }} units</p>
        </div>
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Batch lines</span>
            </div>
            <p class="erp-po-index-stat__value">{{ count($lines) }}</p>
            <p class="erp-po-index-stat__hint">FEFO warehouse suggestions</p>
        </div>
    </div>

    @if($canConfirmPicking)
        <form action="{{ route('admin.orders.confirm-pick', $order) }}" method="POST" class="erp-order-list-card erp-po-index-table-card mb-5">
            @csrf
            <div class="erp-po-index-table-card__head">
                <div>
                    <h2 class="erp-po-index-table-card__title">Confirm picked quantities</h2>
                    <p class="erp-po-index-table-card__desc">Enter actual qty pulled — partial picks stay on the queue until 100% picked.</p>
                </div>
                <button type="submit" class="erp-order-btn erp-order-btn--success">Save &amp; confirm pick</button>
            </div>
            <div class="overflow-x-auto">
                <table class="erp-order-list-table erp-po-index-table erp-po-show-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="is-right">Ordered</th>
                            <th class="is-right">Already picked</th>
                            <th class="is-right">Total picked</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php
                                $ordered = (float) $item->quantity;
                                $already = (float) ($item->picked_quantity ?? 0);
                                $defaultPick = $already > 0 ? $already : $ordered;
                            @endphp
                            <tr>
                                <td>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $item->product->name ?? '—' }}</p>
                                    @if($item->product?->sku)
                                        <p class="text-xs text-gray-500">{{ $item->product->sku }}</p>
                                    @endif
                                    <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                </td>
                                <td class="is-right font-medium">{{ number_format($ordered, 0) }}</td>
                                <td class="is-right">{{ number_format($already, 0) }}</td>
                                <td class="is-right">
                                    <input type="number"
                                           name="items[{{ $loop->index }}][picked_quantity]"
                                           value="{{ old('items.'.$loop->index.'.picked_quantity', $defaultPick) }}"
                                           min="0"
                                           max="{{ $ordered }}"
                                           step="1"
                                           class="w-24 rounded-xl border border-gray-200 bg-white px-3 py-2 text-right text-sm dark:border-gray-700 dark:bg-gray-900">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    @elseif($order->status === 'picked')
        <div class="erp-po-show-panel mb-5">
            <p class="erp-po-show-panel__body">Picking is complete for this order. Return to the order page to confirm packing.</p>
            <a href="{{ route('admin.orders.show', $order) }}" class="erp-order-btn erp-order-btn--brand mt-4 inline-flex">Open sales order</a>
        </div>
    @endif

    <div class="erp-order-list-card erp-po-index-table-card">
        <div class="erp-po-index-table-card__head">
            <div>
                <h2 class="erp-po-index-table-card__title">Batch picking suggestions</h2>
                <p class="erp-po-index-table-card__desc">Pull from these warehouses and batches (FEFO). Verify on the floor before confirming above.</p>
            </div>
            <span class="erp-po-index-table-card__badge">{{ count($lines) }} lines</span>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-order-list-table erp-po-index-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product</th>
                        <th class="is-right">Qty</th>
                        <th>Warehouse</th>
                        <th>Batch</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lines as $line)
                        <tr>
                            <td class="font-mono text-sm">{{ $line['sku'] ?: '—' }}</td>
                            <td>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $line['name'] }}</p>
                                @if(!empty($line['size']))
                                    <p class="text-xs text-gray-500">{{ $line['size'] }}</p>
                                @endif
                            </td>
                            <td class="is-right font-semibold">{{ $line['quantity'] }}</td>
                            <td>{{ $line['warehouse'] ?? '—' }}</td>
                            <td>
                                {{ $line['batch'] ?? '—' }}
                                @if(!empty($line['expiry_date']))
                                    <p class="text-xs text-gray-500">Exp {{ \Carbon\Carbon::parse($line['expiry_date'])->format('d M Y') }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="!py-10 text-center text-sm text-gray-500">No batch suggestions available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-8 print:block print-hidden">
        <div class="erp-po-show-panel"><div class="mb-8 border-b border-gray-300"></div><p class="text-xs text-gray-500">Picker signature</p></div>
        <div class="erp-po-show-panel"><div class="mb-8 border-b border-gray-300"></div><p class="text-xs text-gray-500">Checker signature</p></div>
        <div class="erp-po-show-panel"><div class="mb-8 border-b border-gray-300"></div><p class="text-xs text-gray-500">Supervisor signature</p></div>
    </div>
</div>

@push('styles')
<style media="print">
    @page { size: A4; margin: 1.5cm; }
    .print-hidden, #sidebar, header { display: none !important; }
</style>
@endpush
@endsection
