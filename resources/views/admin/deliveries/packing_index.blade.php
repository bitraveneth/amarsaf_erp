@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Packing slips</h1>
                <p>Print packing slips for scheduled and in-transit deliveries.</p>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($deliveries->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Seq</th>
                        <th>Order</th>
                        <th>Agent</th>
                        <th>Route</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliveries as $delivery)
                        <tr>
                            <td>{{ $delivery->sequence ?? '—' }}</td>
                            <td>#{{ $delivery->order_id }}</td>
                            <td>{{ $delivery->order->agent->name ?? '—' }}</td>
                            <td>{{ $delivery->route->name ?? '—' }}</td>
                            <td>{{ $delivery->vehicle->name ?? '—' }}</td>
                            <td>{{ ucfirst($delivery->status) }}</td>
                            <td>
                                <a href="{{ route('admin.deliveries.packing-slip', $delivery) }}" class="button-secondary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $deliveries->links() }}
        @else
            <p class="panel-note">No deliveries available for packing.</p>
        @endif
    </section>
</div>
@endsection
