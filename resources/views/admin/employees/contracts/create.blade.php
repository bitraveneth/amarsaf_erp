@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add contract for {{ $employee->name }}</h1>
                <p>Define contract period, schedule, and salary details.</p>
            </div>
            <a href="{{ route('admin.employees.contracts.index', $employee) }}" class="button-secondary">Back to contracts</a>
        </header>

        <form action="{{ route('admin.employees.contracts.store', $employee) }}" method="POST" class="form-form">
            @csrf
            @include('admin.employees.contracts.partials.form', ['contract' => $contract])
            <div class="form-actions">
                <button type="submit">Save contract</button>
            </div>
        </form>
    </section>
</div>
@endsection

