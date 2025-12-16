@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Stock movements</h1>
                <p>Review past transfers and reservations.</p>
            </div>
            <a href="{{ route('admin.stock.transfers') }}" class="button-secondary">New transfer</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($movements->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Order</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                        <tr>
                            <td>{{ $movement->stockEntry->product->name }}</td>
                            <td>{{ $movement->stockEntry->warehouse->name }}</td>
                            <td>{{ ucfirst($movement->type) }}</td>
                            <td>{{ number_format($movement->quantity, 2) }}</td>
                            <td>{{ $movement->order?->id ? '#'.$movement->order->id : '—' }}</td>
                            <td>{{ $movement->updated_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $movements->links() }}
        @else
            <p class="panel-note">No movements recorded.</p>
        @endif
    </section>
</div>
@endsection
