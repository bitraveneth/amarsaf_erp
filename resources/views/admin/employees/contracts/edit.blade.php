@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit contract for {{ $employee->name }}</h1>
                <p>Update contract period, schedule, and salary details.</p>
            </div>
            <a href="{{ route('admin.employees.contracts.index', $employee) }}" class="button-secondary">Back to contracts</a>
        </header>

        <form action="{{ route('admin.employees.contracts.update', [$employee, $contract]) }}" method="POST" class="form-form">
            @csrf
            @method('PATCH')
            @include('admin.employees.contracts.partials.form', ['contract' => $contract])
            <div class="form-actions">
                <button type="submit">Update contract</button>
            </div>
        </form>
    </section>
</div>
@endsection

