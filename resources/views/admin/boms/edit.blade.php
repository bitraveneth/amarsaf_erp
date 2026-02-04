@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>Edit BOM</h1>
                <p>Update the bill of materials for {{ $bom->product?->name }}.</p>
            </div>
        </header>

        <form action="{{ route('admin.boms.update', $bom) }}" method="post" class="form-form">
            @csrf
            @method('PATCH')

            <div class="form-section">
                <div class="section-header">
                    <h3>Finished product</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label>Product</label>
                        <input type="text" value="{{ $bom->product?->sku }} – {{ $bom->product?->name }}" disabled>
                    </div>
                    <div>
                        <label for="name">BOM name (optional)</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $bom->name) }}">
                    </div>
                    <div>
                        <label for="is_active">Active</label>
                        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $bom->is_active))>
                    </div>
                    <div class="full-width">
                        <label for="notes">Notes</label>
                        <textarea name="notes" id="notes">{{ old('notes', $bom->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Components</h3>
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
                    @foreach($bom->items as $index => $item)
                        <tr>
                            <td>
                                <select name="items[{{ $index }}][component_product_id]" required>
                                    <option value="">Select product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                            @selected(old("items.$index.component_product_id", $item->component_product_id) == $product->id)>
                                            {{ $product->sku }} – {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.0001" min="0"
                                       name="items[{{ $index }}][quantity]"
                                       value="{{ old("items.$index.quantity", $item->quantity) }}" required>
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][unit]"
                                       value="{{ old("items.$index.unit", $item->unit) }}"
                                       placeholder="pcs, bottle, cap">
                            </td>
                            <td>
                                <button type="button" class="button-secondary" onclick="removeBomRow(this)">Remove</button>
                            </td>
                        </tr>
                    @endforeach
                    @if($bom->items->isEmpty())
                        <tr>
                            <td>
                                <select name="items[0][component_product_id]" required>
                                    <option value="">Select product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->sku }} – {{ $product->name }}</option>
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
                    @endif
                    </tbody>
                </table>

                <div class="form-actions">
                    <button type="button" class="button-secondary" onclick="addBomRow()">+ Add component</button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button-primary">Update BOM</button>
            </div>
        </form>
    </section>
</div>

@push('scripts')
<script>
    let bomRowIndex = {{ max(1, $bom->items->count()) }};
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
