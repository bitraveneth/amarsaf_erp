@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Grant badge to {{ $employee->name }}</h1>
                <p>Select a badge and optionally add a note.</p>
            </div>
            <a href="{{ route('admin.employees.edit', $employee) }}" class="button-secondary">Back to employee</a>
        </header>

        <form action="{{ route('admin.employees.badges.grant', $employee) }}" method="POST" class="form-form">
            @csrf
            <div class="form-section">
                <div class="form-grid">
                    <div>
                        <label for="badge_id">Badge</label>
                        <select id="badge_id" name="badge_id" required>
                            <option value="">Select a badge</option>
                            @foreach($badges as $badge)
                                <option value="{{ $badge->id }}"{{ old('badge_id') == $badge->id ? ' selected' : '' }}>
                                    {{ $badge->name }} ({{ $badge->code }})
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

