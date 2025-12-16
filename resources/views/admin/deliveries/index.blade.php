@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Deliveries</h1>
                <p>Monitor vehicle assignments and proof of delivery.</p>
            </div>
            <a href="{{ route('admin.deliveries.create') }}" class="button-primary">Schedule delivery</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <form action="{{ route('admin.deliveries.optimize') }}" method="POST" class="form-grid" style="margin-bottom:1rem;">
            @csrf
            <div>
                <label for="opt_date">Optimize date</label>
                <input id="opt_date" name="date" type="date" value="{{ now()->toDateString() }}">
            </div>
            <div>
                <label for="opt_vehicle">Vehicle (optional)</label>
                <select id="opt_vehicle" name="vehicle_id">
                    <option value="">All vehicles</option>
                    @php($vehicles = \App\Models\Vehicle::orderBy('name')->get())
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="button-secondary">Auto-sequence deliveries</button>
            </div>
        </form>

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
                        <th>POD</th>
                        <th></th>
                        <th></th>
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
                            <td>{{ $delivery->pod_photo ? 'Uploaded' : 'Pending' }}</td>
                            <td>
                                <form action="{{ route('admin.deliveries.update', $delivery) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status">
                                        @foreach(['scheduled','in_transit','delivered','exception'] as $state)
                                            <option value="{{ $state }}"{{ $delivery->status === $state ? ' selected' : '' }}>
                                                {{ ucfirst($state) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('admin.deliveries.packing-slip', $delivery) }}" class="button-secondary">Packing slip</a>
                            </td>
                            <td>
                                <form action="{{ route('admin.deliveries.destroy', $delivery) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this delivery?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $deliveries->links() }}
        @else
            <p class="panel-note">No deliveries scheduled yet.</p>
        @endif
    </section>
</div>
@endsection
