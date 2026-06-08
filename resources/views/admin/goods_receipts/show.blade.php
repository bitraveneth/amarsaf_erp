@extends('layouts.app')

@section('content')
@php
    $grnGuideSteps = [
        ['label' => 'GRN submitted', 'hint' => 'Quantities captured — PO not updated yet.', 'state' => 'complete'],
        [
            'label' => 'Warehouse sign-off',
            'hint' => $receipt->warehouse_approved_at
                ? 'Approved by ' . ($receipt->warehouseApprover?->name ?? 'warehouse')
                : 'Warehouse officer confirms physical receipt.',
            'state' => $receipt->warehouse_approved_at ? 'complete' : ($canApproveWarehouse ? 'current' : 'upcoming'),
        ],
        [
            'label' => 'Procurement sign-off',
            'hint' => $receipt->procurement_approved_at
                ? 'Approved by ' . ($receipt->procurementApprover?->name ?? 'procurement')
                : 'Procurement confirms against PO.',
            'state' => $receipt->procurement_approved_at ? 'complete' : ($canApproveProcurement ? 'current' : 'upcoming'),
        ],
        [
            'label' => 'Stock posted',
            'hint' => $receipt->status === 'posted' ? 'Inventory updated.' : 'Happens automatically after both approvals.',
            'state' => $receipt->status === 'posted' ? 'complete' : 'upcoming',
        ],
    ];
@endphp
<div class="space-y-6">
    @include('layouts.partials.procurement-inbox-strip')

    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $receipt->grn_number }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $receipt->supplier->name ?? '—' }} · {{ $receipt->warehouse->name ?? '—' }} · {{ optional($receipt->received_at)->format('d M Y H:i') }}
            </p>
            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                Status: {{ ucfirst(str_replace('_', ' ', $receipt->status)) }}
                @if($receipt->purchaseOrder)
                    · PO <a href="{{ route('admin.purchase-orders.show', $receipt->purchaseOrder) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ $receipt->purchaseOrder->number }}</a>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-admin.document-actions type="grn" :id="$receipt->id" compact />
            @if($receipt->status === 'posted')
                <form action="{{ route('admin.goods-receipts.reverse', $receipt) }}" method="POST" onsubmit="return confirm('Reverse {{ $receipt->grn_number }}?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-lg border border-error-300 px-4 py-2 text-sm font-medium text-error-700 hover:bg-error-50 dark:border-error-500/30 dark:text-error-400">Reverse GRN</button>
                </form>
            @endif
            <a href="{{ route('admin.goods-receipts.index', ['tab' => $receipt->status === 'pending_approval' ? 'pending' : 'posted']) }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Back</a>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">{{ $errors->first() }}</div>
    @endif

    <x-admin.procurement-guide title="GRN approval progress" :steps="$grnGuideSteps" />

    @if($receipt->status === 'pending_approval')
        <section class="erp-grn-approve">
            <h2 class="erp-grn-approve__title">Your action</h2>
            <div class="erp-grn-approve__grid">
                <div @class(['erp-grn-approve__card', 'is-done' => $receipt->warehouse_approved_at, 'is-mine' => $canApproveWarehouse && ! $receipt->warehouse_approved_at])>
                    <p class="erp-grn-approve__role">Warehouse</p>
                    @if($receipt->warehouse_approved_at)
                        <p class="erp-grn-approve__status">Signed off {{ $receipt->warehouse_approved_at->format('d M H:i') }}</p>
                        <p class="text-xs text-gray-500">{{ $receipt->warehouseApprover?->name }}</p>
                    @elseif($canApproveWarehouse)
                        <p class="erp-grn-approve__prompt">Confirm goods arrived at {{ $receipt->warehouse->name ?? 'warehouse' }}.</p>
                        <form action="{{ route('admin.goods-receipts.approve-warehouse', $receipt) }}" method="POST">
                            @csrf
                            <button type="submit" class="erp-grn-approve__btn erp-grn-approve__btn--warehouse">Sign off as warehouse</button>
                        </form>
                    @else
                        <p class="erp-grn-approve__waiting">Waiting for warehouse team</p>
                    @endif
                </div>
                <div @class(['erp-grn-approve__card', 'is-done' => $receipt->procurement_approved_at, 'is-mine' => $canApproveProcurement && ! $receipt->procurement_approved_at])>
                    <p class="erp-grn-approve__role">Procurement</p>
                    @if($receipt->procurement_approved_at)
                        <p class="erp-grn-approve__status">Signed off {{ $receipt->procurement_approved_at->format('d M H:i') }}</p>
                        <p class="text-xs text-gray-500">{{ $receipt->procurementApprover?->name }}</p>
                    @elseif($canApproveProcurement)
                        <p class="erp-grn-approve__prompt">Confirm receipt matches PO {{ $receipt->purchaseOrder?->number ?? 'terms' }}.</p>
                        <form action="{{ route('admin.goods-receipts.approve-procurement', $receipt) }}" method="POST">
                            @csrf
                            <button type="submit" class="erp-grn-approve__btn erp-grn-approve__btn--procurement">Sign off as procurement</button>
                        </form>
                    @else
                        <p class="erp-grn-approve__waiting">Waiting for procurement team</p>
                    @endif
                </div>
            </div>
            @if(! $canApproveWarehouse && ! $canApproveProcurement)
                <p class="erp-grn-approve__note">You can view this GRN but cannot sign off. Ask warehouse or procurement to open this page from <strong>GRN inbox → Pending approval</strong> or their notifications.</p>
            @endif
        </section>
    @endif

    @if(!empty($reversalBlockers))
        <div class="rounded-2xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-200">
            <div class="font-semibold">This GRN cannot be reversed right now.</div>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($reversalBlockers as $blocker)
                    <li>{{ $blocker }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($receipt->purchaseOrder)
        <section class="rounded-2xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/40">
            <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500">Linked PO (read-only)</h3>
            <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $receipt->purchaseOrder->number }} — lines below show what was received on this GRN.</p>
        </section>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3 text-right">Qty received</th>
                    <th class="px-4 py-3">QC</th>
                    <th class="px-4 py-3">Remarks</th>
                    <th class="px-4 py-3">Location</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($receipt->items as $item)
                    @php
                        $poItem = $item->purchaseOrderItem;
                        $ordered = $poItem ? (float) $poItem->quantity : null;
                    @endphp
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">
                            <p class="font-medium">{{ $item->product->name ?? $poItem?->description ?? '—' }}</p>
                            @if($ordered !== null)
                                <p class="text-xs text-gray-500">PO ordered: {{ number_format($ordered, 2) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format($item->quantity, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($item->qc_status) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $item->remarks ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->location->code ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($receipt->notes)
        <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <span class="font-semibold text-gray-900 dark:text-white">Notes:</span> {{ $receipt->notes }}
        </div>
    @endif
</div>
@endsection
