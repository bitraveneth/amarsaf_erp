@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add vehicle</h1>
                <p>Register a truck/van for scheduling and load planning.</p>
            </div>
            <a href="{{ route('admin.vehicles.index') }}" class="button-secondary">Back to vehicles</a>
        </header>

        <form action="{{ route('admin.vehicles.store') }}" method="POST" class="form-form">
            @csrf
            <div class="form-section">
                <div class="section-header">
                    <h3>Vehicle details</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="name">Name</label>
                        <input id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div>
                        <label for="type">Type</label>
                        <input id="type" name="type" value="{{ old('type', 'truck') }}">
                    </div>
                    <div>
                        <label for="license_plate">License plate</label>
                        <input id="license_plate" name="license_plate" value="{{ old('license_plate') }}">
                    </div>
                    <div>
                        <label for="driver">Default driver</label>
                        <input id="driver" name="driver" value="{{ old('driver') }}">
                    </div>
                    <div>
                        <label for="capacity_crates">Capacity (crates)</label>
                        <input id="capacity_crates" name="capacity_crates" type="number" min="0" value="{{ old('capacity_crates') }}">
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit">Save vehicle</button>
            </div>
        </form>
    </section>
</div>
@endsection

