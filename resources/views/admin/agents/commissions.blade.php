@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Commission summary</h1>
                <p>Per-agent sales and per-order commissions for {{ $month->format('F Y') }}.</p>
            </div>
        </header>

        @if(empty($rows))
            <p class="panel-note">No commissionable sales in this month.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Sales</th>
                        <th>Commission</th>
                        <th>Effective rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php
                            $rate = $row['sales'] > 0 ? ($row['commission'] / $row['sales']) * 100 : 0;
                        @endphp
                        <tr>
                            <td>{{ $row['agent']->name }}</td>
                            <td>{{ number_format($row['sales'], 2) }}</td>
                            <td>{{ number_format($row['commission'], 2) }}</td>
                            <td>{{ number_format($rate, 2) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</div>
@endsection

