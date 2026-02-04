@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Salary distributions</h1>
                <p>Per-employee salary, allowances, and commission for a period.</p>
            </div>
            <form method="GET" action="{{ route('admin.salary-distributions.index') }}" class="inline-filters">
                <label>
                    Month
                    <input type="month" name="month" value="{{ request('month', $month->format('Y-m')) }}">
                </label>
                <label>
                    Employee
                    <select name="employee_id">
                        <option value="">All</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string)request('employee_id') === (string)$employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="button-secondary">Filter</button>
            </form>
        </header>

        <div class="master-grid">
            <article>
                <p class="metric-label">Total distributed</p>
                <h3>{{ number_format($total, 2) }}</h3>
            </article>
        </div>
    </section>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h2>Distributions</h2>
                <p>One row per employee per period.</p>
            </div>
            <a href="{{ route('admin.salary-distributions.create') }}" class="button-primary">Add distribution</a>
        </header>

        @if($rows->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Period</th>
                        <th>Base salary</th>
                        <th>Bonus</th>
                        <th>TA</th>
                        <th>DA</th>
                        <th>Commission</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Document</th>
                        <th>Remarks</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php
                            $totalRow = ($row->base_salary ?? 0)
                                + ($row->bonus ?? 0)
                                + ($row->ta_allowances ?? 0)
                                + ($row->da_allowances ?? 0)
                                + ($row->commission ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $row->employee?->name ?? '—' }}</td>
                            <td>{{ optional($row->period_start)->format('Y-m-d') }} – {{ optional($row->period_end)->format('Y-m-d') }}</td>
                            <td>{{ number_format($row->base_salary, 2) }}</td>
                            <td>{{ number_format($row->bonus, 2) }}</td>
                            <td>{{ number_format($row->ta_allowances, 2) }}</td>
                            <td>{{ number_format($row->da_allowances, 2) }}</td>
                            <td>{{ number_format($row->commission, 2) }}</td>
                            <td>{{ number_format($totalRow, 2) }}</td>
                            <td>{{ ucfirst($row->payment_method ?? '—') }}</td>
                            <td>
                                @if($row->document_path)
                                    <a href="{{ asset('storage/'.$row->document_path) }}" target="_blank" class="button-secondary">View</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $row->remarks ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.salary-distributions.edit', $row) }}" class="button-secondary">Edit</a>
                                <form action="{{ route('admin.salary-distributions.destroy', $row) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $rows->links() }}
        @else
            <p class="panel-note">No salary distributions recorded for this period.</p>
        @endif
    </section>
</div>
@endsection

