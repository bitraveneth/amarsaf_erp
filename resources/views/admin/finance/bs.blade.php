@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Balance Sheet</h1>
                <p>As of {{ $asOf->format('Y-m-d') }}</p>
            </div>
        </header>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Assets</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assets as $account => $amount)
                    <tr>
                        <td>{{ $account }}</td>
                        <td>{{ number_format($amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Total Assets</strong></td>
                    <td><strong>{{ number_format($totalAssets, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <table class="data-table" style="margin-top: 1.5rem;">
            <thead>
                <tr>
                    <th>Liabilities</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($liabilities as $account => $amount)
                    <tr>
                        <td>{{ $account }}</td>
                        <td>{{ number_format($amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Total Liabilities</strong></td>
                    <td><strong>{{ number_format($totalLiabilities, 2) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Equity</strong></td>
                    <td><strong>{{ number_format($equity, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </section>
</div>
@endsection

