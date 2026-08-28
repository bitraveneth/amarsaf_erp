@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $periodLabel = $from->format('d M Y').' – '.$to->format('d M Y');
    $totalReceipts = $receipts->count();
    $reconciledCount = $receipts->where('reconciled', true)->count();
    $pendingCount = $receipts->where('reconciled', false)->count();
    $pendingAmount = (float) $receipts->where('reconciled', false)->sum('amount');
    $reconciledAmount = (float) $receipts->where('reconciled', true)->sum('amount');
    $pendingPaymentCount = $billPayments->where('reconciled', false)->count();
    $suggestedReceiptIds = $suggestedReceiptIds ?? [];
    $suggestedPaymentIds = $suggestedPaymentIds ?? [];
    $suggestedCount = count($suggestedReceiptIds) + count($suggestedPaymentIds);
    $importPreview = $importResults['preview'] ?? [];
    $isEmpty = $receipts->isEmpty() && $billPayments->isEmpty();
    $sampleUrl = route('admin.finance.reconciliation.sample', [
        'range' => $range,
        'from' => $from->toDateString(),
        'to' => $to->toDateString(),
    ]);
@endphp

<div class="space-y-6" data-tour="reconciliation-overview-header">
    <div class="erp-page-header">
        <div class="flex items-start gap-3">
            <x-admin.page-icon name="ledger" />
            <div>
                <p class="erp-eyebrow">Accounting</p>
                <div class="mt-1 flex items-center gap-2">
                    <h1 class="erp-h1">Bank reconciliation</h1>
                    <x-admin.help-tip label="What is bank reconciliation?">
                        Tick receipts and supplier payments that appear on the bank statement.
                        That confirms cash in the ERP matches the bank. It does not post a new journal.
                    </x-admin.help-tip>
                </div>
                <p class="erp-page-subtitle">Match the bank statement to money already recorded in Saf ERP.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-dashboard.period-filter
                variant="compact"
                :action="route('admin.finance.reconciliation')"
                :range="$range"
                :from="request('from', $from->toDateString())"
                :to="request('to', $to->toDateString())"
                :range-options="$rangeOptions"
                :period-label="$periodLabel"
            />
            <a href="{{ route('admin.reports.bank-reconciliation') }}" class="erp-btn-secondary">Report</a>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <p class="erp-eyebrow">1. Import</p>
            <p class="erp-caption mt-1">Upload the bank CSV, or download a demo file first.</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <p class="erp-eyebrow">2. Review</p>
            <p class="erp-caption mt-1">Saf ticks likely matches. You confirm or untick.</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <p class="erp-eyebrow">3. Save</p>
            <p class="erp-caption mt-1">Checked lines become Reconciled for this period.</p>
        </div>
    </div>

    <section class="erp-dash-panel">
        <div class="erp-dash-panel__header">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="erp-h3">Bank statement</h2>
                    <x-admin.help-tip label="CSV format">
                        Columns: Date, Amount, Reference, Description.
                        Positive amount = money in (match a receipt). Negative amount = money out (match a supplier payment).
                        Saf matches amount and a date within 3 days. Invoice or bill number in Reference improves the match.
                    </x-admin.help-tip>
                </div>
                <p class="erp-caption mt-1">CSV from your bank, or a demo file built from this company’s unreconciled lines.</p>
            </div>
        </div>
        <div class="erp-dash-panel__body space-y-4">
            <form action="{{ route('admin.finance.reconciliation.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                @csrf
                <input type="hidden" name="range" value="{{ $range }}">
                <input type="hidden" name="from" value="{{ $from->toDateString() }}">
                <input type="hidden" name="to" value="{{ $to->toDateString() }}">
                <label class="erp-form-field min-w-0 flex-1">
                    <span class="erp-label">Statement file</span>
                    <input type="file" name="statement" accept=".csv,text/csv,.txt" required class="erp-input">
                </label>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ $sampleUrl }}" class="erp-btn-secondary">
                        Download demo statement
                    </a>
                    <button type="submit" class="erp-btn-primary">Import &amp; match</button>
                </div>
            </form>
            @error('statement')
                <p class="erp-form-error">{{ $message }}</p>
            @enderror
            <p class="erp-caption">
                Demo file includes real unmatched receipts and payments, plus two bank-only lines (charge and interest) that should stay unmatched.
            </p>
        </div>
    </section>

    @if(!empty($importResults))
        <section class="erp-dash-panel">
            <div class="erp-dash-panel__header">
                <div class="flex items-center gap-2">
                    <h2 class="erp-h3">Import result</h2>
                    <x-admin.help-tip label="Suggested match">
                        A suggested match is pre-ticked below. It is not saved until you click Save.
                        Unmatched statement lines (bank charges, interest) stay on this list only.
                    </x-admin.help-tip>
                </div>
                <p class="erp-caption mt-1">
                    {{ $importResults['total_rows'] ?? 0 }} statement line(s) ·
                    {{ $importResults['matched_receipts'] ?? 0 }} receipt match(es) ·
                    {{ $importResults['matched_payments'] ?? 0 }} payment match(es) ·
                    {{ $importResults['unmatched'] ?? 0 }} unmatched
                </p>
            </div>
            @if($importPreview !== [])
                <div class="erp-table-wrap">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Direction</th>
                                <th class="is-right">Amount</th>
                                <th>Reference</th>
                                <th>Match</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($importPreview as $row)
                                <tr>
                                    <td class="erp-table-cell">{{ $row['date'] }}</td>
                                    <td class="erp-table-cell">{{ $row['direction'] === 'outflow' ? 'Out (payment)' : 'In (receipt)' }}</td>
                                    <td class="erp-table-num is-right">{{ $currencyCode }} {{ number_format((float) $row['amount'], 2) }}</td>
                                    <td class="erp-table-cell">{{ $row['reference'] ?: '—' }}</td>
                                    <td>
                                        @if(!empty($row['matched']))
                                            <span class="erp-badge-brand">{{ $row['match_label'] ?? 'Matched' }}</span>
                                        @else
                                            <span class="erp-badge-neutral">No ERP match</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

    <div class="erp-dash-kpi-grid">
        <x-admin.stat-card
            label="Pending receipts"
            :value="$pendingCount"
            :hint="$currencyCode.' '.number_format($pendingAmount, 0)"
            tone="warning"
        />
        <x-admin.stat-card
            label="Pending payments"
            :value="$pendingPaymentCount"
            hint="Supplier outflows still open"
            tone="warning"
        />
        <x-admin.stat-card
            label="Suggested from CSV"
            :value="$suggestedCount"
            hint="Pre-ticked until you save"
        />
        <x-admin.stat-card
            label="Reconciled receipts"
            :value="$reconciledCount"
            :hint="$currencyCode.' '.number_format($reconciledAmount, 0)"
            tone="success"
        />
    </div>

    @if($isEmpty)
        <x-admin.empty-state
            title="Nothing in this period"
            description="No receipts or supplier payments in this date range. Try Last 3 months or All time, or download the demo statement anyway."
        >
            <x-slot:action>
                <a href="{{ route('admin.finance.reconciliation', ['range' => '3m']) }}" class="erp-btn-primary">Show last 3 months</a>
            </x-slot:action>
        </x-admin.empty-state>
    @else
        <form id="reconciliation-form" action="{{ route('admin.finance.reconciliation.update') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="range" value="{{ $range }}">
            <input type="hidden" name="from" value="{{ $from->toDateString() }}">
            <input type="hidden" name="to" value="{{ $to->toDateString() }}">

            <div class="erp-table-card">
                <div class="erp-table-card-header flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="erp-table-card-title">Customer receipts</h2>
                            <x-admin.help-tip class="erp-help-tip--up" label="Receipt tick box">
                                Tick when this receipt appears as a credit on the bank statement.
                                Pending means it is in the ERP but not yet confirmed on the bank.
                            </x-admin.help-tip>
                        </div>
                        <p class="erp-table-card-description">{{ $pendingCount }} pending in this period</p>
                    </div>
                    <span class="erp-badge-neutral" id="selected-count">0 of {{ $totalReceipts }} selected</span>
                </div>
                <div class="erp-table-wrap">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th class="w-12">
                                    <input type="checkbox" id="select-all" class="erp-form-check-input" aria-label="Select all receipts">
                                </th>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th class="is-right">Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receipts as $receipt)
                                @php
                                    $isSuggested = in_array($receipt->id, $suggestedReceiptIds, true);
                                @endphp
                                <tr>
                                    <td>
                                        <input type="hidden" name="visible_receipts[]" value="{{ $receipt->id }}">
                                        <input
                                            type="checkbox"
                                            name="reconciled[]"
                                            value="{{ $receipt->id }}"
                                            @checked($receipt->reconciled || $isSuggested)
                                            class="receipt-checkbox erp-form-check-input"
                                            aria-label="Reconcile receipt {{ $receipt->id }}"
                                        >
                                    </td>
                                    <td class="erp-table-cell whitespace-nowrap">{{ $receipt->received_at?->format('d M Y') }}</td>
                                    <td>
                                        @if($receipt->invoice)
                                            <span class="erp-table-link">{{ $receipt->invoice->number }}</span>
                                            @if($receipt->invoice->order?->agent)
                                                <p class="erp-caption">{{ $receipt->invoice->order->agent->name }}</p>
                                            @endif
                                        @else
                                            <span class="erp-caption">—</span>
                                        @endif
                                        @if($isSuggested && ! $receipt->reconciled)
                                            <span class="erp-badge-brand mt-1">Suggested</span>
                                        @endif
                                    </td>
                                    <td class="erp-table-num is-right">{{ $currencyCode }} {{ number_format((float) $receipt->amount, 2) }}</td>
                                    <td class="erp-table-cell">{{ $receipt->payment_method ? str_replace('_', ' ', $receipt->payment_method) : '—' }}</td>
                                    <td>
                                        @if($receipt->reconciled)
                                            <span class="erp-badge-success">Reconciled</span>
                                        @else
                                            <span class="erp-badge-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8">
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
                    <div class="flex items-center gap-2">
                        <h2 class="erp-table-card-title">Supplier payments</h2>
                        <x-admin.help-tip class="erp-help-tip--up" label="Payment tick box">
                            Tick when this bill payment appears as a debit on the bank statement.
                            Outflows use a negative amount in the CSV.
                        </x-admin.help-tip>
                    </div>
                    <p class="erp-table-card-description">{{ $pendingPaymentCount }} pending outflow(s)</p>
                </div>
                <div class="erp-table-wrap">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th class="w-12">Match</th>
                                <th>Date</th>
                                <th>Bill</th>
                                <th>Supplier</th>
                                <th class="is-right">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($billPayments as $payment)
                                @php
                                    $isSuggestedPayment = in_array($payment->id, $suggestedPaymentIds, true);
                                @endphp
                                <tr>
                                    <td>
                                        <input
                                            type="checkbox"
                                            name="reconciled_payments[]"
                                            value="{{ $payment->id }}"
                                            @checked($payment->reconciled || $isSuggestedPayment)
                                            class="payment-checkbox erp-form-check-input"
                                            aria-label="Reconcile payment {{ $payment->id }}"
                                        >
                                    </td>
                                    <td class="erp-table-cell whitespace-nowrap">{{ $payment->paid_at?->format('d M Y') }}</td>
                                    <td>
                                        <span class="erp-table-link">{{ $payment->bill?->number ?? '—' }}</span>
                                        @if($isSuggestedPayment && ! $payment->reconciled)
                                            <span class="erp-badge-brand mt-1">Suggested</span>
                                        @endif
                                    </td>
                                    <td class="erp-table-cell">{{ $payment->bill?->supplier?->name ?? '—' }}</td>
                                    <td class="erp-table-num is-right">{{ $currencyCode }} {{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>
                                        @if($payment->reconciled)
                                            <span class="erp-badge-success">Reconciled</span>
                                        @else
                                            <span class="erp-badge-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8">
                                        <x-admin.empty-state title="No supplier payments in this period" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="erp-form-actions rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <p class="erp-caption mr-auto">Only lines in {{ $periodLabel }} are updated when you save.</p>
                <button type="submit" class="erp-btn-primary">Save reconciliation</button>
            </div>
        </form>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all');
        const receiptBoxes = document.querySelectorAll('.receipt-checkbox');
        const selectedCount = document.getElementById('selected-count');
        const totalReceipts = {{ $totalReceipts }};

        function updateCount() {
            if (!selectedCount) return;
            selectedCount.textContent = `${document.querySelectorAll('.receipt-checkbox:checked').length} of ${totalReceipts} selected`;
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                receiptBoxes.forEach((box) => { box.checked = selectAll.checked; });
                updateCount();
            });
        }

        receiptBoxes.forEach((box) => box.addEventListener('change', updateCount));
        updateCount();
    });
</script>
@endpush
@endsection
