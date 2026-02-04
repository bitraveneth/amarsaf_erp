@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Assign equipment to {{ $employee->name }}</h1>
                <p>Record a device or item for this employee.</p>
            </div>
            <a href="{{ route('admin.employees.equipment.index', $employee) }}" class="button-secondary">Back to equipment</a>
        </header>

        <form action="{{ route('admin.employees.equipment.store', $employee) }}" method="POST" class="form-form">
            @csrf
            @include('admin.employees.equipment.partials.form', ['item' => $item])
            <div class="form-actions">
                <button type="submit">Save equipment</button>
            </div>
        </form>
    </section>
</div>
@endsection

