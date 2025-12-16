@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Orders</h1>
                <p>Track agent orders and their current statuses.</p>
            </div>
            <a href="{{ route('admin.orders.create') }}" class="button-primary">New order</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($orders->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Type</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Commission</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}">
                                    {{ $order->agent->name }}
                                </a>
                            </td>
                            <td>{{ ucfirst($order->order_type) }}</td>
                            <td>{{ optional($order->delivery_date)->format('Y-m-d') ?? 'TBD' }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td>{{ number_format($order->total, 2) }}</td>
                            <td>{{ number_format($order->commission_total ?? 0, 2) }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="button-secondary">
                                    View
                                </a>
                                <a href="{{ route('admin.orders.edit', $order) }}" class="button-secondary">
                                    Edit
                                </a>
                                <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this order? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $orders->links() }}
        @else
            <p class="panel-note">No orders yet. Tap “New order” to create one.</p>
        @endif
    </section>
</div>
@endsection
