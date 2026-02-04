@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add location for {{ $employee->name }}</h1>
                <p>Record a new location entry (e.g. dealer visit, route stop).</p>
            </div>
            <a href="{{ route('admin.employees.locations.index', $employee) }}" class="button-secondary">Back to locations</a>
        </header>

        <form action="{{ route('admin.employees.locations.store', $employee) }}" method="POST" class="form-form">
            @csrf
            @include('admin.employees.locations.partials.form', ['log' => $log])
            <div class="form-actions">
                <button type="submit">Save location</button>
            </div>
        </form>
    </section>
</div>
@endsection

