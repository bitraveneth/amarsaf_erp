@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Fleet schedule</h1>
                <p>Vehicle and route assignments for {{ $date->format('Y-m-d') }}.</p>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <form action="{{ route('admin.vehicle-schedule.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="scheduled_date">Date</label>
                <input id="scheduled_date" name="scheduled_date" type="date" value="{{ $date->format('Y-m-d') }}" required>
            </div>
            <div>
                <label for="vehicle_id">Vehicle</label>
                <select id="vehicle_id" name="vehicle_id" required>
                    <option value="">Select</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->license_plate ?? '—' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="route_id">Route (optional)</label>
                <select id="route_id" name="route_id">
                    <option value="">None</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}">{{ $route->name }} ({{ $route->zone ?? '—' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="driver">Driver</label>
                <input id="driver" name="driver">
            </div>
            <div class="full-width">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit">Save schedule</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Schedules for {{ $date->format('Y-m-d') }}</h1>
                <p>Planned vehicles and routes.</p>
            </div>
        </header>

        @if($schedules->isEmpty())
            <p class="panel-note">No schedules recorded for this date.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Route</th>
                        <th>Driver</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->vehicle->name }}</td>
                            <td>{{ $schedule->route->name ?? '—' }}</td>
                            <td>{{ $schedule->driver ?? '—' }}</td>
                            <td>{{ $schedule->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Deliveries for {{ $date->format('Y-m-d') }}</h1>
                <p>Sequenced drops by vehicle and route.</p>
            </div>
        </header>

        @if($deliveries->isEmpty())
            <p class="panel-note">No deliveries created for this date.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Route</th>
                        <th>Seq</th>
                        <th>Order</th>
                        <th>Agent</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliveries as $delivery)
                        <tr>
                            <td>{{ $delivery->vehicle->name ?? '—' }}</td>
                            <td>{{ $delivery->route->name ?? '—' }}</td>
                            <td>{{ $delivery->sequence ?? '—' }}</td>
                            <td>#{{ $delivery->order_id }}</td>
                            <td>{{ $delivery->order->agent->name ?? '—' }}</td>
                            <td>{{ ucfirst($delivery->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</div>
@endsection
