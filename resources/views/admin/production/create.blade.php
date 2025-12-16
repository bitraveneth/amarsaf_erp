@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Log production run</h1>
                <p>Capture quantity, line, shift, and QC status for each batch.</p>
            </div>
            <a href="{{ route('admin.production.index') }}" class="button-secondary">Back to runs</a>
        </header>

        <form action="{{ route('admin.production.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="product_id">Product</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Select</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="batch_id">Batch</label>
                <select id="batch_id" name="batch_id" required>
                    <option value="">Select</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->batch_code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="warehouse_id">Warehouse</label>
                <select id="warehouse_id" name="warehouse_id">
                    <option value="">Select</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}"{{ old('warehouse_id') == $warehouse->id ? ' selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="line">Production line</label>
                <input id="line" name="line" value="{{ old('line') }}">
            </div>
            <div>
                <label for="shift">Shift</label>
                <input id="shift" name="shift" value="{{ old('shift') }}">
            </div>
            <div>
                <label for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="number" step="1" required value="{{ old('quantity', 0) }}">
            </div>
            <div>
                <label for="qc_status">QC status</label>
                <select id="qc_status" name="qc_status">
                    @foreach(['pending','approved','rejected'] as $status)
                        <option value="{{ $status }}"{{ old('qc_status') == $status ? ' selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="full-width">
                <label for="notes">Notes</label>
                <textarea name="notes">{{ old('notes') }}</textarea>
            </div>
            <div class="form-actions">
                <button type="submit">Record run</button>
            </div>
        </form>
    </section>
</div>
@endsection
