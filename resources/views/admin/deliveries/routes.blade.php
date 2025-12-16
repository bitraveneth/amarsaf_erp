@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Delivery routes</h1>
                <p>Plan zones and days for van schedules.</p>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <form action="{{ route('admin.delivery-routes.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="name">Route name</label>
                <input id="name" name="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label for="zone">Zone / area</label>
                <input id="zone" name="zone" value="{{ old('zone') }}" placeholder="e.g. Dhaka North">
            </div>
            <div>
                <label for="day">Day pattern</label>
                <input id="day" name="day" value="{{ old('day') }}" placeholder="e.g. Mon/Wed/Fri">
            </div>
            <div>
                <label for="vehicle_id">Preferred vehicle</label>
                <select id="vehicle_id" name="vehicle_id">
                    <option value="">Unassigned</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}"{{ old('vehicle_id') == $vehicle->id ? ' selected' : '' }}>
                            {{ $vehicle->name }} · {{ $vehicle->license_plate ?? '—' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="full-width">
                <label for="driver">Default driver</label>
                <input id="driver" name="driver" value="{{ old('driver') }}" placeholder="Optional driver name">
            </div>
            <div class="form-actions">
                <button type="submit">Save route</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h2>Defined routes</h2>
                <p>Routes with zones, days, and preferred vehicles.</p>
            </div>
        </header>

        @if($routes->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Zone</th>
                        <th>Day</th>
                        <th>Vehicle</th>
                        <th>Driver</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($routes as $route)
                        <tr>
                            <td>{{ $route->name }}</td>
                            <td>{{ $route->zone ?? '—' }}</td>
                            <td>{{ $route->day ?? '—' }}</td>
                            <td>{{ $route->vehicle->name ?? 'Unassigned' }}</td>
                            <td>{{ $route->driver ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="panel-note">No routes defined yet. Add your first route above.</p>
        @endif
    </section>
</div>
@endsection

