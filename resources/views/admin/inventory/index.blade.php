@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Inventory health</h1>
                <p>View reserved vs. available stock and expiring batches.</p>
            </div>
            <a href="{{ route('admin.stock.movements') }}" class="button-secondary">Movement log</a>
        </header>

        <div class="master-grid">
            @foreach($summary as $bucket)
                <article>
                    <p class="metric-label">{{ $bucket->warehouse->name }} / {{ ucfirst($bucket->status) }}</p>
                    <h3>{{ number_format($bucket->total, 2) }}</h3>
                    <small>Qty</small>
                </article>
            @endforeach
        </div>

        <section class="form-section">
            <div class="section-header">
                <h3>Expiring soon</h3>
                <p>Batches with expiry within 30 days.</p>
            </div>
            @if($expiringSoon->isEmpty())
                <p class="panel-note">No expiring batches in the next 30 days.</p>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Batch</th>
                            <th>Expiry</th>
                            <th>Warehouse</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiringSoon as $entry)
                            <tr>
                                <td>{{ $entry->product->name }}</td>
                                <td>{{ $entry->batch->batch_code ?? '—' }}</td>
                                <td>{{ optional($entry->batch->expiry_date)->format('Y-m-d') }}</td>
                                <td>{{ $entry->warehouse->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </section>
</div>
@endsection
