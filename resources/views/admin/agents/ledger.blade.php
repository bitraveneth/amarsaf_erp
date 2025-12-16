@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Agent ledger · {{ $agent->name }}</h1>
                <p>Invoices and receipts between {{ $from->format('Y-m-d') }} and {{ $to->format('Y-m-d') }}.</p>
            </div>
            <a href="{{ route('admin.agents.index') }}" class="button-secondary">Back to agents</a>
        </header>

        @if(empty($rows))
            <p class="panel-note">No ledger activity in this period.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Ref</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ $row['date']->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($row['type']) }}</td>
                            <td>{{ $row['ref'] }}</td>
                            <td>{{ number_format($row['debit'], 2) }}</td>
                            <td>{{ number_format($row['credit'], 2) }}</td>
                            <td>{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="panel-note">Closing balance: {{ number_format($balance, 2) }}</p>
        @endif
    </section>
</div>
@endsection

