@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Employees</h1>
                <p>Manage employee profiles, contact details, and documents.</p>
            </div>
            <a href="{{ route('admin.employees.create') }}" class="button-primary">Add employee</a>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($employees->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Job position</th>
                        <th>Work email</th>
                        <th>Work zone</th>
                        <th>Profile</th>
                        <th>HR</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                        <tr>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->department ?? '—' }}</td>
                            <td>{{ $employee->job_position ?? '—' }}</td>
                            <td>{{ $employee->work_email ?? '—' }}</td>
                            <td>{{ $employee->work_zone ?? '—' }}</td>
                            <td>
                                <div class="button-group">
                                    <a href="{{ route('admin.employees.show', $employee) }}" class="button-secondary">
                                        View
                                    </a>
                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="button-secondary">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="inline-form" onsubmit="return confirm('Delete this employee? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button-secondary">Delete</button>
                                    </form>
                                </div>
                            </td>
                            <td>
                                <details class="row-menu">
                                    <summary>HR actions</summary>
                                    <div class="row-menu-list">
                                        <a href="{{ route('admin.employees.contracts.index', $employee) }}">Contracts</a>
                                        <a href="{{ route('admin.employees.allowances.index', $employee) }}">Allowances</a>
                                        <a href="{{ route('admin.employees.equipment.index', $employee) }}">Equipment</a>
                                        <a href="{{ route('admin.employees.leaves.index', $employee) }}">Leave</a>
                                        <a href="{{ route('admin.employees.locations.index', $employee) }}">Locations</a>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $employees->links() }}
        @else
            <p class="panel-note">No employees added yet.</p>
        @endif
    </section>
</div>
@endsection
