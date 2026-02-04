@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Employee leaves</h1>
                <p>All leave requests across employees.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.employees.index') }}" class="button-secondary">Back to employees</a>
                <a href="{{ route('admin.leaves.create') }}" class="button-primary">Add leave request</a>
            </div>
        </header>

        @if($leaves->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Period</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Approved by</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $leave)
                        <tr>
                            <td>{{ $leave->employee?->name ?? '—' }}</td>
                            <td>
                                {{ $leave->start_date?->format('Y-m-d') }}
                                –
                                {{ $leave->end_date?->format('Y-m-d') }}
                            </td>
                            <td>{{ ucfirst($leave->type) }}</td>
                            <td>{{ $leave->reason ?? '—' }}</td>
                            <td>{{ ucfirst($leave->status) }}</td>
                            <td>{{ $leave->approved_by ?? '—' }}</td>
                            <td>
                                @if($leave->employee)
                                    <a href="{{ route('admin.employees.leaves.edit', [$leave->employee, $leave]) }}" class="button-secondary">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.employees.leaves.destroy', [$leave->employee, $leave]) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this leave request? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button-secondary">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $leaves->links() }}
        @else
            <p class="panel-note">No leave requests recorded yet.</p>
        @endif
    </section>
</div>
@endsection
