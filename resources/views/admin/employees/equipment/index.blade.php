@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Equipment for {{ $employee->name }}</h1>
                <p>Track devices and other equipment assigned to this employee.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.employees.index') }}" class="button-secondary">Back to employees</a>
                <a href="{{ route('admin.employees.equipment.create', $employee) }}" class="button-primary">Assign equipment</a>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($equipment->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Effective date</th>
                        <th>Product name</th>
                        <th>Device ID / IMEI</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equipment as $item)
                        <tr>
                            <td>{{ $item->effective_date?->format('Y-m-d') }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->device_identifier ?? '—' }}</td>
                            <td>{{ ucfirst($item->status) }}</td>
                            <td>{{ $item->notes ? \Illuminate\Support\Str::limit($item->notes, 60) : '—' }}</td>
                            <td>
                                <a href="{{ route('admin.employees.equipment.edit', [$employee, $item]) }}" class="button-secondary">
                                    Edit
                                </a>
                                <form action="{{ route('admin.employees.equipment.destroy', [$employee, $item]) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this equipment record? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $equipment->links() }}
        @else
            <p class="panel-note">No equipment recorded for this employee yet.</p>
        @endif
    </section>
</div>
@endsection
