@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel metrics-panel">
        <div class="panel-header">
            <div>
                <h1>SAFERP admin overview</h1>
                <p>Quick snapshot of users, products, and master data.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.products.index') }}" class="button-secondary">Products</a>
                <a href="{{ route('admin.orders.index') }}" class="button-secondary">Orders</a>
                <a href="{{ route('admin.warehouses.index') }}" class="button-secondary">Warehouses</a>
            </div>
        </div>
        <div class="metric-grid">
            @foreach($metrics as $metric)
                <article class="metric-card">
                    <p class="metric-label">{{ $metric['label'] }}</p>
                    <h2 class="metric-value">{{ $metric['value'] }}</h2>
                    <p class="metric-change">{{ $metric['detail'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="panel">
        <header>
            <h2>Today at a glance</h2>
            <p>Operational snapshot for deliveries, production, and cash.</p>
        </header>
        <div class="master-grid">
            <article>
                <p class="metric-label">Orders delivering today</p>
                <h3>{{ number_format($todayOrders) }}</h3>
                <small>Based on order delivery date.</small>
            </article>
            <article>
                <p class="metric-label">Batches expiring soon</p>
                <h3>{{ number_format($expiringSoonCount) }}</h3>
                <small>Expiry within the next 30 days.</small>
            </article>
            <article>
                <p class="metric-label">Approved production today</p>
                <h3>{{ number_format($todayProductionQty) }}</h3>
                <small>Total bottles/litres from approved runs.</small>
            </article>
            <article>
                <p class="metric-label">Outstanding receivables</p>
                <h3>{{ number_format($outstandingReceivables, 2) }}</h3>
                <small>Open invoices net of receipts.</small>
            </article>
            <article>
                <p class="metric-label">Receipts today</p>
                <h3>{{ number_format($todayReceipts, 2) }}</h3>
                <small>Customer payments received today.</small>
            </article>
        </div>
    </section>

    <section class="panel">
        <header>
            <h2>System alerts</h2>
            <p>Things that may need attention.</p>
        </header>
        @if(empty($alerts))
            <p class="panel-note">All clear. No current alerts.</p>
        @else
            <ul class="activity-list">
                @foreach($alerts as $alert)
                    <li>{{ $alert }}</li>
                @endforeach
            </ul>
        @endif
    </section>

</div>
@endsection
