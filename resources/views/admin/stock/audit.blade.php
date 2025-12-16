@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Stock audit / cycle count</h1>
                <p>Record counted quantities and variances per warehouse, product, and batch.</p>
            </div>
        </header>

        @if(session('status'))
            <p class="panel-note">{{ session('status') }}</p>
        @endif

        <form action="{{ route('admin.stock.audit.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="warehouse_id">Warehouse</label>
                <select id="warehouse_id" name="warehouse_id" required>
                    <option value="">Select</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="product_id">Product</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Select</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->sku }} — {{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="batch_id">Batch (optional)</label>
                <select id="batch_id" name="batch_id">
                    <option value="">Any batch</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->batch_code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="counted_quantity">Counted quantity</label>
                <input id="counted_quantity" name="counted_quantity" type="number" min="0" required>
            </div>
            <div class="full-width">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit">Record count</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Recent audits</h1>
                <p>Latest variances across warehouses.</p>
            </div>
        </header>

        @if($audits->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Warehouse</th>
                        <th>Product</th>
                        <th>Batch</th>
                        <th>System</th>
                        <th>Counted</th>
                        <th>Variance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($audits as $audit)
                        <tr>
                            <td>{{ $audit->created_at->format('Y-m-d') }}</td>
                            <td>{{ $audit->warehouse->name }}</td>
                            <td>{{ $audit->product->sku }} — {{ $audit->product->name }}</td>
                            <td>{{ $audit->batch->batch_code ?? '—' }}</td>
                            <td>{{ $audit->system_quantity }}</td>
                            <td>{{ $audit->counted_quantity }}</td>
                            <td>{{ $audit->variance }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $audits->links() }}
        @else
            <p class="panel-note">No audits recorded yet.</p>
        @endif
    </section>
</div>
@endsection

