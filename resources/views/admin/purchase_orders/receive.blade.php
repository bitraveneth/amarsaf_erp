@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $fieldClass = 'w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white';
    $labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400';
    $openItems = $purchaseOrder->items->filter(fn ($item) => max((float) $item->quantity - (float) $item->received_quantity, 0) > 0);
@endphp

<div class="mx-auto max-w-(--breakpoint-2xl) space-y-6">
    @include('layouts.partials.procurement-inbox-strip')

    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-brand-600 dark:text-brand-400">Receive against PO</p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $purchaseOrder->number }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $purchaseOrder->supplier->name ?? '—' }} · PO is locked — enter actual quantities received.
            </p>
        </div>
        <a href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-gray-200 bg-white/80 px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs backdrop-blur-sm hover:bg-white dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to {{ $purchaseOrder->number }}
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin.purchase-orders.receive.store', $purchaseOrder) }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="supplier_id" value="{{ $purchaseOrder->supplier_id }}">

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="{{ $labelClass }}">Warehouse <span class="text-red-500">*</span></label>
                    <select name="warehouse_id" required class="{{ $fieldClass }}">
                        <option value="">Select warehouse</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Received at <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="received_at" value="{{ old('received_at', now()->format('Y-m-d\TH:i')) }}" required class="{{ $fieldClass }}">
                </div>
                <div class="sm:col-span-3">
                    <label class="{{ $labelClass }}">GRN notes</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Delivery note ref, vehicle no…" class="{{ $fieldClass }}">
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Lines to receive</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Services and custom lines confirm receipt on the PO without adding stock. If you receive less than the open balance, remarks are required.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:bg-gray-900/80 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Material / service</th>
                            <th class="px-4 py-3 text-right">Ordered</th>
                            <th class="px-4 py-3 text-right">Already received</th>
                            <th class="px-4 py-3 text-right">Open</th>
                            <th class="px-4 py-3 text-right">Receive now</th>
                            <th class="px-4 py-3">Remarks</th>
                            <th class="px-4 py-3">Location</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($openItems as $index => $item)
                            @php
                                $ordered = (float) $item->quantity;
                                $received = (float) $item->received_quantity;
                                $open = max($ordered - $received, 0);
                                $label = $item->product?->name ?? $item->description;
                                $isStockLine = $item->product?->isStockTracked() ?? false;
                                $lineKind = ! $item->product_id ? 'Custom' : ($isStockLine ? 'Stock' : 'Service');
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $item->id }}">
                                    @if($item->product_id)
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                    @endif
                                    <input type="hidden" name="items[{{ $index }}][unit_cost]" value="{{ $item->unit_price }}">
                                    <input type="hidden" name="items[{{ $index }}][qc_status]" value="approved">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $label }}</p>
                                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                        <span>{{ $item->product?->uom ?? $item->uom }}</span>
                                        @if($lineKind !== 'Stock')
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $lineKind }} · no stock</span>
                                        @endif
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums text-gray-700 dark:text-gray-300">{{ number_format($ordered, 2) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums text-gray-700 dark:text-gray-300">{{ number_format($received, 2) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums font-semibold text-gray-900 dark:text-white">{{ number_format($open, 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <input type="number" min="0.01" max="{{ $open }}" step="0.01"
                                           name="items[{{ $index }}][quantity]"
                                           value="{{ old('items.'.$index.'.quantity', $open) }}"
                                           class="{{ $fieldClass }} max-w-[8rem] text-right font-semibold tabular-nums" required>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="items[{{ $index }}][remarks]"
                                           value="{{ old('items.'.$index.'.remarks') }}"
                                           placeholder="Required if partial"
                                           class="{{ $fieldClass }} min-w-[12rem]">
                                </td>
                                <td class="px-4 py-3">
                                    @if($isStockLine)
                                        <select name="items[{{ $index }}][warehouse_location_id]" class="{{ $fieldClass }} min-w-[8rem]">
                                            <option value="">—</option>
                                            @foreach($locations as $location)
                                                <option value="{{ $location->id }}" @selected(old('items.'.$index.'.warehouse_location_id') == $location->id)>{{ $location->code }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <span class="text-xs text-gray-400">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="flex flex-col gap-3 rounded-2xl border border-brand-100 bg-brand-50/50 px-5 py-4 text-sm text-brand-900 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-100 sm:flex-row sm:items-center sm:justify-between">
            <p>Submitting creates a <strong>pending GRN</strong>. Warehouse and procurement must both approve. Stock posts only for material lines — services and custom items update the PO only.</p>
            <div class="flex shrink-0 gap-3">
                <a href="{{ route('admin.goods-receipts.index', ['tab' => 'awaiting']) }}" class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">Cancel</a>
                <button type="submit" class="inline-flex items-center rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600">Submit for approval</button>
            </div>
        </div>
    </form>
</div>
@endsection
