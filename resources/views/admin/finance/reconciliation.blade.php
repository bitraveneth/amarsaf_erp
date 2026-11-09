@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $periodLabel = $from->format('d M Y').' – '.$to->format('d M Y');
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
    $periodQuery = [
        'range' => $range,
        'from' => $from->toDateString(),
        'to' => $to->toDateString(),
    ];
    $sampleUrl = route('admin.finance.reconciliation.sample', $periodQuery);
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
                        Click Reconcile on a receipt or payment when it appears on the bank statement.
                        That confirms cash in the ERP matches the bank. It does not post a new journal.
                    </x-admin.help-tip>
                </div>
                <p class="erp-page-subtitle">Match the bank statement to money already recorded in Saf ERP — one line at a time.</p>
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
            <p class="erp-eyebrow">1. Import (optional)</p>
            <p class="erp-caption mt-1">Upload a bank CSV to highlight likely matches.</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <p class="erp-eyebrow">2. Reconcile each line</p>
            <p class="erp-caption mt-1">Use the button on each row when you see it on the bank.</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <p class="erp-eyebrow">3. Done</p>
            <p class="erp-caption mt-1">No batch save needed — each click updates immediately.</p>
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
                <p class="erp-caption mt-1">Optional. CSV from your bank, or a demo file built from this company’s unreconciled lines.</p>
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
                    <a href="{{ $sampleUrl }}" class="erp-btn-secondary">Download demo statement</a>
                    <button type="submit" class="erp-btn-primary">Import &amp; match</button>
                </div>
            </form>
            @error('statement')
                <p class="erp-form-error">{{ $message }}</p>
            @enderror
        </div>
    </section>

    @if(!empty($importResults))
        <section class="erp-dash-panel">
            <div class="erp-dash-panel__header">
                <div class="flex items-center gap-2">
                    <h2 class="erp-h3">Import result</h2>
                    <x-admin.help-tip label="Suggested match">
                        Suggested rows are marked below. Click Reconcile on each row to confirm — nothing is saved until you click.
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
            hint="Click Reconcile on those rows"
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
            description="No receipts or supplier payments in this date range. Try Last 3 months or Lifetime, or download the demo statement anyway."
        >
            <x-slot:action>
                <a href="{{ route('admin.finance.reconciliation', ['range' => '3m']) }}" class="erp-btn-primary">Show last 3 months</a>
            </x-slot:action>
        </x-admin.empty-state>
    @else
        <div class="space-y-6">
            <div class="erp-table-card">
                <div class="erp-table-card-header flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="erp-table-card-title">Customer receipts</h2>
                            <x-admin.help-tip class="erp-help-tip--up" label="Reconcile button">
                                Click Reconcile when this receipt appears as a credit on the bank statement.
                                Undo if you marked it by mistake.
                            </x-admin.help-tip>
                        </div>
                        <p class="erp-table-card-description">{{ $pendingCount }} pending in this period</p>
                    </div>
                </div>
                <div class="erp-table-wrap">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th class="is-right">Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th class="is-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receipts as $receipt)
                                @php
                                    $isSuggested = in_array($receipt->id, $suggestedReceiptIds, true);
                                @endphp
                                <tr>
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
                                    <td class="is-right">
                                        <form method="POST" action="{{ route('admin.finance.reconciliation.receipt', $receipt) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="range" value="{{ $range }}">
                                            <input type="hidden" name="from" value="{{ $from->toDateString() }}">
                                            <input type="hidden" name="to" value="{{ $to->toDateString() }}">
                                            @if($receipt->reconciled)
                                                <button type="submit" class="erp-btn-secondary erp-btn--sm">Undo</button>
                                            @else
                                                <button type="submit" class="erp-btn-primary erp-btn--sm">Reconcile</button>
                                            @endif
                                        </form>
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
                        <x-admin.help-tip class="erp-help-tip--up" label="Payment reconcile">
                            Click Reconcile when this bill payment appears as a debit on the bank statement.
                        </x-admin.help-tip>
                    </div>
                    <p class="erp-table-card-description">{{ $pendingPaymentCount }} pending outflow(s)</p>
                </div>
                <div class="erp-table-wrap">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Bill</th>
                                <th>Supplier</th>
                                <th class="is-right">Amount</th>
                                <th>Status</th>
                                <th class="is-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($billPayments as $payment)
                                @php
                                    $isSuggestedPayment = in_array($payment->id, $suggestedPaymentIds, true);
                                @endphp
                                <tr>
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
                                    <td class="is-right">
                                        <form method="POST" action="{{ route('admin.finance.reconciliation.payment', $payment) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="range" value="{{ $range }}">
                                            <input type="hidden" name="from" value="{{ $from->toDateString() }}">
                                            <input type="hidden" name="to" value="{{ $to->toDateString() }}">
                                            @if($payment->reconciled)
                                                <button type="submit" class="erp-btn-secondary erp-btn--sm">Undo</button>
                                            @else
                                                <button type="submit" class="erp-btn-primary erp-btn--sm">Reconcile</button>
                                            @endif
                                        </form>
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

            <p class="erp-caption">Showing {{ $periodLabel }}. Each Reconcile / Undo click saves that line immediately.</p>
        </div>
    @endif

    <x-admin.quick-guide
        title="How bank reconciliation works"
        lead="Confirm that money already in Saf ERP also appears on the bank statement. This does not post a new journal."
        :steps="[
            ['title' => 'Pick the period', 'body' => 'Show the same dates as the bank statement you are checking.'],
            ['title' => 'Optional: import CSV', 'body' => 'Upload the bank file to mark Suggested matches on receipts and payments.'],
            ['title' => 'Click Reconcile on each line', 'body' => 'When you see that receipt or payment on the bank, click Reconcile — it saves immediately.'],
            ['title' => 'Undo if needed', 'body' => 'Marked by mistake? Click Undo on that row.'],
        ]"
        :examples="[
            [
                'eyebrow' => 'Receipts',
                'title' => 'Money in',
                'bullets' => [
                    'Customer receipt on the list = credit on the bank',
                    'Suggested badge means CSV matched amount/date',
                    'Status becomes Reconciled after you click',
                ],
            ],
            [
                'eyebrow' => 'Payments',
                'title' => 'Money out',
                'bullets' => [
                    'Supplier bill payment = debit on the bank',
                    'CSV uses a negative amount for outflows',
                    'Bank charges with no ERP match stay unmatched',
                ],
            ],
        ]"
        note="You do not need a full batch save. Reconcile one line when you see it — useful for daily work, not only month-end."
        :open="false"
    />
</div>
@endsection
