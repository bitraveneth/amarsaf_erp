@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit leave request for {{ $employee->name }}</h1>
                <p>Update dates, type, or approval status.</p>
            </div>
            <a href="{{ route('admin.employees.leaves.index', $employee) }}" class="button-secondary">Back to leave requests</a>
        </header>

        <form action="{{ route('admin.employees.leaves.update', [$employee, $leave]) }}" method="POST" class="form-form">
            @csrf
            @method('PATCH')
            @include('admin.employees.leaves.partials.form', ['leave' => $leave])
            <div class="form-actions">
                <button type="submit">Update leave request</button>
            </div>
        </form>
    </section>
</div>
@endsection

