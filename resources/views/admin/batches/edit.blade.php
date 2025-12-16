@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit batch</h1>
                <p>Adjust production, expiry, or QC status.</p>
            </div>
            <a href="{{ route('admin.batches.index') }}" class="button-secondary">Back to list</a>
        </header>

        <form action="{{ route('admin.batches.update', $batch) }}" method="POST" class="form-grid">
            @csrf
            @method('PATCH')
            <div>
                <label for="product_id">Product</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Select product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"{{ old('product_id', $batch->product_id) == $product->id ? ' selected' : '' }}>
                            {{ $product->sku }} — {{ $product->name }}
                        </option>
                    @endforeach
                </select>
                @error('product_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="batch_code">Batch code</label>
                <input id="batch_code" name="batch_code" value="{{ old('batch_code', $batch->batch_code) }}" required>
                @error('batch_code') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="production_date">Production date</label>
                <input id="production_date" name="production_date" type="date" value="{{ old('production_date', $batch->production_date) }}" required>
                @error('production_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="expiry_date">Expiry date</label>
                <input id="expiry_date" name="expiry_date" type="date" value="{{ old('expiry_date', $batch->expiry_date) }}">
                @error('expiry_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="qc_status">QC status</label>
                <select id="qc_status" name="qc_status">
                    @foreach(['pending', 'approved', 'rejected'] as $status)
                        <option value="{{ $status }}"{{ old('qc_status', $batch->qc_status) == $status ? ' selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes">{{ old('notes', $batch->notes) }}</textarea>
            </div>
            <div class="form-actions">
                <button type="submit">Save batch</button>
            </div>
        </form>
    </section>
</div>
@endsection
