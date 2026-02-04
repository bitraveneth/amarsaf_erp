@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Agent performance</h1>
                <p>Sales and collections by agent for the selected period.</p>
            </div>
            <form method="GET" action="{{ route('admin.reports.agents') }}" class="inline-filters">
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

        @if($rows->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Location</th>
                        <th>Orders</th>
                        <th>Invoices</th>
                        <th>Invoiced</th>
                        <th>Credits</th>
                        <th>Net sales</th>
                        <th>Receipts</th>
                        <th>Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php
                            $agent = $row['agent'];
                        @endphp
                        <tr>
                            <td>{{ $agent->name }}</td>
                            <td>
                                {{ $agent->area ?? '—' }} / {{ $agent->zone ?? '—' }}
                                @if($agent->location_code || $agent->special_code)
                                    <br>
                                    <small>
                                        {{ $agent->location_code ?? '' }} {{ $agent->special_code ? '(' . $agent->special_code . ')' : '' }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $row['order_count'] }}</td>
                            <td>{{ $row['invoice_count'] }}</td>
                            <td>{{ number_format($row['invoiced'], 2) }}</td>
                            <td>{{ number_format($row['credits'], 2) }}</td>
                            <td>{{ number_format($row['net_sales'], 2) }}</td>
                            <td>{{ number_format($row['receipts'], 2) }}</td>
                            <td>{{ number_format($row['outstanding'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="panel-note">No invoices found for this period.</p>
        @endif
    </section>
</div>
@endsection

