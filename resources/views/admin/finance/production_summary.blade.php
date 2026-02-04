@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Production &amp; P/L summary</h1>
                <p>Production quantities vs sales and expenses for the selected period.</p>
            </div>
            <form method="GET" action="{{ route('admin.reports.production') }}" class="inline-filters">
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
                <p class="metric-label">Total sales (invoices)</p>
                <h3>{{ number_format($salesTotal, 2) }}</h3>
            </article>
            <article>
                <p class="metric-label">Total expenses</p>
                <h3>{{ number_format($expensesTotal, 2) }}</h3>
            </article>
            <article>
                <p class="metric-label">Approx. profit / loss</p>
                <h3>{{ number_format($approxProfit, 2) }}</h3>
            </article>
        </div>
    </section>

    <section class="panel">
        <header>
            <h2>Production by product</h2>
            <p>Number of runs and total quantity produced per product.</p>
        </header>

        @if($byProduct->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Runs</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($byProduct as $row)
                        <tr>
                            <td>{{ $row['product']?->name ?? 'Unknown product' }}</td>
                            <td>{{ $row['runs'] }}</td>
                            <td>{{ number_format($row['quantity']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="panel-note">No production runs recorded for this period.</p>
        @endif
    </section>
</div>
@endsection

