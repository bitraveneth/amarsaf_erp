@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Vehicles</h1>
                <p>Maintain fleet trucks/vans for deliveries.</p>
            </div>
            <a href="{{ route('admin.vehicles.create') }}" class="button-primary">Add vehicle</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($vehicles->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Plate</th>
                        <th>Driver</th>
                        <th>Capacity (crates)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicles as $vehicle)
                        <tr>
                            <td>{{ $vehicle->name }}</td>
                            <td>{{ $vehicle->type ?? '—' }}</td>
                            <td>{{ $vehicle->license_plate ?? '—' }}</td>
                            <td>{{ $vehicle->driver ?? '—' }}</td>
                            <td>{{ $vehicle->capacity_crates ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $vehicles->links() }}
        @else
            <p class="panel-note">No vehicles added yet.</p>
        @endif
    </section>
</div>
@endsection

