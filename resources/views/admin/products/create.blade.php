@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add product</h1>
                <p>Capture SKU details, packaging linkage, and pricing.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="button-secondary">Back to catalog</a>
        </header>

        <form action="{{ route('admin.products.store') }}" method="POST" class="form-form" enctype="multipart/form-data">
            @csrf

            <div class="form-section">
                <div class="section-header">
                    <h3>Product details</h3>
                    <p>Define the SKU, descriptive copy, and hero image.</p>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="sku">SKU</label>
                        <input id="sku" name="sku" value="{{ old('sku') }}" required>
                        @error('sku') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="name">Product name</label>
                        <input id="name" name="name" value="{{ old('name') }}" required>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="product_image">Product image <span class="text-muted">(PNG/JPG, 1080×1080 px preferred)</span></label>
                        <input id="product_image" name="product_image" type="file" accept="image/*">
                    </div>
                    <div class="full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Measurements</h3>
                    <p>Define size labels, volumes, and auxiliary codes.</p>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="size">Size (e.g., 500ml)</label>
                        <input id="size" name="size" value="{{ old('size') }}">
                    </div>
                    <div>
                        <label for="volume_ml">Volume (ml)</label>
                        <input id="volume_ml" name="volume_ml" type="number" step="0.01" value="{{ old('volume_ml') }}">
                    </div>
                    <div>
                        <label for="sku_code">Internal code (optional) <span class="text-muted">– legacy / agent app code</span></label>
                        <input id="sku_code" name="sku_code" value="{{ old('sku_code') }}">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Packaging &amp; taxation</h3>
                    <p>Link SKUs to packaging master data and VAT-ready tax classes.</p>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="packaging_type_id">Packaging type</label>
                        <select id="packaging_type_id" name="packaging_type_id">
                            <option value="">Unassigned</option>
                            @foreach($packagingTypes as $type)
                                <option value="{{ $type->id }}"{{ old('packaging_type_id') == $type->id ? ' selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="tax_class_id">
                            Tax class
                            <span class="text-muted">– HSN/SAC code and local tax references load from the class.</span>
                        </label>
                        <select id="tax_class_id" name="tax_class_id">
                            <option value="">Unassigned</option>
                            @foreach($taxClasses as $class)
                                <option value="{{ $class->id }}"{{ old('tax_class_id') == $class->id ? ' selected' : '' }}>
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
                    <p>Track mineral source, pH, TDS, and Bangladesh-ready certifications (e.g., BSTI, ISO).</p>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="mineral_source">Mineral source</label>
                        <input id="mineral_source" name="mineral_source" value="{{ old('mineral_source') }}">
                    </div>
                    <div>
                        <label for="ph">pH</label>
                        <input id="ph" name="ph" type="number" step="0.01" value="{{ old('ph') }}">
                    </div>
                    <div>
                        <label for="tds">TDS (ppm)</label>
                        <input id="tds" name="tds" type="number" value="{{ old('tds') }}">
                    </div>
                    <div>
                        <label for="certifications">Certifications <span class="text-muted">(BSTI, ISO 22000, Halal, etc.)</span></label>
                        <input id="certifications" name="certifications" value="{{ old('certifications') }}">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Codes &amp; pricing</h3>
                    <p>Barcode, QR code, and base price information that travel with every order.</p>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="barcode">Barcode</label>
                        <input id="barcode" name="barcode" value="{{ old('barcode') }}">
                    </div>
                    <div>
                        <label for="qr_code_file">Upload QR image</label>
                        <input id="qr_code_file" name="qr_code_file" type="file" accept="image/*">
                    </div>
                    <div>
                        <label for="base_price">Base price</label>
                        <input id="base_price" name="base_price" type="number" step="0.01" value="{{ old('base_price') }}" required>
                        @error('base_price') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Save product</button>
            </div>
        </form>
    </section>
</div>
@endsection
