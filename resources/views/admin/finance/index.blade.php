@extends('layouts.app')

@section('content')
@php
    $currency = config('app.currency', 'BDT');
@endphp
<div class="erp-page" data-tour="finance-overview-header">
    <x-admin.page-header
        title="Customer invoices"
        subtitle="Sales invoices, what was collected, and what is still due."
        icon="ledger"
    />

    <div class="erp-dash-kpi-grid">
        <x-dashboard.kpi label="Invoices" :value="number_format($summary['count'])" :hint="number_format($summary['open']) . ' still open'" />
        <x-dashboard.kpi label="Invoiced" :value="$currency . ' ' . number_format($summary['gross'], 0)" hint="Net plus VAT" />
        <x-dashboard.kpi label="Collected" tone="success" :value="$currency . ' ' . number_format($summary['collected'], 0)" hint="Receipts and advances applied" />
        <x-dashboard.kpi label="Outstanding" tone="warning" :value="$currency . ' ' . number_format($summary['outstanding'], 0)" hint="Still to collect" />
    </div>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <h2 class="erp-h3">Invoice register</h2>
            <p class="erp-caption">{{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }} of {{ number_format($invoices->total()) }}</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="table-layout: fixed; width: 100%;">
                <colgroup>
                    <col>
                    <col style="width: 12rem">
                    <col style="width: 10.5rem">
                    <col style="width: 10.5rem">
                    <col style="width: 6.5rem">
                    <col style="width: 28rem">
                </colgroup>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Agent</th>
                        <th class="is-right">Total</th>
                        <th class="is-right">Due</th>
                        <th>Status</th>
                        <th class="is-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        @php
                            $due = (float) $invoice->outstanding;
                            $total = (float) $invoice->gross_total;
                            $badge = match ($invoice->status) {
                                'paid' => 'erp-badge-success',
                                'adjusted' => 'erp-badge-warning',
                                'overdue' => 'erp-badge-warning',
                                default => 'erp-badge-brand',
                            };
                            $canMarkPaid = $invoice->status !== 'paid' && $due > 0;
                        @endphp
                        <tr class="cursor-pointer"
                            onclick="if (!event.target.closest('a,button,input,label,select,form')) window.location = this.dataset.href"
                            data-href="{{ route('admin.finance.show', $invoice) }}">
                            <td>
                                <a href="{{ route('admin.finance.show', $invoice) }}" class="erp-table-link">{{ $invoice->number }}</a>
                                <p class="erp-caption">{{ $invoice->issued_at?->format('d M Y') ?? 'No date' }}{{ $invoice->order_id ? ' · Order #' . $invoice->order_id : '' }}</p>
                            </td>
                            <td class="erp-table-cell">{{ $invoice->order?->agent?->name ?? '—' }}</td>
                            <td class="is-right"><span class="erp-table-num">{{ $currency }} {{ number_format($total, 2) }}</span></td>
                            <td class="is-right">
                                <span class="erp-table-num">{{ $due > 0 ? $currency . ' ' . number_format($due, 2) : '—' }}</span>
                            </td>
                            <td><span class="{{ $badge }}">{{ ucfirst($invoice->status) }}</span></td>
                            <td class="text-right whitespace-nowrap">
                                <div class="erp-action-group flex-nowrap justify-end">
                                    <x-admin.document-link type="invoice" :id="$invoice->id" label="Print" />
                                    @if($canMarkPaid)
                                        <form action="{{ route('admin.finance.receipts.store', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('Mark invoice {{ $invoice->number }} as paid for {{ $currency }} {{ number_format($due, 2) }}?');">
                                            @csrf
                                            <input type="hidden" name="amount" value="{{ $due }}">
                                            <input type="hidden" name="payment_method" value="bank_transfer">
                                            <input type="hidden" name="received_at" value="{{ now()->format('Y-m-d') }}">
                                            <button type="submit" class="erp-btn-action">Mark paid</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.finance.credit-notes.create', $invoice) }}" class="erp-btn-action-warning">Credit</a>
                                    <a href="{{ route('admin.finance.show', $invoice) }}" class="erp-btn-action">View</a>
                                    <form action="{{ route('admin.finance.destroy', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('Delete invoice {{ $invoice->number }}? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="erp-btn-action-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state title="No invoices" description="Invoices appear here when a delivered order is billed." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    <x-admin.quick-guide
        title="How customer invoices work"
        lead="Invoices usually appear after a delivered sales order is billed. This screen is for collection and follow-up."
        :steps="[
            ['title' => 'Open an invoice', 'body' => 'See lines, VAT, withholding, receipts, advances, and credit notes.'],
            ['title' => 'Record a receipt', 'body' => 'When the agent pays, post cash/bank against the invoice outstanding.'],
            ['title' => 'Watch outstanding', 'body' => 'KPI and Due column show what is still collectible.'],
            ['title' => 'Statements', 'body' => 'Customer statement by agent shows opening, invoices, receipts, and running balance.'],
        ]"
        :examples="[
            [
                'eyebrow' => 'Typical flow',
                'title' => 'Order → delivery → invoice → receipt',
                'bullets' => [
                    'Sales order confirmed and delivered',
                    'Invoice posts AR, sales, and VAT',
                    'Receipt reduces AR and increases bank',
                    'Status moves toward Paid when outstanding hits zero',
                ],
            ],
        ]"
        note="Prefer receipt and credit-note screens on the invoice — not a manual journal — for normal collections and returns."
        :open="false"
    />
</div>
@endsection
