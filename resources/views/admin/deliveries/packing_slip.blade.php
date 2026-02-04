@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Packing slip · Delivery #{{ $delivery->id }}</h1>
                <p>Order #{{ $delivery->order->id }} · {{ $delivery->order->agent->name }}</p>
                <p class="text-muted">
                    @php
                        $order = $delivery->order;
                        $agent = $order?->agent;
                        $deliveryDate = $order?->delivery_date ?? $delivery->created_at;
                    @endphp
                    Date: {{ optional($deliveryDate)->format('Y-m-d') }} ·
                    Status: {{ ucfirst($delivery->status) }}
                </p>
            </div>
            <div class="button-group">
                <button type="button" class="button-secondary" onclick="window.print()">Print</button>
                <a href="{{ route('admin.deliveries.index') }}" class="button-secondary">Back to deliveries</a>
                <form action="{{ route('admin.deliveries.destroy', $delivery) }}" method="POST" class="inline-form" onsubmit="return confirm('Delete this delivery?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button-secondary">Delete delivery</button>
                </form>
            </div>
        </header>

        <div class="master-grid" style="margin-bottom: 1.5rem;">
            <article>
                <h3>Ship to</h3>
                <p>
                    {{ $agent->name ?? '—' }}<br>
                    @if($agent?->area)
                        Area: {{ $agent->area }}<br>
                    @endif
                    @if($agent?->zone)
                        Zone: {{ $agent->zone }}<br>
                    @endif
                    @if($agent?->phone)
                        Phone: {{ $agent->phone }}<br>
                    @endif
                    @if($agent?->location_code)
                        Code: {{ $agent->location_code }}<br>
                    @endif
                </p>
            </article>
            <article>
                <h3>Route & vehicle</h3>
                <p>
                    Route: {{ $delivery->route->name ?? '—' }}<br>
                    Vehicle: {{ $delivery->vehicle->name ?? '—' }}<br>
                    Driver: {{ $delivery->vehicle->driver ?? $delivery->route->driver ?? '—' }}<br>
                    @if($delivery->sequence)
                        Stop #: {{ $delivery->sequence }}<br>
                    @endif
                </p>
            </article>
        </div>

        <h2>Items to pack</h2>
        @php
            $totalQty = 0;
        @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Pack</th>
                </tr>
            </thead>
            <tbody>
                @foreach($delivery->order->items as $item)
                    @php
                        $totalQty += $item->quantity;
                    @endphp
                    <tr>
                        <td>{{ $item->product->sku ?? '—' }}</td>
                        <td>{{ $item->product->name ?? '—' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->product->packagingType->name ?? $item->product->size ?? '—' }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2"><strong>Total</strong></td>
                    <td><strong>{{ $totalQty }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 2rem; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.5rem;">
            <div>
                <hr>
                <p class="text-muted">Picker signature</p>
            </div>
            <div>
                <hr>
                <p class="text-muted">Checker signature</p>
            </div>
            <div>
                <hr>
                <p class="text-muted">Driver signature</p>
            </div>
        </div>
    </section>
</div>
@endsection
