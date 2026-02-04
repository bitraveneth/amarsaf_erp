@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Payroll summary</h1>
                <p>Salary and allowances per employee for the selected period.</p>
            </div>
            <form method="GET" action="{{ route('admin.reports.payroll') }}" class="inline-filters">
                <label>
                    From
                    <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}">
                </label>
                <label>
                    To
                    <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}">
                </label>
                <button type="submit" class="button-secondary">Apply</button>
            </form>
        </header>

        <div class="master-grid">
            <article>
                <p class="metric-label">Total salary</p>
                <h3>{{ number_format($totals['salary'], 2) }}</h3>
            </article>
            <article>
                <p class="metric-label">Total TA</p>
                <h3>{{ number_format($totals['ta'], 2) }}</h3>
            </article>
            <article>
                <p class="metric-label">Total DA</p>
                <h3>{{ number_format($totals['da'], 2) }}</h3>
            </article>
            <article>
                <p class="metric-label">Total bonus</p>
                <h3>{{ number_format($totals['bonus'], 2) }}</h3>
            </article>
            <article>
                <p class="metric-label">Grand total</p>
                <h3>{{ number_format($totals['grand'], 2) }}</h3>
            </article>
        </div>
    </section>

    <section class="panel">
        <header>
            <h2>Per-employee breakdown</h2>
            <p>Base contract amounts plus TA/DA/bonus allowances in this period.</p>
        </header>

        @if($rows->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Base salary</th>
                        <th>Base TA</th>
                        <th>Base DA</th>
                        <th>Base bonus</th>
                        <th>TA allowances</th>
                        <th>DA allowances</th>
                        <th>Bonus allowances</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php
                            $employee = $row['employee'];
                        @endphp
                        <tr>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->department ?? '—' }}</td>
                            <td>{{ number_format($row['total_salary'], 2) }}</td>
                            <td>{{ number_format($row['base_ta'], 2) }}</td>
                            <td>{{ number_format($row['base_da'], 2) }}</td>
                            <td>{{ number_format($row['base_bonus'], 2) }}</td>
                            <td>{{ number_format($row['ta_allowances'], 2) }}</td>
                            <td>{{ number_format($row['da_allowances'], 2) }}</td>
                            <td>{{ number_format($row['bonus_allowances'], 2) }}</td>
                            <td>{{ number_format($row['grand_total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="panel-note">No payroll data found for this period.</p>
        @endif
    </section>
</div>
@endsection

