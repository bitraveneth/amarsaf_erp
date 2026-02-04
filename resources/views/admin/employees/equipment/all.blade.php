@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Employee equipment</h1>
                <p>All devices and equipment assigned across employees.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.employees.index') }}" class="button-secondary">Back to employees</a>
                <a href="{{ route('admin.equipment.create') }}" class="button-primary">Assign equipment</a>
            </div>
        </header>

        @if($equipment->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Effective date</th>
                        <th>Employee</th>
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
                            <td>{{ $item->employee?->name ?? '—' }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->device_identifier ?? '—' }}</td>
                            <td>{{ ucfirst($item->status) }}</td>
                            <td>{{ $item->notes ? \Illuminate\Support\Str::limit($item->notes, 60) : '—' }}</td>
                            <td>
                                @if($item->employee)
                                    <a href="{{ route('admin.employees.equipment.edit', [$item->employee, $item]) }}" class="button-secondary">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.employees.equipment.destroy', [$item->employee, $item]) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this equipment record? This cannot be undone.');">
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
            {{ $equipment->links() }}
        @else
            <p class="panel-note">No equipment recorded yet.</p>
        @endif
    </section>
</div>
@endsection
