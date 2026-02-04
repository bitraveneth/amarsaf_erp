@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Customer returns</h1>
                <p>List of items returned by customers and added back to stock.</p>
            </div>
            <a href="{{ route('admin.returns.customer.create') }}" class="button-primary">Record return</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($returns->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order</th>
                        <th>Agent</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Quantity</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returns as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('Y-m-d') }}</td>
                            <td>{{ $movement->order ? '#'.$movement->order->id : '—' }}</td>
                            <td>{{ $movement->order?->agent?->name ?? '—' }}</td>
                            <td>{{ $movement->stockEntry->product->name }}</td>
                            <td>{{ $movement->stockEntry->warehouse->name }}</td>
                            <td>{{ number_format($movement->quantity, 2) }}</td>
                            <td>{{ $movement->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $returns->links() }}
        @else
            <p class="panel-note">No customer returns recorded yet.</p>
        @endif
    </section>
</div>
@endsection

