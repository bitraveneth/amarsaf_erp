@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Schedule delivery</h1>
                <p>Attach a route/vehicle to an order and upload POD after delivery.</p>
            </div>
            <a href="{{ route('admin.deliveries.index') }}" class="button-secondary">Back to deliveries</a>
        </header>

        <form action="{{ route('admin.deliveries.store') }}" method="POST" class="form-form" enctype="multipart/form-data">
            @csrf
            <div class="form-section">
                <div class="section-header">
                    <h3>Delivery details</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="order_id">Order</label>
                        <select id="order_id" name="order_id" required>
                            <option value="">Select order</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}"{{ old('order_id') == $order->id ? ' selected' : '' }}>
                                    #{{ $order->id }} · {{ $order->agent->name ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="route_id">Delivery route</label>
                        <select id="route_id" name="route_id">
                            <option value="">Select route</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}"{{ old('route_id') == $route->id ? ' selected' : '' }}>
                                    {{ $route->name }} ({{ $route->zone ?? '—' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="vehicle_id">Vehicle</label>
                        <select id="vehicle_id" name="vehicle_id">
                            <option value="">Select vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}"{{ old('vehicle_id') == $vehicle->id ? ' selected' : '' }}>
                                    {{ $vehicle->name }} · {{ $vehicle->license_plate ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            @foreach(['scheduled','in_transit','delivered','exception'] as $status)
                                <option value="{{ $status }}"{{ old('status') == $status ? ' selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="full-width">
                        <label for="exception_notes">Exception notes</label>
                        <textarea id="exception_notes" name="exception_notes">{{ old('exception_notes') }}</textarea>
                    </div>
                    <div>
                        <label for="pod_photo">POD photo</label>
                        <input id="pod_photo" type="file" name="pod_photo" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit">Schedule</button>
            </div>
        </form>
    </section>
</div>
@endsection
