@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel metrics-panel">
        <div class="panel-header">
            <h1>Admin dashboard</h1>
            <p>Live data pulled from your application data stores.</p>
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

    <section class="panel master-panel">
        <header>
            <h2>Product master data</h2>
            <p>Single source of truth for SKU, packaging and taxation.</p>
        </header>
        <div class="master-grid">
            <article>
                <p class="metric-label">Products</p>
                <h3>{{ number_format($masterSummary['products']) }}</h3>
                <small>{{ $productTableReady ? 'Active catalog entries' : 'Run migrations to enable' }}</small>
            </article>
            <article>
                <p class="metric-label">Packaging</p>
                <h3>{{ number_format($masterSummary['packaging']) }}</h3>
                <small>{{ $packagingReady ? 'Defined packaging types' : 'Add packaging types first' }}</small>
            </article>
            <article>
                <p class="metric-label">Tax classes</p>
                <h3>{{ number_format($masterSummary['taxClasses']) }}</h3>
                <small>{{ $taxReady ? 'HSN / local tax rules' : 'Create a tax table' }}</small>
            </article>
            <article>
                <p class="metric-label">Batches</p>
                <h3>{{ number_format($masterSummary['batches']) }}</h3>
                <small>{{ $batchCount ? 'Tracked batches' : 'Add batches when production starts' }}</small>
            </article>
        </div>
    </section>

    <section class="panel recent-panel">
        <header>
            <h2>Recent products</h2>
            <p>Latest SKUs and their packaging/tax linkage.</p>
        </header>
        @if(!$productTableReady)
            <p class="panel-note">Products table is unavailable. Run `php artisan migrate` first.</p>
        @elseif($recentProducts->isEmpty())
            <p class="panel-note">No products have been registered yet.</p>
        @else
            <div class="recent-table">
                <div class="recent-row header">
                    <span>SKU</span>
                    <span>Product</span>
                    <span>Packaging</span>
                    <span>Price</span>
                </div>
                @foreach($recentProducts as $product)
                    <div class="recent-row">
                        <span>{{ $product->sku }}</span>
                        <span>{{ $product->name }}</span>
                        <span>{{ $product->packagingType->name ?? '—' }}</span>
                        <span>{{ number_format($product->base_price ?? 0, 2) }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="panel recent-panel">
        <header>
            <h2>Recent batches</h2>
            <p>Production and QC snapshots.</p>
        </header>
        @if($recentBatches->isEmpty())
            <p class="panel-note">No batch data available yet.</p>
        @else
            <div class="recent-table">
                <div class="recent-row header">
                    <span>Product</span>
                    <span>Batch</span>
                    <span>Production</span>
                    <span>QC status</span>
                </div>
                @foreach($recentBatches as $batch)
                    <div class="recent-row">
                        <span>{{ $batch->product->name ?? '—' }}</span>
                        <span>{{ $batch->batch_code }}</span>
                        <span>{{ optional($batch->production_date)->format('M d, Y') }}</span>
                        <span>{{ ucfirst($batch->qc_status) }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
