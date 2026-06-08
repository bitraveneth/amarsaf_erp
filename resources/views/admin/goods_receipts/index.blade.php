@extends('layouts.app')

@section('content')
@php
    $tabs = [
        'awaiting' => 'Awaiting receipt',
        'pending' => 'Pending approval',
        'posted' => 'Posted',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Goods receipt notes (GRN)</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Receive approved POs, dual sign-off, then stock posts automatically.</p>
        </div>
        <a href="{{ route('admin.goods-receipts.create') }}" data-tour="goods-receipts-primary-action"
           class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            Manual GRN
        </a>
    </div>

    @include('layouts.partials.procurement-inbox-strip')

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <x-admin.procurement-guide
        title="GRN workflow"
        :steps="[
            ['label' => 'Awaiting receipt', 'hint' => 'Approved POs — click Receive goods.', 'state' => $tab === 'awaiting' ? 'current' : 'upcoming'],
            ['label' => 'Pending approval', 'hint' => 'Warehouse + procurement sign-off.', 'state' => $tab === 'pending' ? 'current' : 'upcoming'],
            ['label' => 'Posted', 'hint' => 'Stock updated — view history.', 'state' => $tab === 'posted' ? 'current' : 'upcoming'],
        ]"
    />

    <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-1 dark:border-gray-800">
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.goods-receipts.index', ['tab' => $key]) }}"
               @class([
                   'rounded-t-lg px-4 py-2 text-sm font-semibold transition',
                   'bg-white text-brand-700 shadow-sm ring-1 ring-gray-200 dark:bg-gray-900 dark:text-brand-300 dark:ring-gray-700' => $tab === $key,
                   'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' => $tab !== $key,
               ])>
                {{ $label }}
                @if($key === 'awaiting' && $awaitingOrders->count() > 0)
                    <span class="ml-1 rounded-full bg-brand-100 px-2 py-0.5 text-xs text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">{{ $awaitingOrders->count() }}</span>
                @endif
                @if($key === 'pending' && $pendingReceipts->total() > 0)
                    <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800 dark:bg-amber-500/20 dark:text-amber-200">{{ $pendingReceipts->total() }}</span>
                @endif
            </a>
        @endforeach
    </div>

    @if($tab === 'awaiting')
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">PO</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Order date</th>
                        <th class="px-4 py-3">Open lines</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($awaitingOrders as $order)
                        @php
                            $openLines = $order->items->filter(fn ($i) => max((float) $i->quantity - (float) $i->received_quantity, 0) > 0)->count();
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $order->number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $order->supplier->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ optional($order->order_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $openLines }} line(s)</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.purchase-orders.receive', $order) }}"
                                   class="inline-flex items-center rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600">
                                    Receive goods
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No purchase orders awaiting receipt. Approve a PO to queue it here.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($tab === 'pending')
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">GRN</th>
                        <th class="px-4 py-3">PO</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Warehouse</th>
                        <th class="px-4 py-3">Sign-offs</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($pendingReceipts as $receipt)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $receipt->grn_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $receipt->purchaseOrder?->number ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $receipt->supplier->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $receipt->warehouse->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                <span @class(['font-semibold', 'text-emerald-600' => $receipt->warehouse_approved_at])>WH {{ $receipt->warehouse_approved_at ? '✓' : '—' }}</span>
                                <span class="mx-1 text-gray-300">·</span>
                                <span @class(['font-semibold', 'text-emerald-600' => $receipt->procurement_approved_at])>Proc {{ $receipt->procurement_approved_at ? '✓' : '—' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.goods-receipts.show', $receipt) }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">Review →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No GRNs waiting for approval.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($pendingReceipts->hasPages())
                <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $pendingReceipts->links() }}</div>
            @endif
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">GRN</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Warehouse</th>
                        <th class="px-4 py-3">Received at</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($receipts as $receipt)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $receipt->grn_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $receipt->supplier->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $receipt->warehouse->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ optional($receipt->received_at)->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $receipt->status)) }}</td>
                            <td class="px-4 py-3 text-right">
                                <x-admin.action-group>
                                    <x-admin.action-view :href="route('admin.goods-receipts.show', $receipt)" />
                                    @if($receipt->status === 'posted')
                                        <form action="{{ route('admin.goods-receipts.reverse', $receipt) }}" method="POST" onsubmit="return confirm('Reverse {{ $receipt->grn_number }}?');" class="inline">
                                            @csrf
                                            <button type="submit" class="erp-btn-action-danger">Reverse</button>
                                        </form>
                                    @endif
                                </x-admin.action-group>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No GRN posted yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($receipts->hasPages())
                <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $receipts->links() }}</div>
            @endif
        </div>
    @endif
</div>
@endsection
