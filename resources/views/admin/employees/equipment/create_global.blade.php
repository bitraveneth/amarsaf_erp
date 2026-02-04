@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Assign equipment</h1>
                <p>Select an employee and record a device or item.</p>
            </div>
            <a href="{{ route('admin.equipment.index') }}" class="button-secondary">Back to equipment</a>
        </header>

        <form action="{{ route('admin.equipment.store') }}" method="POST" class="form-form">
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
            @include('admin.employees.equipment.partials.form', ['item' => $item, 'employee' => $employee])

            <div class="form-actions">
                <button type="submit">Save equipment</button>
            </div>
        </form>
    </section>
</div>
@endsection

