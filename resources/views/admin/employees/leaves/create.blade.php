@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add leave request for {{ $employee->name }}</h1>
                <p>Record a new leave application.</p>
            </div>
            <a href="{{ route('admin.employees.leaves.index', $employee) }}" class="button-secondary">Back to leave requests</a>
        </header>

        <form action="{{ route('admin.employees.leaves.store', $employee) }}" method="POST" class="form-form">
            @csrf
            @include('admin.employees.leaves.partials.form', ['leave' => $leave])
            <div class="form-actions">
                <button type="submit">Save leave request</button>
            </div>
        </form>
    </section>
</div>
@endsection

