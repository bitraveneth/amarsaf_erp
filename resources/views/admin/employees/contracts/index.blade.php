@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Contracts for {{ $employee->name }}</h1>
                <p>Manage employment contracts, salary, and allowances.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.employees.index') }}" class="button-secondary">Back to employees</a>
                <a href="{{ route('admin.employees.contracts.create', $employee) }}" class="button-primary">Add contract</a>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($contracts->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
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
                                <a href="{{ route('admin.employees.contracts.edit', [$employee, $contract]) }}" class="button-secondary">
                                    Edit
                                </a>
                                <form action="{{ route('admin.employees.contracts.destroy', [$employee, $contract]) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this contract? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $contracts->links() }}
        @else
            <p class="panel-note">No contracts recorded for this employee yet.</p>
        @endif
    </section>
</div>
@endsection

