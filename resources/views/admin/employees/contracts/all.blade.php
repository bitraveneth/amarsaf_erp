@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Employee contracts</h1>
                <p>All active and historical contracts across employees.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.employees.index') }}" class="button-secondary">Back to employees</a>
                <a href="{{ route('admin.contracts.create') }}" class="button-primary">Add contract</a>
            </div>
        </header>

        @if($contracts->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Reference</th>
                        <th>Period</th>
                        <th>Salary</th>
                        <th>TA</th>
                        <th>DA</th>
                        <th>Bonus</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contracts as $contract)
                        <tr>
                            <td>{{ $contract->employee?->name ?? '—' }}</td>
                            <td>{{ $contract->reference }}</td>
                            <td>
                                {{ $contract->start_date?->format('Y-m-d') }}
                                –
                                {{ $contract->end_date?->format('Y-m-d') ?? 'Open-ended' }}
                            </td>
                            <td>{{ number_format($contract->salary_amount ?? 0, 2) }}</td>
                            <td>{{ number_format($contract->travel_allowance ?? 0, 2) }}</td>
                            <td>{{ number_format($contract->dearness_allowance ?? 0, 2) }}</td>
                            <td>{{ number_format($contract->bonus ?? 0, 2) }}</td>
                            <td>{{ ucfirst($contract->status) }}</td>
                            <td>
                                @if($contract->employee)
                                    <a href="{{ route('admin.employees.contracts.edit', [$contract->employee, $contract]) }}" class="button-secondary">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.employees.contracts.destroy', [$contract->employee, $contract]) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this contract? This cannot be undone.');">
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
            {{ $contracts->links() }}
        @else
            <p class="panel-note">No contracts recorded yet.</p>
        @endif
    </section>
</div>
@endsection
