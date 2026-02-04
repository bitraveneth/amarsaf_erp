@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>New BOM</h1>
                <p>Specify which components and quantities are required to produce 1 unit of the finished product.</p>
            </div>
        </header>

        <form action="{{ route('admin.boms.store') }}" method="post" class="form-form">
            @csrf

            <div class="form-section">
                <div class="section-header">
                    <h3>Finished product</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="product_id">Product</label>
                        <select name="product_id" id="product_id" required>
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                    {{ $product->sku }} – {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="name">BOM name (optional)</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}">
                    </div>
                    <div>
                        <label for="is_active">Active</label>
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                    </div>
                    <div class="full-width">
                        <label for="notes">Notes</label>
                        <textarea name="notes" id="notes">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Components</h3>
                    <p>Example: for 1 carton – 12 bottles, 12 caps, 12 labels, etc.</p>
                </div>

                <table class="data-table" id="bom-items-table">
                    <thead>
                    <tr>
                        <th>Component product</th>
                        <th>Quantity per 1 unit</th>
                        <th>Unit (optional)</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <select name="items[0][component_product_id]" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->sku }} – {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.0001" min="0" name="items[0][quantity]" required>
                        </td>
                        <td>
                            <input type="text" name="items[0][unit]" placeholder="pcs, bottle, cap">
                        </td>
                        <td>
                            <button type="button" class="button-secondary" onclick="removeBomRow(this)">Remove</button>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <div class="form-actions">
                    <button type="button" class="button-secondary" onclick="addBomRow()">+ Add component</button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button-primary">Save BOM</button>
            </div>
        </form>
    </section>
</div>

@push('scripts')
<script>
    let bomRowIndex = 1;
    function addBomRow() {
        const table = document.getElementById('bom-items-table').querySelector('tbody');
        const template = table.rows[0].cloneNode(true);
        [...template.querySelectorAll('select,input')].forEach((el) => {
            if (el.name.includes('component_product_id')) {
                el.name = `items[${bomRowIndex}][component_product_id]`;
                el.value = '';
            } else if (el.name.includes('quantity')) {
                el.name = `items[${bomRowIndex}][quantity]`;
                el.value = '';
            } else if (el.name.includes('unit')) {
                el.name = `items[${bomRowIndex}][unit]`;
                el.value = '';
            }
        });
        table.appendChild(template);
        bomRowIndex++;
    }

    function removeBomRow(button) {
        const tableBody = document.getElementById('bom-items-table').querySelector('tbody');
        if (tableBody.rows.length === 1) return;
        button.closest('tr').remove();
    }
</script>
@endpush
@endsection
