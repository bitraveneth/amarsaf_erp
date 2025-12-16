@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Order #{{ $order->id }}</h1>
                <p>{{ $order->agent->name }} · {{ ucfirst($order->order_type) }} · {{ ucfirst($order->status) }}</p>
                <p class="text-muted">
                    Delivery: {{ optional($order->delivery_date)->format('Y-m-d') ?? 'TBD' }} ·
                    Total: {{ number_format($order->total, 2) }} ·
                    Commission: {{ number_format($order->commission_total ?? 0, 2) }}
                </p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.orders.index') }}" class="button-secondary">Back to orders</a>
                <form action="{{ route('admin.orders.status.update', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <select name="status">
                        @foreach(['draft','confirmed','packed','dispatched','delivered'] as $status)
                            <option value="{{ $status }}"{{ $order->status === $status ? ' selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="button-secondary">Update status</button>
                </form>
                @if($order->status === 'delivered')
                    <form action="{{ route('admin.orders.invoice', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="button-secondary">Create invoice</button>
                    </form>
                @endif
                <a href="{{ route('admin.orders.picking-list', $order) }}" class="button-secondary">Picking list</a>
            </div>
        </header>

        <table class="data-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Tax class</th>
                    <th>Qty</th>
                    <th>Unit price</th>
                    <th>Line total</th>
                    <th>Commission</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    @php
                        $lineTotal = $item->quantity * $item->unit_price;
                    @endphp
                    <tr>
                        <td>{{ $item->product->sku ?? '—' }}</td>
                        <td>{{ $item->product->name ?? '—' }}</td>
                        <td>
                            @if($item->product && $item->product->taxClass)
                                {{ $item->product->taxClass->name }} ({{ $item->product->taxClass->rate }}%)
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($lineTotal, 2) }}</td>
                        <td>
                            {{ number_format($item->commission_amount ?? 0, 2) }}
                            @if($item->commission_rate)
                                <span class="text-muted">({{ number_format($item->commission_rate, 2) }}%)</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($order->statusHistory->isNotEmpty())
            <div class="form-section">
                <div class="section-header">
                    <h3>Status history</h3>
                </div>
                <ul class="activity-list">
                    @foreach($order->statusHistory->sortBy('changed_at') as $entry)
                        <li>
                            <strong>{{ ucfirst($entry->status) }}</strong>
                            <p class="activity-action">Changed at {{ $entry->changed_at->format('Y-m-d H:i') }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($order->notes)
            <p class="panel-note">Notes: {{ $order->notes }}</p>
        @endif
    </section>
</div>
@endsection
