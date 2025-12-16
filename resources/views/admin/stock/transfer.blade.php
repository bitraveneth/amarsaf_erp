@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Stock transfer</h1>
                <p>Move available stock between warehouses.</p>
            </div>
        </header>

        <form action="{{ route('admin.stock.transfers.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="entry_id">Source stock</label>
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
                <label for="destination_warehouse_id">Destination warehouse</label>
                <select id="destination_warehouse_id" name="destination_warehouse_id" required>
                    <option value="">Select warehouse</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="number" step="0.01" required>
            </div>
            <div>
                <label for="notes">Notes</label>
                <input id="notes" name="notes" value="{{ old('notes') }}">
            </div>
            <div class="form-actions">
                <button type="submit">Transfer stock</button>
            </div>
        </form>
    </section>
</div>
@endsection
