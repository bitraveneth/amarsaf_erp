@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit location for {{ $employee->name }}</h1>
                <p>Update timestamp, coordinates, or notes.</p>
            </div>
            <a href="{{ route('admin.employees.locations.index', $employee) }}" class="button-secondary">Back to locations</a>
        </header>

        <form action="{{ route('admin.employees.locations.update', [$employee, $log]) }}" method="POST" class="form-form">
            @csrf
            @method('PATCH')
            @include('admin.employees.locations.partials.form', ['log' => $log])
            <div class="form-actions">
                <button type="submit">Update location</button>
            </div>
        </form>
    </section>
</div>
@endsection

