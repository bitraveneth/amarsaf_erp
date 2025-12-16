@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Product catalog</h1>
                <p>Browse SKUs, packaging, and pricing at a glance.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.products.export') }}" class="button-secondary">Export CSV</a>
                <a href="{{ route('admin.products.create') }}" class="button-primary">Add product</a>
            </div>
        </header>

        <div class="catalog-hero">
            <div class="catalog-metric-grid">
                <article class="metric-card catalog-metric">
                    <p class="metric-label">Total SKUs</p>
                    <h3>{{ number_format($products->total()) }}</h3>
                    <small>Sorted by latest</small>
                </article>
                <article class="metric-card catalog-metric">
                    <p class="metric-label">Packaging types</p>
                    <h3>{{ number_format($packagingCount) }}</h3>
                    <small>Linked to packaging master</small>
                </article>
                <article class="metric-card catalog-metric">
                    <p class="metric-label">Tax classes</p>
                    <h3>{{ number_format($taxClassCount) }}</h3>
                    <small>HSN/SAC rates defined</small>
                </article>
            </div>
            <div class="catalog-actions">
                <input type="search" placeholder="Search SKU, name, barcode" aria-label="Search catalog">
                <button type="button" class="button-secondary">Filter</button>
            </div>
        </div>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        @if($products->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Packaging</th>
                        <th>Tax rate</th>
                        <th>Price</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{ $product->sku }}</td>
                            <td>
                                <a href="{{ route('admin.products.show', $product) }}">
                                    {{ $product->name }}
                                </a>
                                <small class="text-muted">{{ $product->size ?? '—' }}</small>
                            </td>
                            <td>{{ $product->packagingType->name ?? '—' }}</td>
                            <td>{{ $product->taxClass->rate ?? '0' }}%</td>
                            <td>{{ number_format($product->base_price ?? 0, 2) }}</td>
                            <td>
                                <a href="{{ route('admin.products.show', $product) }}" class="button-secondary">View</a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="button-secondary">Edit</a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $products->links() }}
        @else
            <p class="panel-note">The catalog is currently empty. Click “Add product” to get started.</p>
        @endif
    </section>
</div>
@endsection
