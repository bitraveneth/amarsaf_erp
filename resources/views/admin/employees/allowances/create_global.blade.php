@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add allowance / TA slip</h1>
                <p>Select an employee and submit a new allowance claim.</p>
            </div>
            <a href="{{ route('admin.allowances.index') }}" class="button-secondary">Back to allowances</a>
        </header>

        <form action="{{ route('admin.allowances.store') }}" method="POST" class="form-form" enctype="multipart/form-data">
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

            @include('admin.employees.allowances.partials.form', ['allowance' => $allowance])

            <div class="form-actions">
                <button type="submit">Save allowance</button>
            </div>
        </form>
    </section>
</div>
@endsection

