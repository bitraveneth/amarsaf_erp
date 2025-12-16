@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit {{ $product->name }}</h1>
                <p>Adjust SKU details, packaging, or pricing.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.products.show', $product) }}" class="button-secondary">Back to details</a>
                <a href="{{ route('admin.products.create') }}" class="button-secondary">New SKU</a>
            </div>
        </header>

        <form action="{{ route('admin.products.update', $product) }}" method="POST" class="form-form" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="form-section">
                <div class="section-header">
                    <h3>Product details</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="sku">SKU</label>
                        <input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
                        @error('sku') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="name">Product name</label>
                        <input id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="product_image">Product image</label>
                        <input id="product_image" name="product_image" type="file" accept="image/*">
                        @if($product->image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="product-image-preview">
                        @endif
                    </div>
                    <div class="full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Measurements</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="size">Size</label>
                        <input id="size" name="size" value="{{ old('size', $product->size) }}">
                    </div>
                    <div>
                        <label for="volume_ml">Volume (ml)</label>
                        <input id="volume_ml" name="volume_ml" type="number" step="0.01" value="{{ old('volume_ml', $product->volume_ml) }}">
                    </div>
                    <div>
                        <label for="sku_code">SKU code</label>
                        <input id="sku_code" name="sku_code" value="{{ old('sku_code', $product->sku_code) }}">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Packaging &amp; taxation</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="packaging_type_id">Packaging</label>
                        <select id="packaging_type_id" name="packaging_type_id">
                            <option value="">Unassigned</option>
                            @foreach($packagingTypes as $type)
                                <option value="{{ $type->id }}"{{ old('packaging_type_id', $product->packaging_type_id) == $type->id ? ' selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="tax_class_id">Tax class</label>
                        <select id="tax_class_id" name="tax_class_id">
                            <option value="">Unassigned</option>
                            @foreach($taxClasses as $class)
                                <option value="{{ $class->id }}"{{ old('tax_class_id', $product->tax_class_id) == $class->id ? ' selected' : '' }}>
                                    {{ $class->name }} &middot; {{ $class->rate }}%
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Composition &amp; certifications</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="mineral_source">Mineral source</label>
                        <input id="mineral_source" name="mineral_source" value="{{ old('mineral_source', $product->mineral_source) }}">
                    </div>
                    <div>
                        <label for="ph">pH</label>
                        <input id="ph" name="ph" type="number" step="0.01" value="{{ old('ph', $product->ph) }}">
                    </div>
                    <div>
                        <label for="tds">TDS</label>
                        <input id="tds" name="tds" type="number" value="{{ old('tds', $product->tds) }}">
                    </div>
                    <div>
                        <label for="certifications">Certifications</label>
                        <input id="certifications" name="certifications" value="{{ old('certifications', $product->certifications) }}">
                        <p class="text-muted">Lists approvals such as BSTI, ISO, or WQA.</p>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Codes &amp; pricing</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="barcode">Barcode</label>
                        <input id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}">
                    </div>
                    <div>
                        <label for="qr_code_file">Upload QR image</label>
                        <input id="qr_code_file" name="qr_code_file" type="file" accept="image/*">
                        @if($product->qr_code)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($product->qr_code) }}" alt="QR code" class="qr-preview">
                        @endif
                    </div>
                    <div>
                        <label for="base_price">Base price</label>
                        <input id="base_price" name="base_price" type="number" step="0.01" value="{{ old('base_price', $product->base_price) }}">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Save changes</button>
            </div>
        </form>
    </section>
</div>
@endsection
