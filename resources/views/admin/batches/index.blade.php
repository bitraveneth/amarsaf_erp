@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <h1>Batches</h1>
            <p>Track production dates, expiry, and QC status.</p>
        </header>
        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <form action="{{ route('admin.batches.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="product_id">Product</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Select product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"{{ old('product_id') == $product->id ? ' selected' : '' }}>
                            {{ $product->sku }} — {{ $product->name }}
                        </option>
                    @endforeach
                </select>
                @error('product_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="batch_code">Batch code</label>
                <input id="batch_code" name="batch_code" value="{{ old('batch_code') }}" required>
                @error('batch_code') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="production_date">Production date</label>
                <input id="production_date" name="production_date" type="date" value="{{ old('production_date') }}" required>
                @error('production_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="expiry_date">Expiry date</label>
                <input id="expiry_date" name="expiry_date" type="date" value="{{ old('expiry_date') }}">
            </div>
            <div>
                <label for="qc_status">QC status</label>
                <select id="qc_status" name="qc_status">
                    <option value="pending"{{ old('qc_status') == 'pending' ? ' selected' : '' }}>Pending</option>
                    <option value="approved"{{ old('qc_status') == 'approved' ? ' selected' : '' }}>Approved</option>
                    <option value="rejected"{{ old('qc_status') == 'rejected' ? ' selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div>
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
            </div>
            <div class="form-actions">
                <button type="submit">Record batch</button>
            </div>
        </form>

        @if($batches->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Batch</th>
                        <th>Production</th>
                        <th>Expiry</th>
                        <th>QC</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                    <tr>
                        <td>{{ $batch->product->name ?? '—' }}</td>
                        <td>{{ $batch->batch_code }}</td>
                        <td>{{ $batch->production_date ?? '—' }}</td>
                        <td>{{ $batch->expiry_date ?? '—' }}</td>
                        <td>{{ ucfirst($batch->qc_status) }}</td>
                        <td>
                            <a href="{{ route('admin.batches.edit', $batch) }}" class="button-secondary">Edit</a>
                            <form action="{{ route('admin.batches.destroy', $batch) }}" method="POST" class="inline-form" style="display:inline-block" onsubmit="return confirm('Delete this batch?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button-secondary">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $batches->links() }}
        @else
            <p class="panel-note">No batches recorded yet.</p>
        @endif
    </section>
</div>
@endsection
