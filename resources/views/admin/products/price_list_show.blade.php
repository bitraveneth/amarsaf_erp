@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Price list · {{ $product->sku }} — {{ $product->name }}</h1>
                <p>Base price and agent-specific overrides for this SKU.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.products.prices.index') }}" class="button-secondary">Back to price lists</a>
                <a href="{{ route('admin.products.edit', $product) }}" class="button-secondary">Edit product</a>
            </div>
        </header>

        <article class="metric-card">
            <p class="metric-label">Base price</p>
            <h3>{{ number_format($product->base_price ?? 0, 2) }}</h3>
            <small class="text-muted">Configured on the product master</small>
        </article>

        @if($agentPrices->isEmpty())
            <p class="panel-note">
                No agent-specific price overrides found for this SKU.
                Use the agent pricing screen to define negotiated prices.
            </p>
        @else
            <h2>Agent overrides</h2>
            <table class="data-table">
                <thead>
                <tr>
                    <th>Agent</th>
                    <th>Area / Zone</th>
                    <th>Override price</th>
                    <th>Notes</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($agentPrices as $row)
                    <tr>
                        <td>{{ optional($row->agent)->name ?? '—' }}</td>
                        <td>
                            @if($row->agent)
                                {{ $row->agent->area }}@if($row->agent->zone) · {{ $row->agent->zone }}@endif
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ number_format($row->price ?? 0, 2) }}</td>
                        <td>{{ $row->notes ?? '—' }}</td>
                        <td>
                            @if($row->agent)
                                <a href="{{ route('admin.agents.pricing.edit', $row->agent) }}" class="button-secondary">
                                    Open agent pricing
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>
</div>
@endsection

