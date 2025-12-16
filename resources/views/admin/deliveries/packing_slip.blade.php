@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Packing slip · Delivery #{{ $delivery->id }}</h1>
                <p>Order #{{ $delivery->order->id }} · {{ $delivery->order->agent->name }}</p>
                <p class="text-muted">
                    @if($delivery->sequence)
                        Stop #{{ $delivery->sequence }} ·
                    @endif
                    Route: {{ $delivery->route->name ?? '—' }} ·
                    Vehicle: {{ $delivery->vehicle->name ?? '—' }}
                </p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.deliveries.index') }}" class="button-secondary">Back to deliveries</a>
                <form action="{{ route('admin.deliveries.destroy', $delivery) }}" method="POST" class="inline-form" onsubmit="return confirm('Delete this delivery?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button-secondary">Delete delivery</button>
                </form>
            </div>
        </header>

        <table class="data-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($delivery->order->items as $item)
                    <tr>
                        <td>{{ $item->product->sku ?? '—' }}</td>
                        <td>{{ $item->product->name ?? '—' }}</td>
                        <td>{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
@endsection
