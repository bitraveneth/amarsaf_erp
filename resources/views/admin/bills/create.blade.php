@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Add purchase bill</h1>
                <p>Record supplier invoices and book AP.</p>
            </div>
            <a href="{{ route('admin.bills.index') }}" class="button-secondary">Back to bills</a>
        </header>

        <form action="{{ route('admin.bills.store') }}" method="POST" class="form-form">
            @csrf
            <div class="form-section">
                <div class="section-header">
                    <h3>Header</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="supplier_id">Supplier</label>
                        <select id="supplier_id" name="supplier_id" required>
                            <option value="">Select</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="bill_date">Bill date</label>
                        <input id="bill_date" name="bill_date" type="date" required>
                    </div>
                    <div>
                        <label for="due_date">Due date</label>
                        <input id="due_date" name="due_date" type="date">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Line items</h3>
                </div>
                <div id="bill-items" class="form-grid">
                    <div class="full-width">
                        <label for="items[0][description]">Description</label>
                        <input name="items[0][description]" required>
                    </div>
                    <div>
                        <label for="items[0][product_id]">Product (optional)</label>
                        <select name="items[0][product_id]">
                            <option value="">None</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->sku }} — {{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="items[0][quantity]">Quantity</label>
                        <input name="items[0][quantity]" type="number" min="1" value="1" required>
                    </div>
                    <div>
                        <label for="items[0][unit_price]">Unit price</label>
                        <input name="items[0][unit_price]" type="number" step="0.01" min="0" required>
                    </div>
                </div>
                <button type="button" class="button-secondary" id="add-bill-item">Add line</button>
            </div>

            <div class="form-actions">
                <button type="submit">Save bill</button>
            </div>
        </form>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.getElementById('bill-items');
        const addBtn = document.getElementById('add-bill-item');
        let index = 1;

        addBtn?.addEventListener('click', () => {
            const block = document.createElement('div');
            block.innerHTML = `
                <div class="full-width">
                    <label for="items[${index}][description]">Description</label>
                    <input name="items[${index}][description]" required>
                </div>
                <div>
                    <label for="items[${index}][product_id]">Product (optional)</label>
                    <select name="items[${index}][product_id]">
                        <option value="">None</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} — {{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="items[${index}][quantity]">Quantity</label>
                    <input name="items[${index}][quantity]" type="number" min="1" value="1" required>
                </div>
                <div>
                    <label for="items[${index}][unit_price]">Unit price</label>
                    <input name="items[${index}][unit_price]" type="number" step="0.01" min="0" required>
                </div>
            `;
            wrapper.appendChild(block);
            index += 1;
        });
    });
</script>
@endpush
@endsection

