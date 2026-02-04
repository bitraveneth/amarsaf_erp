@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Location logs for {{ $employee->name }}</h1>
                <p>Manual or imported location entries for this employee.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.employees.index') }}" class="button-secondary">Back to employees</a>
                <a href="{{ route('admin.employees.locations.create', $employee) }}" class="button-primary">Add location</a>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($logs->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date/time</th>
                        <th>Label</th>
                        <th>Coordinates</th>
                        <th>Source</th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->logged_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->location_label ?? '—' }}</td>
                            <td>
                                @if($log->latitude !== null && $log->longitude !== null)
                                    {{ $log->latitude }}, {{ $log->longitude }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $log->source ?? '—' }}</td>
                            <td>{{ $log->notes ?? '—' }}</td>
                            <td>
                                @if($log->latitude !== null && $log->longitude !== null)
                                    <a href="https://www.google.com/maps?q={{ $log->latitude }},{{ $log->longitude }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="button-secondary">
                                        View
                                    </a>
                                @endif
                                <a href="{{ route('admin.employees.locations.edit', [$employee, $log]) }}" class="button-secondary">
                                    Edit
                                </a>
                                <form action="{{ route('admin.employees.locations.destroy', [$employee, $log]) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this location log? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $logs->links() }}
        @else
            <p class="panel-note">No location logs recorded for this employee yet.</p>
        @endif
    </section>
</div>
@endsection
