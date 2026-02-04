@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add leave request</h1>
                <p>Select an employee and record a new leave application.</p>
            </div>
            <a href="{{ route('admin.leaves.index') }}" class="button-secondary">Back to leave requests</a>
        </header>

        <form action="{{ route('admin.leaves.store') }}" method="POST" class="form-form">
            @csrf
            <div class="form-section">
                <div class="section-header">
                    <h3>Employee</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="employee_id">Employee</label>
                        <select id="employee_id" name="employee_id" required>
                            <option value="">Select employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ (string)old('employee_id') === (string)$employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @php($employee = new \App\Models\Employee(['name' => 'Selected employee']))
            @include('admin.employees.leaves.partials.form', ['leave' => $leave])

            <div class="form-actions">
                <button type="submit">Save leave request</button>
            </div>
        </form>
    </section>
</div>
@endsection

