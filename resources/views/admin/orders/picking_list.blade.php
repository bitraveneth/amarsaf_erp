@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Picking list · Order #{{ $order->id }}</h1>
                <p>{{ $order->agent->name }} · Delivery {{ optional($order->delivery_date)->format('Y-m-d') ?? 'TBD' }}</p>
            </div>
            <a href="{{ route('admin.orders.show', $order) }}" class="button-secondary">Back to order</a>
        </header>

        <table class="data-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Warehouse</th>
                    <th>Batch (if any)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $line)
                    <tr>
                        <td>{{ $line['sku'] }}</td>
                        <td>{{ $line['name'] }}</td>
                        <td>{{ $line['quantity'] }}</td>
                        <td>{{ $line['warehouse'] ?? '—' }}</td>
                        <td>{{ $line['batch'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
@endsection

