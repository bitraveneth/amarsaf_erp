@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Finance</h1>
                <p>Invoices, VAT, and ledger snapshots.</p>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($invoices->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Order</th>
                        <th>Agent</th>
                        <th>Net</th>
                        <th>VAT</th>
                        <th>Withholding</th>
                        <th>Status</th>
                        <th>Receipts</th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->number }}</td>
                            <td>{{ $invoice->order_id ? '#'.$invoice->order_id : '—' }}</td>
                            <td>{{ $invoice->order?->agent->name ?? '—' }}</td>
                            <td>{{ number_format($invoice->net_total,2) }}</td>
                            <td>{{ number_format($invoice->vat_amount,2) }}</td>
                            <td>{{ number_format($invoice->withholding,2) }}</td>
                            <td>{{ ucfirst($invoice->status) }}</td>
                            <td>{{ number_format($invoice->receipts()->sum('amount'), 2) }}</td>
                            <td>
                                @if($invoice->status !== 'paid')
                                    <form action="{{ route('admin.finance.receipts.store', $invoice) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="amount" value="{{ max(0, ($invoice->net_total + $invoice->vat_amount) - $invoice->receipts()->sum('amount')) }}">
                                        <button type="submit" class="button-secondary">Mark paid</button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.finance.credit-notes.create', $invoice) }}">Credit note</a>
                            </td>
                            <td>
                                <a href="{{ route('admin.finance.show', $invoice) }}" class="button-secondary">View</a>
                                <form action="{{ route('admin.finance.destroy', $invoice) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this invoice? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $invoices->links() }}
        @else
            <p class="panel-note">No invoices yet.</p>
        @endif
    </section>
</div>
@endsection
