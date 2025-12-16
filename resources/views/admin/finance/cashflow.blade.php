@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Cashflow</h1>
                <p>{{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</p>
            </div>
        </header>

        <table class="data-table">
            <tbody>
                <tr>
                    <td>Cash inflows (Bank debits)</td>
                    <td>{{ number_format($cashIn, 2) }}</td>
                </tr>
                <tr>
                    <td>Cash outflows (Bank credits)</td>
                    <td>{{ number_format($cashOut, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Net cashflow</strong></td>
                    <td><strong>{{ number_format($net, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </section>
</div>
@endsection

