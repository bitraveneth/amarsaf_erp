@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit order #{{ $order->id }}</h1>
                <p>{{ $order->agent->name }} · {{ ucfirst($order->order_type) }}</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="button-secondary">Back to orders</a>
        </header>

        <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="form-form">
            @csrf
            @method('PATCH')

            <div class="form-section">
                <div class="section-header">
                    <h3>Planning</h3>
                    <p>Adjust delivery date and internal notes. Line items and agent are managed from the original order.</p>
                </div>
                <div class="form-grid">
                    <div>
                        <label>Agent</label>
                        <p class="panel-note">{{ $order->agent->name }}</p>
                    </div>
                    <div>
                        <label for="delivery_date">Delivery date</label>
                        <input id="delivery_date" name="delivery_date" type="date"
                               value="{{ optional($order->delivery_date)->format('Y-m-d') }}">
                        @error('delivery_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="full-width">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes">{{ old('notes', $order->notes) }}</textarea>
                        @error('notes') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Current items (read-only)</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit price</th>
                            <th>Line total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php $lineTotal = $item->quantity * $item->unit_price; @endphp
                            <tr>
                                <td>{{ $item->product->sku ?? '—' }}</td>
                                <td>{{ $item->product->name ?? '—' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ number_format($lineTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <button type="submit">Save changes</button>
            </div>
        </form>
    </section>
</div>
@endsection

