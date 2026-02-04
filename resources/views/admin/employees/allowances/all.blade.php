@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Employee allowances</h1>
                <p>All TA/DA and other allowances across employees.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.employees.index') }}" class="button-secondary">Back to employees</a>
                <a href="{{ route('admin.allowances.create') }}" class="button-primary">Add allowance</a>
            </div>
        </header>

        @if($allowances->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Slip</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allowances as $allowance)
                        <tr>
                            <td>{{ $allowance->date?->format('Y-m-d') }}</td>
                            <td>{{ $allowance->employee?->name ?? '—' }}</td>
                            <td>{{ strtoupper($allowance->type) }}</td>
                            <td>{{ $allowance->reference ?? '—' }}</td>
                            <td>{{ number_format($allowance->amount, 2) }}</td>
                            <td>{{ ucfirst($allowance->status) }}</td>
                            <td>
                                @if($allowance->attachment_path)
                                    <a href="{{ asset('storage/'.$allowance->attachment_path) }}" target="_blank" class="button-secondary">View slip</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($allowance->employee)
                                    <a href="{{ route('admin.employees.allowances.edit', [$allowance->employee, $allowance]) }}" class="button-secondary">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.employees.allowances.destroy', [$allowance->employee, $allowance]) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this allowance? This cannot be undone.');">
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
            {{ $allowances->links() }}
        @else
            <p class="panel-note">No allowances recorded yet.</p>
        @endif
    </section>
</div>
@endsection
