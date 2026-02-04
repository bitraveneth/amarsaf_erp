@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit equipment for {{ $employee->name }}</h1>
                <p>Update device details or status.</p>
            </div>
            <a href="{{ route('admin.employees.equipment.index', $employee) }}" class="button-secondary">Back to equipment</a>
        </header>

        <form action="{{ route('admin.employees.equipment.update', [$employee, $item]) }}" method="POST" class="form-form">
            @csrf
            @method('PATCH')
            @include('admin.employees.equipment.partials.form', ['item' => $item])
            <div class="form-actions">
                <button type="submit">Update equipment</button>
            </div>
        </form>
    </section>
</div>
@endsection

