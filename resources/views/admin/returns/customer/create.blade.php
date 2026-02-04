@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Record customer return</h1>
                <p>Capture a return against an order and add stock back into a warehouse.</p>
            </div>
            <a href="{{ route('admin.returns.customer.index') }}" class="button-secondary">Back to returns</a>
        </header>

        <form action="{{ route('admin.returns.customer.store') }}" method="POST" class="form-grid">
            @csrf
            <div>
                <label for="order_id">Order</label>
                <select id="order_id" name="order_id" required>
                    <option value="">Select order</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}" {{ old('order_id') == $order->id ? 'selected' : '' }}>
                            #{{ $order->id }} – {{ $order->agent->name ?? 'No agent' }} ({{ $order->order_type }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="product_id">Product</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Select product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="warehouse_id">Warehouse</label>
                <select id="warehouse_id" name="warehouse_id" required>
                    <option value="">Select warehouse</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="number" step="0.01" value="{{ old('quantity') }}" required>
            </div>
            <div class="full-width">
                <label for="notes">Notes</label>
                <input id="notes" name="notes" value="{{ old('notes') }}">
            </div>
            <div class="form-actions full-width">
                <button type="submit">Save return</button>
            </div>
        </form>
    </section>
</div>
@endsection

