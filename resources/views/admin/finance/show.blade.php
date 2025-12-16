@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Invoice {{ $invoice->number }}</h1>
                <p>Order #{{ $invoice->order_id ?? '—' }} · {{ $invoice->order?->agent->name ?? 'Unassigned' }}</p>
                <p class="text-muted">
                    Status: {{ ucfirst($invoice->status) }} · Issued {{ optional($invoice->issued_at)->format('Y-m-d') }}
                </p>
            </div>
            <a href="{{ route('admin.finance.index') }}" class="button-secondary">Back to finance</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <div class="master-grid">
            <article>
                <p class="metric-label">Net total</p>
                <h3>{{ number_format($invoice->net_total, 2) }}</h3>
            </article>
            <article>
                <p class="metric-label">VAT</p>
                <h3>{{ number_format($invoice->vat_amount, 2) }}</h3>
            </article>
            <article>
                <p class="metric-label">Withholding</p>
                <h3>{{ number_format($invoice->withholding, 2) }}</h3>
            </article>
            <article>
                <p class="metric-label">Receipts</p>
                <h3>{{ number_format($invoice->receipts->sum('amount'), 2) }}</h3>
            </article>
        </div>

        <h2>Items</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit price</th>
                    <th>Line total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->product->sku ?? '—' }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Receipts</h2>
        @if($invoice->receipts->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->receipts as $receipt)
                        <tr>
                            <td>{{ optional($receipt->received_at)->format('Y-m-d') }}</td>
                            <td>{{ number_format($receipt->amount, 2) }}</td>
                            <td>{{ $receipt->payment_method ?? '—' }}</td>
                            <td>
                                <form action="{{ route('admin.finance.receipts.destroy', $receipt) }}" method="POST" class="inline-form" onsubmit="return confirm('Delete this receipt?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="panel-note">No receipts recorded yet.</p>
        @endif

        <h2>Credit notes</h2>
        @if($invoice->creditNotes->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->creditNotes as $credit)
                        <tr>
                            <td>{{ $credit->number }}</td>
                            <td>{{ optional($credit->issued_at)->format('Y-m-d') }}</td>
                            <td>{{ number_format($credit->amount, 2) }}</td>
                            <td>{{ $credit->reason ?? '—' }}</td>
                            <td>
                                <form action="{{ route('admin.finance.credit-notes.destroy', $credit) }}" method="POST" class="inline-form" onsubmit="return confirm('Delete this credit note?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="panel-note">No credit notes for this invoice.</p>
        @endif
    </section>
</div>
@endsection
