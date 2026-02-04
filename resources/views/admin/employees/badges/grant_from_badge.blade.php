@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Grant badge: {{ $badge->name }}</h1>
                <p>Select an employee to award this badge.</p>
            </div>
            <a href="{{ route('admin.badges.index') }}" class="button-secondary">Back to badges</a>
        </header>

        <form action="{{ route('admin.badges.grant', $badge) }}" method="POST" class="form-form">
            @csrf
            <div class="form-section">
                <div class="form-grid">
                    <div>
                        <label for="employee_id">Employee</label>
                        <select id="employee_id" name="employee_id" required>
                            <option value="">Select employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="granted_at">Granted date</label>
                        <input id="granted_at" name="granted_at" type="date" value="{{ old('granted_at') }}">
                    </div>
                    <div class="full-width">
                        <label for="note">Note</label>
                        <input id="note" name="note" value="{{ old('note') }}">
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit">Grant badge</button>
            </div>
        </form>
    </section>
</div>
@endsection

