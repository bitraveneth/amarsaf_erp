@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>{{ $product->name }}</h1>
                <p>SKU {{ $product->sku }} · {{ $product->size ?? 'Unspecified size' }}</p>
                <p class="text-muted">{{ $product->description ?? 'No description provided yet.' }}</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.products.edit', $product) }}" class="button-secondary">Edit</a>
                <a href="{{ route('admin.products.create') }}" class="button-secondary">New SKU</a>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <div class="master-grid">
            <article>
                <p class="metric-label">Packaging</p>
                <h3>{{ $product->packagingType->name ?? 'Unassigned' }}</h3>
                <small>{{ $product->packagingType->unit ?? '' }}</small>
            </article>
            <article>
                <p class="metric-label">Tax class</p>
                <h3>{{ $product->taxClass->name ?? 'Unassigned' }} ({{ $product->taxClass->rate ?? 0 }}%)</h3>
                <small>{{ $product->taxClass->hsn_code ?? $product->taxClass->local_tax_code ?? '' }}</small>
            </article>
            <article>
                <p class="metric-label">Volume</p>
                <h3>{{ $product->volume_ml ? number_format($product->volume_ml, 2) . ' ml' : '—' }}</h3>
                <small>Size: {{ $product->size ?? 'unknown' }}</small>
            </article>
            <article>
                <p class="metric-label">Price</p>
                <h3>{{ number_format($product->base_price ?? 0, 2) }}</h3>
                <small>Base price</small>
            </article>
        </div>

        @if($product->image_path)
            <div class="product-image-card">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="product-image-full">
                <p class="panel-note">Product image</p>
            </div>
        @endif

        <div class="form-section">
            <div class="section-header">
                <h3>Composition</h3>
            </div>
            <div class="form-grid">
                <div>
                    <label>Mineral source</label>
                    <p class="panel-note">{{ $product->mineral_source ?? 'Not specified' }}</p>
                </div>
                <div>
                    <label>pH</label>
                    <p class="panel-note">{{ $product->ph ?? '—' }}</p>
                </div>
                <div>
                    <label>TDS</label>
                    <p class="panel-note">{{ $product->tds ?? '—' }}</p>
                </div>
                <div>
                    <label>Certifications</label>
                    <p class="panel-note">{{ $product->certifications ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="form-section">
                <div class="section-header">
                    <h3>Codes &amp; identifiers</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label>Barcode</label>
                        <p class="panel-note">{{ $product->barcode ?? '—' }}</p>
                    </div>
                    @if($product->qr_code)
                        <div>
                            <label>QR image</label>
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($product->qr_code) }}" alt="QR code" class="qr-preview">
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($product->qr_code) }}" target="_blank" class="text-muted">Download image</a>
                        </div>
                    @else
                        <div>
                            <label>QR image</label>
                            <p class="panel-note">Not uploaded yet.</p>
                        </div>
                    @endif
                </div>

            <div class="section-header">
                <h3>Batches</h3>
            </div>
            @if($product->batches->isEmpty())
                <p class="panel-note">No batches recorded yet.</p>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Batch code</th>
                            <th>Production</th>
                            <th>Expiry</th>
                            <th>QC status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->batches as $batch)
                            <tr>
                                <td>{{ $batch->batch_code }}</td>
                        <td>{{ $batch->production_date ?? '—' }}</td>
                        <td>{{ $batch->expiry_date ?? '—' }}</td>
                                <td>{{ ucfirst($batch->qc_status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
</div>
@endsection
