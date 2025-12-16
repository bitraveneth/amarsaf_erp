@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Credit note · {{ $invoice->number }}</h1>
                <p>Order #{{ $invoice->order_id }} · {{ $invoice->order?->agent->name ?? '—' }}</p>
            </div>
            <a href="{{ route('admin.finance.index') }}" class="button-secondary">Back to finance</a>
        </header>

        <form action="{{ route('admin.finance.credit-notes.store', $invoice) }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label>Invoice total (incl. VAT)</label>
                <p class="panel-note">{{ number_format($invoice->net_total + $invoice->vat_amount, 2) }}</p>
            </div>
            <div>
                <label for="amount">Credit amount</label>
                <input id="amount" name="amount" type="number" step="0.01" min="0.01" required>
                @error('amount') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="full-width">
                <label for="reason">Reason</label>
                <textarea id="reason" name="reason"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit">Create credit note</button>
            </div>
        </form>
    </section>
</div>
@endsection

