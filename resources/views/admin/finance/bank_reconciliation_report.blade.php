@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
    $openCount = ($totals['receipts_open'] ?? 0) + ($totals['payments_open'] ?? 0);
@endphp

<x-report.page
    eyebrow="Accountant tools"
    title="Bank reconciliation"
    subtitle="Customer receipts and supplier payments for the selected period."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.bank-reconciliation')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="accountant" />
            <a href="{{ route('admin.finance.reconciliation', request()->only(['range', 'from', 'to'])) }}" class="erp-btn-primary">Reconcile</a>
            <x-report.export-actions module="bank-reconciliation" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Receipts in"
            tone="success"
            :value="$ccy . ' ' . number_format($totals['receipts_total'], 0)"
            :hint="$totals['receipts_count'] . ' receipts · ' . ($totals['receipts_reconciled'] ?? 0) . ' matched'"
        />
        <x-dashboard.kpi
            label="Payments out"
            tone="warning"
            :value="$ccy . ' ' . number_format($totals['payments_total'], 0)"
            :hint="$totals['payments_count'] . ' payments · ' . ($totals['payments_reconciled'] ?? 0) . ' matched'"
        />
        <x-dashboard.kpi
            label="Net movement"
            :value="$ccy . ' ' . number_format($totals['net'], 0)"
            hint="Receipts minus payments"
        />
        <x-dashboard.kpi
            label="Still open"
            :tone="$openCount > 0 ? 'warning' : 'success'"
            :value="(string) $openCount"
            hint="Not yet reconciled"
        />
    </x-slot:kpis>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="erp-table-card">
            <div class="erp-table-card-header">
                <div>
                    <h2 class="erp-h3">Receipts</h2>
                    <p class="erp-caption">Customer collections in period</p>
                </div>
                <p class="erp-caption">{{ number_format($totals['receipts_count']) }}</p>
            </div>
            <div class="erp-table-wrap">
                <table class="erp-table w-full" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice / customer</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th class="is-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            @php
                                $invoice = $receipt->invoice;
                                $agentName = $invoice?->order?->agent?->name;
                                $method = $receipt->payment_method
                                    ? ucfirst(str_replace('_', ' ', $receipt->payment_method))
                                    : '—';
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap">{{ $receipt->received_at?->format('d M Y') ?? '—' }}</td>
                                <td>
                                    @if($invoice)
                                        <a href="{{ route('admin.finance.show', $invoice) }}" class="erp-table-link">{{ $invoice->number ?? ('#' . $invoice->id) }}</a>
                                    @else
                                        <span>—</span>
                                    @endif
                                    <p class="erp-caption">{{ $agentName ?: 'No agent' }}</p>
                                </td>
                                <td>{{ $method }}</td>
                                <td>
                                    <span class="{{ $receipt->reconciled ? 'erp-badge-success' : 'erp-badge-warning' }}">
                                        {{ $receipt->reconciled ? 'Matched' : 'Open' }}
                                    </span>
                                </td>
                                <td class="is-right"><span class="erp-table-num">{{ number_format((float) $receipt->amount, 2) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-admin.empty-state title="No receipts in this period" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="erp-table-card">
            <div class="erp-table-card-header">
                <div>
                    <h2 class="erp-h3">Payments</h2>
                    <p class="erp-caption">Supplier bill payments in period</p>
                </div>
                <p class="erp-caption">{{ number_format($totals['payments_count']) }}</p>
            </div>
            <div class="erp-table-wrap">
                <table class="erp-table w-full" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Bill / supplier</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th class="is-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            @php
                                $bill = $payment->bill;
                                $method = $payment->method
                                    ? ucfirst(str_replace('_', ' ', $payment->method))
                                    : '—';
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap">{{ $payment->paid_at?->format('d M Y') ?? '—' }}</td>
                                <td>
                                    @if($bill)
                                        <a href="{{ route('admin.bills.show', $bill) }}" class="erp-table-link">{{ $bill->number ?? ('#' . $bill->id) }}</a>
                                    @else
                                        <span>{{ $payment->batch_reference ?: '—' }}</span>
                                    @endif
                                    <p class="erp-caption">{{ $bill?->supplier?->name ?: 'No supplier' }}</p>
                                </td>
                                <td>{{ $method }}</td>
                                <td>
                                    <span class="{{ $payment->reconciled ? 'erp-badge-success' : 'erp-badge-warning' }}">
                                        {{ $payment->reconciled ? 'Matched' : 'Open' }}
                                    </span>
                                </td>
                                <td class="is-right"><span class="erp-table-num">{{ number_format((float) $payment->amount, 2) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-admin.empty-state title="No payments in this period" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-report.page>
@endsection
