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

        <section class="form-section mt-6">
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiringSoon as $entry)
                            <tr>
                                <td>{{ $entry->product->name }}</td>
                                <td>{{ $entry->batch->batch_code ?? '—' }}</td>
                                <td>{{ optional($entry->batch->expiry_date)->format('Y-m-d') }}</td>
                                <td>{{ $entry->warehouse->name }}</td>
                                <td>
                                    <form action="{{ route('admin.stock.entries.writeoff', $entry) }}" method="POST" class="inline-form" onsubmit="return confirm('Write off this batch as expired?');">
                                        @csrf
                                        <button type="submit" class="button-secondary">Write off</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="form-section mt-4">
            <div class="section-header">
                <h3>Recent stock movements</h3>
                <p>Last 10 inventory transactions – transfers, write‑offs, deliveries.</p>
            </div>
            @if($recentMovements->isEmpty())
                <p class="panel-note">No recent stock movements found.</p>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Warehouse</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Type</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentMovements as $movement)
                            <tr>
                                <td>{{ $movement->created_at?->format('Y-m-d') }}</td>
                                <td>{{ optional($movement->stockEntry->warehouse)->name ?? '—' }}</td>
                                <td>
                                    @php
                                        $product = optional($movement->stockEntry->product);
                                    @endphp
                                    {{ $product->sku ?? '' }} {{ $product->name ?? '' }}
                                </td>
                                <td>{{ number_format($movement->quantity, 2) }}</td>
                                <td>{{ ucfirst($movement->type) }}</td>
                                <td>
                                    @if($movement->order)
                                        Order #{{ $movement->order->id }}
                                        @if($movement->order->agent)
                                            – {{ $movement->order->agent->name }}
                                        @endif
                                    @else
                                        {{ $movement->notes }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </section>
</div>
@endsection
