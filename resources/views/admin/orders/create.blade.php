@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel">
        <header class="panel-header">
            <div>
                <h1>New order</h1>
                <p>Capture SKU, quantity, and delivery info for the agent.</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="button-secondary">Back to orders</a>
        </header>

        <form action="{{ route('admin.orders.store') }}" method="POST" class="form-form">
            @csrf
            <div class="form-section">
                <div class="section-header">
                    <h3>Agent & order</h3>
                </div>
                <div class="form-grid">
                    <div>
                        <label for="agent_id">Agent</label>
                        <select id="agent_id" name="agent_id" required>
                            <option value="">Select</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}"{{ old('agent_id') == $agent->id ? ' selected' : '' }}>
                                    {{ $agent->name }} · {{ $agent->zone ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="order_type">Order type</label>
                        <select id="order_type" name="order_type">
                            @foreach(['regular','bulk','sample','return'] as $type)
                                <option value="{{ $type }}"{{ old('order_type') == $type ? ' selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="delivery_date">Delivery date</label>
                        <input id="delivery_date" name="delivery_date" type="date" value="{{ old('delivery_date') }}">
                    </div>
                    <div class="full-width">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">
                    <h3>Order items</h3>
                    <p>Add SKUs, quantity, and negotiated price (from price list or default).</p>
                </div>
                <div id="order-items" class="form-grid">
                    <div class="order-item-row">
                        <label for="items[0][product_id]">Product</label>
                        <select name="items[0][product_id]" data-product-select required>
                            <option value="">Select SKU</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->sku }} — {{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="order-item-row">
                        <label for="items[0][quantity]">Quantity</label>
                        <input name="items[0][quantity]" type="number" value="1" min="1" required>
                    </div>
                    <div class="order-item-row">
                        <label for="items[0][unit_price]">Unit price</label>
                        <input name="items[0][unit_price]" type="number" step="0.01" min="0" data-unit-price required>
                    </div>
                </div>
                <button type="button" class="button-secondary" id="add-item">Add another item</button>
            </div>

            <div class="form-actions">
                <button type="submit">Save order</button>
            </div>
        </form>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.agentPricing = @json($priceLists);
        window.productBasePrices = @json($products->pluck('base_price', 'id'));

        const itemsWrapper = document.getElementById('order-items');
        const addBtn = document.getElementById('add-item');
        const agentSelect = document.getElementById('agent_id');
        let index = 1;

        function refreshPrices() {
            const agentId = agentSelect?.value || null;
            const agentPrices = (window.agentPricing && agentId && window.agentPricing[agentId]) || {};

            const productSelects = itemsWrapper.querySelectorAll('[data-product-select]');
            productSelects.forEach((select) => {
                const row = select.closest('.order-item-row') || select.parentElement;
                const priceInput = row.parentElement.querySelector('[data-unit-price]');
                if (!priceInput) {
                    return;
                }

                const productId = select.value;
                if (!productId) {
                    return;
                }

                let price = null;
                if (agentPrices && Object.prototype.hasOwnProperty.call(agentPrices, productId)) {
                    price = agentPrices[productId];
                } else if (window.productBasePrices && Object.prototype.hasOwnProperty.call(window.productBasePrices, productId)) {
                    price = window.productBasePrices[productId];
                }

                if (price !== null && price !== undefined && price !== '') {
                    priceInput.value = price;
                }
            });
        }

        addBtn?.addEventListener('click', () => {
            const template = document.createElement('div');
            template.innerHTML = `
                <div class="order-item-row">
                    <label for="items[${index}][product_id]">Product</label>
                    <select name="items[${index}][product_id]" data-product-select required>
                        <option value="">Select SKU</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} — {{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="order-item-row">
                    <label for="items[${index}][quantity]">Quantity</label>
                    <input name="items[${index}][quantity]" type="number" value="1" min="1" required>
                </div>
                <div class="order-item-row">
                    <label for="items[${index}][unit_price]">Unit price</label>
                    <input name="items[${index}][unit_price]" type="number" step="0.01" min="0" data-unit-price required>
                </div>
            `;
            itemsWrapper.appendChild(template);
            const newSelect = itemsWrapper.querySelectorAll('[data-product-select]')[itemsWrapper.querySelectorAll('[data-product-select]').length - 1];
            newSelect.addEventListener('change', refreshPrices);
            index += 1;
        });

        agentSelect?.addEventListener('change', refreshPrices);

        const initialSelects = itemsWrapper.querySelectorAll('[data-product-select]');
        initialSelects.forEach((select) => {
            select.addEventListener('change', refreshPrices);
        });
    });
</script>
@endpush
@endsection
