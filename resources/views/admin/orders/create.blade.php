@extends('layouts.app')

@section('content')
<div class="erp-order-page">
    <x-admin.order-toolbar
        title="New sales order"
        subtitle="Sell finished products to agents — confirm, fulfill, then deliver."
        :back-url="route('admin.orders.index')"
        back-label="All sales orders"
    />

    <form action="{{ route('admin.orders.store') }}" method="POST" class="erp-order-form">
        @csrf

        <div class="erp-order-form__workflow">
            <x-admin.order-workflow type="sales" :step="1" variant="hero" />
        </div>

        <div class="erp-order-form__body">
                <!-- Agent & Order Section -->
                <section class="erp-order-panel">
                    <div class="erp-order-panel__head">
                        <div>
                            <h3 class="erp-order-panel__title">Agent &amp; delivery</h3>
                            <p class="erp-order-panel__desc">Who is ordering and where goods should go</p>
                        </div>
                    </div>
                    
                    <div class="erp-order-field-grid">
                        <!-- Agent -->
                        <div class="lg:col-span-4 space-y-2">
                            <label for="agent_id" class="erp-order-label">
                                Agent <span class="text-red-500">*</span>
                            </label>
                            <select id="agent_id" 
                                    name="agent_id" 
                                    required
                                    class="erp-order-input">
                                    <option value="">Select agent</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}"{{ old('agent_id') == $agent->id ? ' selected' : '' }}>
                                            {{ $agent->name }} · {{ $agent->zone ?? '—' }} · {{ $agent->area ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            @error('agent_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Agent PO / Reference -->
                        <div class="space-y-2">
                            <label for="agent_reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Agent PO / Reference
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 20l4-16 4 4 4-4 4 16H7z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="agent_reference" 
                                       name="agent_reference" 
                                       value="{{ old('agent_reference') }}"
                                       placeholder="e.g. PO-2025-001"
                                       class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                            </div>
                            @error('agent_reference')
                                <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Order Type -->
                        <div class="space-y-2">
                            <label for="order_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Order Type
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <select id="order_type" 
                                        name="order_type"
                                        class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                    @foreach(['regular','bulk','sample','return'] as $type)
                                        <option value="{{ $type }}"{{ old('order_type', 'regular') == $type ? ' selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                "Sample" orders are non-billable free issues. "Return" orders are pickup authorizations and do not reserve stock or generate invoices. "Bulk" orders default to credit if no payment mode is chosen.
                            </p>
                        </div>

                        <!-- Delivery Date -->
                        <div class="space-y-2">
                            <label for="delivery_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Delivery Date
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="date" 
                                       id="delivery_date" 
                                       name="delivery_date" 
                                       value="{{ old('delivery_date', now()->addDays(7)->format('Y-m-d')) }}"
                                       class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition-all">
                            </div>
                            @error('delivery_date')
                                <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Mode -->
                        <div class="space-y-2">
                            <label for="payment_mode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Payment Mode
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                                    </svg>
                                </div>
                                <select id="payment_mode" 
                                        name="payment_mode"
                                        class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                    <option value="">Select payment mode</option>
                                    @foreach(['cash' => 'Cash', 'credit' => 'Credit', 'bkash' => 'bKash', 'bank_transfer' => 'Bank Transfer'] as $value => $label)
                                        <option value="{{ $value }}"{{ old('payment_mode') === $value ? ' selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Contact Name -->
                        <div class="space-y-2">
                            <label for="delivery_contact_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Delivery Contact Name
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="delivery_contact_name" 
                                       name="delivery_contact_name" 
                                       value="{{ old('delivery_contact_name') }}"
                                       placeholder="e.g. Md. Rahim Uddin"
                                       class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                            </div>
                        </div>

                        <!-- Delivery Contact Phone -->
                        <div class="space-y-2">
                            <label for="delivery_contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Delivery Contact Phone
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="delivery_contact_phone" 
                                       name="delivery_contact_phone" 
                                       value="{{ old('delivery_contact_phone') }}"
                                       placeholder="e.g. +880 1XXX-XXXXXX"
                                       class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                            </div>
                        </div>

                        <!-- Delivery Address (Full Width) -->
                        <div class="lg:col-span-3 space-y-2">
                            <label for="delivery_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Delivery Address
                            </label>
                            <div class="relative group">
                                <div class="absolute left-3 top-3 flex items-start pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                </div>
                                <textarea id="delivery_address" 
                                          name="delivery_address" 
                                          rows="3"
                                          placeholder="Street address, city, postal code, country"
                                          class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">{{ old('delivery_address') }}</textarea>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Leave blank to use agent's default address</p>
                        </div>

                        <!-- Notes (Full Width) -->
                        <div class="lg:col-span-3 space-y-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notes
                            </label>
                            <div class="relative group">
                                <div class="absolute left-3 top-3 flex items-start pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                    </svg>
                                </div>
                                <textarea id="notes" 
                                          name="notes" 
                                          rows="3"
                                          placeholder="Add any special instructions, delivery notes, or additional information..."
                                          class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Order Items Section -->
                <section class="erp-order-lines mt-6">
                    <div class="erp-order-lines__head">
                        <div>
                            <h3 class="erp-order-panel__title">Order lines</h3>
                            <p class="erp-order-panel__desc">Finished products, quantities and negotiated prices</p>
                        </div>
                    </div>

                    <div id="order-items" class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Product</label>
                                <select name="items[0][product_id]" 
                                        data-product-select 
                                        required
                                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                                    <option value="">Select SKU</option>
                                    @foreach($products as $product)
                                        @php
                                            $available = $availability[$product->id] ?? 0;
                                        @endphp
                                        <option value="{{ $product->id }}" data-stock="{{ $available }}">
                                            {{ $product->sku }} — {{ $product->name }}
                                            ({{ number_format($available, 0) }} avail.)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                                <input type="number" 
                                       name="items[0][quantity]" 
                                       value="1" 
                                       min="1" 
                                       required
                                       class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Unit Price (BDT)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-gray-500">BDT</span>
                                    <input type="number" 
                                           name="items[0][unit_price]" 
                                           step="0.01" 
                                           min="0" 
                                           data-unit-price 
                                           required
                                           placeholder="0.00"
                                           class="w-full rounded-lg border border-gray-200 bg-white pl-12 pr-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-item" class="erp-order-lines__add">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add line item
                    </button>
                </section>
        </div>

        <x-admin.order-footer
            submit-label="Create sales order"
            :cancel-url="route('admin.orders.index')"
            hint="Order starts as draft until confirmed"
        />
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.agentPricing = @json($priceLists);
        window.productBasePrices = @json($products->pluck('base_price', 'id'));

        const itemsWrapper = document.getElementById('order-items');
        const addBtn = document.getElementById('add-item');
        const agentSelect = document.getElementById('agent_id');
        let index = {{ isset($order) && $order->items ? $order->items->count() : 1 }};

        function refreshPrices() {
            const agentId = agentSelect?.value || null;
            const agentPrices = (window.agentPricing && agentId && window.agentPricing[agentId]) || {};

            const productSelects = itemsWrapper.querySelectorAll('[data-product-select]');
            productSelects.forEach((select) => {
                const row = select.closest('.grid');
                const priceInput = row.querySelector('[data-unit-price]');
                if (!priceInput) return;

                const productId = select.value;
                if (!productId) return;

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
            template.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800';
            template.innerHTML = `
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Product</label>
                    <select name="items[${index}][product_id]" data-product-select required class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                        <option value="">Select SKU</option>
                        @foreach($products as $product)
                            @php
                                $available = $availability[$product->id] ?? 0;
                            @endphp
                            <option value="{{ $product->id }}" data-stock="{{ $available }}">
                                {{ $product->sku }} — {{ $product->name }}
                                ({{ number_format($available, 0) }} avail.)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                    <input type="number" name="items[${index}][quantity]" value="1" min="1" required class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                </div>
                <div class="space-y-2 relative">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Unit Price (BDT)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-gray-500">BDT</span>
                        <input type="number" name="items[${index}][unit_price]" step="0.01" min="0" data-unit-price required placeholder="0.00" class="w-full rounded-lg border border-gray-200 bg-white pl-12 pr-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                    </div>
                </div>
            `;
            itemsWrapper.appendChild(template);
            
            const newSelect = template.querySelector('[data-product-select]');
            newSelect.addEventListener('change', refreshPrices);
            index += 1;
        });

        agentSelect?.addEventListener('change', refreshPrices);

        const initialSelects = itemsWrapper.querySelectorAll('[data-product-select]');
        initialSelects.forEach((select) => {
            select.addEventListener('change', refreshPrices);
        });
        
        // Trigger initial price load
        refreshPrices();
    });
</script>
@endpush
@endsection
