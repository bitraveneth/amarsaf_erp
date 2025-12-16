@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Profit &amp; Loss</h1>
                <p>{{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</p>
            </div>
        </header>

        <table class="data-table">
            <tbody>
                <tr>
                    <td>Sales revenue</td>
                    <td>{{ number_format($sales, 2) }}</td>
                </tr>
                <tr>
                    <td>Sales returns</td>
                    <td>{{ number_format($returns, 2) }}</td>
                </tr>
                <tr>
                    <td>Net sales</td>
                    <td>{{ number_format($netSales, 2) }}</td>
                </tr>
                <tr>
                    <td>Commission expense</td>
                    <td>{{ number_format($commissions, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Profit</strong></td>
                    <td><strong>{{ number_format($profit, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </section>
</div>
@endsection

