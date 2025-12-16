@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Picking lists</h1>
                <p>Orders that are ready to be picked (confirmed/packed).</p>
            </div>
        </header>

        @if($orders->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Agent</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->agent->name }}</td>
                            <td>{{ optional($order->delivery_date)->format('Y-m-d') ?? 'TBD' }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td>{{ number_format($order->total, 2) }}</td>
                            <td>
                                <a href="{{ route('admin.orders.picking-list', $order) }}" class="button-secondary">
                                    View picking list
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $orders->links() }}
        @else
            <p class="panel-note">No confirmed or packed orders waiting to be picked.</p>
        @endif
    </section>
</div>
@endsection

