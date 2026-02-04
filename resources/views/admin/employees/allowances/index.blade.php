@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Allowances & TA/DA for {{ $employee->name }}</h1>
                <p>Track travel and other allowances, with slips.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.employees.index') }}" class="button-secondary">Back to employees</a>
                <a href="{{ route('admin.employees.allowances.create', $employee) }}" class="button-primary">Add allowance</a>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($allowances->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
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
                                <a href="{{ route('admin.employees.allowances.edit', [$employee, $allowance]) }}" class="button-secondary">
                                    Edit
                                </a>
                                <form action="{{ route('admin.employees.allowances.destroy', [$employee, $allowance]) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this allowance? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $allowances->links() }}
        @else
            <p class="panel-note">No allowances recorded for this employee yet.</p>
        @endif
    </section>
</div>
@endsection

