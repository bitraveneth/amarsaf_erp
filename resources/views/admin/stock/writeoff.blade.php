@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Stock write-off</h1>
                <p>Write off expired, wasted, or returned-to-supplier stock.</p>
            </div>
            <a href="{{ route('admin.stock.movements') }}" class="button-secondary">Back to movements</a>
        </header>

        <form action="{{ route('admin.stock.writeoff.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="entry_id">Stock entry</label>
                <select id="entry_id" name="entry_id" required>
                    <option value="">Select entry</option>
                    @foreach($entries as $entry)
                        <option value="{{ $entry->id }}">
                            {{ $entry->product->name }} · {{ number_format($entry->quantity, 2) }} {{ $entry->warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="quantity">Quantity to write off</label>
                <input id="quantity" name="quantity" type="number" step="0.01" required>
            </div>
            <div>
                <label for="reason">Reason</label>
                <select id="reason" name="reason" required>
                    <option value="expired">Expired</option>
                    <option value="wasted">Wasted/damaged</option>
                    <option value="supplier-return">Return to supplier</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label for="notes">Notes</label>
                <input id="notes" name="notes" value="{{ old('notes') }}">
            </div>
            <div class="form-actions">
                <button type="submit">Write off stock</button>
            </div>
        </form>
    </section>
</div>
@endsection

