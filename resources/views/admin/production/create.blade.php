@extends('layouts.app')

@section('content')
<div class="space-y-6 screen-production-create">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    New production run
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    Step 3 · Produce
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Pick a product, preview a previous run on the right, then load it — a <strong>new batch lot</strong> is still created when you save.
            </p>
        </div>
        <a href="{{ route('admin.manufacturing.dashboard') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Manufacturing dashboard
        </a>
    </div>

    <section class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <x-admin.order-workflow type="manufacturing" :step="3" :in-progress="true" variant="procurement" label="Manufacturing process" />
    </section>

    <div id="bom-status-banner" class="rounded-2xl border px-5 py-4 text-sm border-gray-200 bg-gray-50 text-gray-600 dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-400">
        Select a product to confirm the active recipe (step 1) before recording this run.
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 7h8M8 11h6M8 15h4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Run setup</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Choose product and quantity — preview a previous run on the right, then load it into the form</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.production.store') }}" method="POST" class="p-6" id="production-create-form"
              x-data="productionForm(@js($formState))">
            @csrf
            <input type="hidden" name="batch_mode" id="batch_mode" value="{{ old('batch_mode', 'auto') }}">

            <div class="bom-form__product-layout">
                <div class="bom-form__product-main">
                    <div class="po-create__field">
                        <label for="product_id" class="po-create__label">Product <span class="po-create__req">*</span></label>
                        <select id="product_id" name="product_id" x-model="selectedProductId" required
                                @change="onProductChange()"
                                class="po-create__input">
                            <option value="">Select finished product…</option>
                            <template x-for="product in products" :key="product.id">
                                <option :value="String(product.id)" x-text="productLabel(product)"></option>
                            </template>
                        </select>
                        @error('product_id')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="po-create__field">
                            <label for="quantity" class="po-create__label">Quantity to produce <span class="po-create__req">*</span></label>
                            <input type="number" id="quantity" name="quantity" x-model="quantity"
                                   @input="syncExternalWidgets()"
                                   step="1" min="1" required placeholder="e.g. 500"
                                   class="po-create__input">
                            @error('quantity')<p class="po-create__error">{{ $message }}</p>@enderror
                        </div>

                        <div class="po-create__field">
                            <label for="warehouse_id" class="po-create__label">Warehouse</label>
                            <select id="warehouse_id" name="warehouse_id" x-model="warehouseId"
                                    @change="syncExternalWidgets()"
                                    class="po-create__input">
                                <option value="">Select warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                            @error('warehouse_id')<p class="po-create__error">{{ $message }}</p>@enderror
                        </div>

                        <div class="po-create__field">
                            <label for="line" class="po-create__label">Production line</label>
                            <select id="line" name="line" x-model="line" class="po-create__input">
                                <option value="Line 1">Line 1</option>
                                <option value="Line 2">Line 2</option>
                            </select>
                            @error('line')<p class="po-create__error">{{ $message }}</p>@enderror
                        </div>

                        <div class="po-create__field">
                            <label for="shift" class="po-create__label">Shift</label>
                            <select id="shift" name="shift" x-model="shift" class="po-create__input">
                                <option value="Morning">Morning</option>
                                <option value="Evening">Evening</option>
                                <option value="Night">Night</option>
                            </select>
                            @error('shift')<p class="po-create__error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="bom-form__draft-panel">
                        <p class="bom-form__draft-label">Run summary</p>

                        <div class="bom-form__draft-product" x-show="selectedProductId" x-cloak>
                            <span class="erp-po-index-supplier__avatar" x-text="productInitial()"></span>
                            <div class="min-w-0 flex-1">
                                <p class="bom-form__draft-name" x-text="selectedProductName()"></p>
                                <p class="bom-form__draft-recipe">
                                    <span x-text="quantity || '—'"></span> units · <span x-text="line"></span> · <span x-text="shift"></span>
                                </p>
                            </div>
                        </div>

                        <p class="bom-form__draft-hint" x-show="! selectedProductId">
                            Select a product to start this production run.
                        </p>

                        <dl class="bom-form__draft-stats" x-show="selectedProductId" x-cloak>
                            <div class="bom-form__draft-stat">
                                <dt>Material cost / unit</dt>
                                <dd>{{ config('app.currency', 'BDT') }} <span x-text="formatMoney(currentUnitCost())"></span></dd>
                            </div>
                            <div class="bom-form__draft-stat">
                                <dt>Estimated total</dt>
                                <dd>{{ config('app.currency', 'BDT') }} <span x-text="formatMoney(currentTotalCost())"></span></dd>
                            </div>
                            <div class="bom-form__draft-stat" x-show="hasActiveBom() && ! useExistingBatch" x-cloak>
                                <dt>New batch on save</dt>
                                <dd class="font-mono text-xs" x-text="previewBatchCode()"></dd>
                            </div>
                            <div class="bom-form__draft-stat" x-show="appliedRunId" x-cloak>
                                <dt>Loaded from</dt>
                                <dd x-text="selectedRunLabel()"></dd>
                            </div>
                            <div class="bom-form__draft-stat">
                                <dt>On save</dt>
                                <dd x-text="useExistingBatch ? 'Links existing batch lot' : 'Creates new batch lot automatically'"></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <aside class="bom-form__recipe-preview" x-show="selectedProductId" x-cloak>
                    <div class="bom-form__preview-head">
                        <label for="preview_run_id" class="po-create__label mb-0">Previous production runs</label>
                        <p class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                            Preview any past run for this product, then load its settings into the form on the left.
                        </p>
                    </div>

                    <select id="preview_run_id" x-model="previewRunId"
                            class="po-create__input bom-form__preview-select mt-3">
                        <option value="">Start fresh — no previous run</option>
                        <template x-for="run in runsForProduct()" :key="run.id">
                            <option :value="String(run.id)" x-text="runOptionLabel(run)"></option>
                        </template>
                    </select>

                    <div class="bom-form__preview-body" x-show="previewRun()" x-cloak>
                        <div class="bom-form__preview-meta">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-semibold text-brand-600 dark:text-brand-400" x-text="previewRun()?.code"></span>
                                <span x-show="previewRun()?.batch_code"
                                      class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    Batch <span x-text="previewRun()?.batch_code"></span>
                                </span>
                            </div>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                <span x-text="previewRun()?.quantity"></span> units · <span x-text="previewRun()?.line"></span> · <span x-text="previewRun()?.shift"></span>
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="previewRun()?.warehouse_name || 'No warehouse'"></span>
                                · recorded <span x-text="previewRun()?.created_at"></span>
                            </p>
                        </div>

                        <div class="bom-form__preview-table-wrap">
                            <table class="bom-form__preview-table">
                                <thead>
                                    <tr>
                                        <th>Material</th>
                                        <th class="text-right">Required</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="line in previewRunLines()" :key="line.id">
                                        <tr>
                                            <td x-text="line.label"></td>
                                            <td class="text-right tabular-nums" x-text="line.qty"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="bom-form__preview-foot">
                            <span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Est. material cost</span>
                            <span class="text-sm font-bold tabular-nums text-brand-600 dark:text-brand-400">
                                {{ config('app.currency', 'BDT') }} <span x-text="formatMoney(previewRunTotalCost())"></span>
                            </span>
                        </div>

                        <button type="button"
                                @click="usePreviewRun()"
                                :disabled="isPreviewApplied()"
                                :class="isPreviewApplied()
                                    ? 'bom-form__preview-use-btn is-applied'
                                    : 'bom-form__preview-use-btn'"
                                x-text="isPreviewApplied() ? 'Loaded in form on the left' : 'Use this run'">
                        </button>

                        <a :href="previewRun()?.show_url"
                           class="mt-2 block text-center text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                            View full run details →
                        </a>
                    </div>

                    <div class="bom-form__preview-empty" x-show="! previewRun() && runsForProduct().length === 0" x-cloak>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No previous runs for this product yet — enter details on the left.</p>
                    </div>

                    <div class="bom-form__preview-empty" x-show="! previewRun() && runsForProduct().length > 0" x-cloak>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Select a run above to preview materials and settings before loading.</p>
                    </div>
                </aside>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-4 border-t border-gray-100 pt-8 dark:border-gray-800 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="order_number" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Production order No.
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="order_number" name="order_number" x-model="orderNumber"
                               placeholder="Auto-generate if blank"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        <button type="button" id="btn-generate-order-number"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>
                    @error('order_number')<p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>@enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select id="status" name="status" x-model="status"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="planned">Planned</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    @error('status')<p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>@enderror
                </div>

                <!-- Supervisor -->
                <div>
                    <label for="supervisor_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Supervisor</label>
                    <select id="supervisor_id" name="supervisor_id" x-model="supervisorId"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Select supervisor</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">
                                {{ $employee->name }}{{ $employee->job_position ? ' – '.$employee->job_position : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('supervisor_id')<p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>@enderror
                </div>

                <!-- Materials Reserved (Full Width) -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="materials_reserved" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Raw materials reserved</label>
                    <textarea id="materials_reserved" name="materials_reserved" x-model="materialsReserved" rows="2"
                              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                              placeholder="e.g., 24,000 bottles, 1,000 cartons, caps, labels."></textarea>
                    @error('materials_reserved')<p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>@enderror
                </div>

                <!-- Notes (Full Width) -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea id="notes" name="notes" x-model="notes" rows="2"
                              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                              placeholder="Additional information about this production run..."></textarea>
                    @error('notes')<p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>@enderror
                </div>

                <!-- Advanced: existing batch -->
                <div class="sm:col-span-2 lg:col-span-3 rounded-xl border border-dashed border-gray-200 bg-gray-50/60 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <input type="checkbox" id="use_existing_batch" x-model="useExistingBatch"
                               @change="syncExternalWidgets()"
                               class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800">
                        Link to an existing open batch (advanced)
                    </label>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave unchecked to auto-create a new batch lot when you save — recommended for most runs.</p>
                    <div id="existing-batch-field" class="mt-3" x-show="useExistingBatch" x-cloak>
                        <label for="batch_id" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Existing batch</label>
                        <select id="batch_id"
                                name="batch_id"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select open batch for this product…</option>
                        </select>
                        @error('batch_id')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.production.index') }}" 
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Record production run
                </button>
            </div>
        </form>
    </div>

    <!-- Materials Required Section -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                        <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Materials Required for this Run</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Based on the active BOM. Uses the selected warehouse to highlight shortages.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Component</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Required</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Warehouse</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Available</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Shortage</th>
                    </tr>
                </thead>
                <tbody id="materials-required-body" class="divide-y divide-gray-200 dark:divide-gray-800">
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-12 w-12 text-gray-300 dark:text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Select a product and quantity to see material requirements.
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div id="materials-required-summary" class="border-t border-gray-100 px-6 py-4 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-400">
            Select a product and quantity to see summary of required materials and services.
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productionForm', (config) => ({
            currencyCode: @js(config('app.currency', 'BDT')),
            products: config.products || [],
            previousRunsByProduct: config.previousRunsByProduct || {},
            materialRequirements: config.materialRequirements || {},
            productUnitCosts: config.productUnitCosts || {},
            productSkus: config.productSkus || {},
            bomCatalog: config.bomCatalog || {},
            selectedProductId: config.selectedProductId ? String(config.selectedProductId) : '',
            quantity: config.quantity ?? '',
            warehouseId: config.warehouseId ? String(config.warehouseId) : '',
            line: config.line || 'Line 1',
            shift: config.shift || 'Morning',
            supervisorId: config.supervisorId ? String(config.supervisorId) : '',
            status: config.status || 'confirmed',
            notes: config.notes || '',
            materialsReserved: config.materialsReserved || '',
            orderNumber: config.orderNumber || '',
            previewRunId: config.previewRunId ? String(config.previewRunId) : '',
            appliedRunId: config.appliedRunId ? String(config.appliedRunId) : '',
            initialRepeatRunId: config.initialRepeatRunId || null,
            useExistingBatch: @js(old('batch_mode') === 'existing'),
            init() {
                this.$nextTick(() => {
                    if (this.initialRepeatRunId) {
                        this.previewRunId = String(this.initialRepeatRunId);
                        this.usePreviewRun(true);
                    } else if (this.selectedProductId && ! this.previewRunId) {
                        this.previewRunId = this.defaultPreviewRunId();
                    }
                    this.syncExternalWidgets();
                });
            },
            productLabel(product) {
                const sku = (product.sku || '').trim();
                const name = (product.name || '').trim();
                return sku ? `${sku} – ${name}` : name;
            },
            productMap() {
                return Object.fromEntries(this.products.map((p) => [String(p.id), p]));
            },
            selectedProductName() {
                const match = this.productMap()[String(this.selectedProductId)];
                return match ? this.productLabel(match) : 'Select finished product';
            },
            productInitial() {
                const name = this.selectedProductName();
                if (name === 'Select finished product') return '?';
                return name.charAt(0).toUpperCase();
            },
            runsForProduct() {
                if (! this.selectedProductId) return [];
                return this.previousRunsByProduct[String(this.selectedProductId)] || [];
            },
            runOptionLabel(run) {
                const batch = run.batch_code ? ` · ${run.batch_code}` : '';
                return `${run.code} · ${run.quantity} units${batch}`;
            },
            defaultPreviewRunId() {
                const runs = this.runsForProduct();
                return runs.length ? String(runs[0].id) : '';
            },
            previewRun() {
                if (! this.previewRunId) return null;
                return this.runsForProduct().find(
                    (item) => String(item.id) === String(this.previewRunId)
                ) || null;
            },
            previewRunLines() {
                const run = this.previewRun();
                if (! run || ! this.selectedProductId) return [];

                const components = this.materialRequirements[String(this.selectedProductId)] || [];
                const qty = parseFloat(run.quantity) || 0;

                return components.map((comp) => {
                    const required = (parseFloat(comp.quantity_per_unit) || 0) * qty;
                    const label = comp.sku ? `${comp.sku} — ${comp.name || ''}` : (comp.name || 'Material');

                    return {
                        id: comp.product_id,
                        label,
                        qty: `${required.toFixed(2)} ${comp.uom || ''}`.trim(),
                    };
                });
            },
            previewRunTotalCost() {
                const run = this.previewRun();
                if (! run) return 0;

                if (run.material_total_cost !== null && run.material_total_cost !== undefined) {
                    return parseFloat(run.material_total_cost) || 0;
                }

                const unitCost = this.productUnitCosts[String(this.selectedProductId)] || 0;
                return unitCost * (parseFloat(run.quantity) || 0);
            },
            currentUnitCost() {
                const cost = this.productUnitCosts[String(this.selectedProductId)];
                return cost !== undefined && cost !== null ? cost : null;
            },
            currentTotalCost() {
                const unitCost = this.currentUnitCost();
                const qty = parseFloat(this.quantity) || 0;
                if (unitCost === null || ! qty) return null;
                return unitCost * qty;
            },
            hasActiveBom() {
                return !!(this.bomCatalog[String(this.selectedProductId)] ?? null);
            },
            previewBatchCode() {
                if (! this.selectedProductId) return '—';
                const sku = (this.productSkus[String(this.selectedProductId)] || ('P' + this.selectedProductId))
                    .replace(/[^A-Z0-9]/gi, '')
                    .toUpperCase();
                const dateKey = new Date().toISOString().slice(0, 10).replace(/-/g, '');
                return `${sku}-${dateKey}-001`;
            },
            formatMoney(value) {
                if (value === null || value === undefined || value === '') return '—';
                const amount = parseFloat(value);
                if (Number.isNaN(amount)) return '—';
                return amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            selectedRunLabel() {
                const run = this.runsForProduct().find(
                    (item) => String(item.id) === String(this.appliedRunId)
                );
                return run ? `${run.code} · ${run.quantity} units` : '';
            },
            isPreviewApplied() {
                return !!this.appliedRunId && String(this.appliedRunId) === String(this.previewRunId);
            },
            onProductChange() {
                this.previewRunId = this.defaultPreviewRunId();
                this.appliedRunId = '';
                this.syncExternalWidgets();
            },
            usePreviewRun(focusQuantity = true) {
                const run = this.previewRun();
                if (! run) return;

                this.quantity = String(run.quantity || '');
                this.line = run.line || 'Line 1';
                this.shift = run.shift || 'Morning';
                this.warehouseId = run.warehouse_id ? String(run.warehouse_id) : this.warehouseId;
                this.supervisorId = run.supervisor_id ? String(run.supervisor_id) : '';
                this.notes = run.notes || '';
                this.materialsReserved = run.materials_reserved || '';
                this.appliedRunId = String(run.id);
                this.syncExternalWidgets();

                if (focusQuantity) {
                    this.$nextTick(() => {
                        const quantityInput = document.getElementById('quantity');
                        quantityInput?.focus();
                        quantityInput?.select();
                    });
                }
            },
            syncExternalWidgets() {
                window.dispatchEvent(new CustomEvent('production-form-sync'));
            },
        }));
    });

    document.addEventListener('DOMContentLoaded', () => {
        const productUnitCosts = @json($productUnitCosts ?? []);
        const materialRequirements = @json($materialRequirements ?? []);
        const warehouseStock = @json($warehouseStock ?? []);
        const warehouseNames = @json($warehouses->pluck('name','id'));
        const bomCatalog = @json($bomCatalog ?? []);
        const productSkus = @json($productSkus ?? []);
        const openBatchesByProduct = @json($openBatchesByProduct ?? []);
        const productSelect = document.getElementById('product_id');
        const quantityInput = document.getElementById('quantity');
        const warehouseSelect = document.getElementById('warehouse_id');
        const materialsBody = document.getElementById('materials-required-body');
        const materialsSummary = document.getElementById('materials-required-summary');
        const bomStatusBanner = document.getElementById('bom-status-banner');
        const useExistingBatch = document.getElementById('use_existing_batch');
        const existingBatchField = document.getElementById('existing-batch-field');
        const batchModeInput = document.getElementById('batch_mode');
        const batchSelect = document.getElementById('batch_id');

        function populateOpenBatches() {
            if (!batchSelect || !productSelect) return;

            const productId = productSelect.value;
            const batches = openBatchesByProduct[productId] || [];
            const oldBatchId = @json(old('batch_id'));

            batchSelect.innerHTML = '<option value="">Select open batch for this product…</option>';
            batches.forEach((batch) => {
                const option = document.createElement('option');
                option.value = batch.id;
                option.textContent = `${batch.code}${batch.production_date ? ' · ' + batch.production_date : ''}`;
                batchSelect.appendChild(option);
            });

            if (oldBatchId) {
                batchSelect.value = String(oldBatchId);
            }
        }

        function syncBatchMode() {
            const useExisting = useExistingBatch?.checked;
            if (batchModeInput) {
                batchModeInput.value = useExisting ? 'existing' : 'auto';
            }
            if (batchSelect) {
                batchSelect.disabled = !useExisting;
                if (!useExisting) {
                    batchSelect.value = '';
                }
            }
        }

        function updateBomStatusBanner() {
            if (!bomStatusBanner) return;
            const productId = productSelect.value;
            if (!productId) {
                bomStatusBanner.className = 'rounded-2xl border px-5 py-4 text-sm border-gray-200 bg-gray-50 text-gray-600 dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-400';
                bomStatusBanner.innerHTML = 'Select a product to see whether an active manufacturing recipe is loaded.';
                return;
            }

            const bom = bomCatalog[productId] ?? null;
            if (bom) {
                bomStatusBanner.className = 'rounded-2xl border px-5 py-4 text-sm border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-100';
                bomStatusBanner.innerHTML = `
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold">Active recipe loaded — no new BOM needed</p>
                            <p class="mt-1">${bom.name} · ${bom.items_count} component${bom.items_count === 1 ? '' : 's'}. Materials below are calculated from this recipe.</p>
                        </div>
                        <div class="flex flex-wrap gap-2 shrink-0">
                            <a href="${bom.show_url}" class="inline-flex items-center rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 dark:border-emerald-500/40 dark:bg-emerald-950/40 dark:text-emerald-100">View recipe</a>
                            <a href="${bom.edit_url}" class="inline-flex items-center rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 dark:border-emerald-500/40 dark:bg-emerald-950/40 dark:text-emerald-100">Edit recipe</a>
                        </div>
                    </div>`;
                return;
            }

            const createUrl = @json(route('admin.boms.create')) + '?product_id=' + encodeURIComponent(productId);
            bomStatusBanner.className = 'rounded-2xl border px-5 py-4 text-sm border-amber-200 bg-amber-50 text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100';
            bomStatusBanner.innerHTML = `
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold">No active recipe for this product</p>
                        <p class="mt-1">You can still record production, but materials will not auto-calculate or deduct until a BOM exists.</p>
                    </div>
                    <a href="${createUrl}" class="inline-flex shrink-0 items-center rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">Create BOM</a>
                </div>`;
        }

        function refreshProductionWidgets() {
            updateBomStatusBanner();
            populateOpenBatches();
            syncBatchMode();
            updateMaterialRequirements();
        }

        function updateMaterialRequirements() {
            const productId = productSelect.value;
            const quantity = parseFloat(quantityInput.value) || 0;
            const warehouseId = warehouseSelect.value || '{{ $defaultWarehouseId ?? '' }}';

            const components = materialRequirements[productId] ?? null;
            if (!materialsBody) return;

            materialsBody.innerHTML = '';

            const rawDetails = {};
            let labourRequired = 0;
            let utilitiesRequired = 0;
            let labourShortage = 0;
            let utilitiesShortage = 0;
            let hasComponents = false;

            if (!components || components.length === 0 || !quantity) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="5" class="px-4 py-8 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="h-12 w-12 text-gray-300 dark:text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                ${!productId ? 'Select a product' : !quantity ? 'Enter quantity' : 'No BOM defined for this product'}.
                            </p>
                        </div>
                    </td>
                `;
                materialsBody.appendChild(row);
                return;
            }

            components.forEach((comp) => {
                const required = comp.quantity_per_unit * quantity;
                const available = (warehouseStock[warehouseId] && warehouseStock[warehouseId][comp.product_id])
                    ? warehouseStock[warehouseId][comp.product_id]
                    : 0;
                const shortage = Math.max(0, required - available);
                const warehouseName = warehouseNames[warehouseId] || 'Selected warehouse';
                const type = (comp.type || '').toString().toLowerCase();

                // Collect raw material details by SKU/name
                if (type === 'raw') {
                    const key = comp.sku || comp.name || 'Raw material';
                    rawDetails[key] = (rawDetails[key] || 0) + required;
                }

                // Sum services separately
                if (comp.sku && comp.sku.startsWith('SV-LAB')) {
                    labourRequired += required;
                    if (shortage > 0) labourShortage += shortage;
                } else if (comp.sku && comp.sku.startsWith('SV-UTIL')) {
                    utilitiesRequired += required;
                    if (shortage > 0) utilitiesShortage += shortage;
                }

                const row = document.createElement('tr');
                row.className = shortage > 0 ? 'bg-error-50/30 dark:bg-error-500/5' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50';
                
                row.innerHTML = `
                    <td class="px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                ${comp.sku ? comp.sku + ' — ' : ''}${comp.name ?? ''}
                            </p>
                            ${comp.type ? `<span class="text-xs text-gray-500 dark:text-gray-400">${comp.type}</span>` : ''}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            ${required.toFixed(2)} ${comp.uom ?? ''}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                        ${warehouseName}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm ${available >= required ? 'text-gray-700 dark:text-gray-300' : 'text-error-600 dark:text-error-500'}">
                            ${available.toFixed(2)} ${comp.uom ?? ''}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        ${shortage > 0 ? 
                            `<span class="inline-flex items-center rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/20 dark:text-error-400">
                                ${shortage.toFixed(2)} ${comp.uom ?? ''}
                            </span>` : 
                            `<span class="text-xs text-success-600 dark:text-success-400">OK</span>`
                        }
                    </td>
                `;
                materialsBody.appendChild(row);
                hasComponents = true;
            });

            if (materialsSummary && hasComponents) {
                const lines = [];

                if (Object.keys(rawDetails).length) {
                    const rawLine = Object.entries(rawDetails)
                        .map(([name, qty]) => `${qty.toFixed(2)} × ${name}`)
                        .join(', ');
                    lines.push(`<span class="font-medium">Raw materials:</span> ${rawLine}`);
                }

                lines.push(
                    `<span class="font-medium">Labour:</span> ${labourRequired.toFixed(2)} day (shortage: ${labourShortage.toFixed(2)})`
                );

                lines.push(
                    `<span class="font-medium">Utilities:</span> ${utilitiesRequired.toFixed(2)} shift (shortage: ${utilitiesShortage.toFixed(2)})`
                );

                materialsSummary.innerHTML = lines.join('<br>');
            } else {
                materialsSummary.textContent = 'Select a product and quantity to see summary of required materials and services.';
            }
        }

        window.addEventListener('production-form-sync', refreshProductionWidgets);
        if (useExistingBatch) useExistingBatch.addEventListener('change', syncBatchMode);

        refreshProductionWidgets();
        
        // Generate order number button
        const generateBtn = document.getElementById('btn-generate-order-number');
        const orderNumberInput = document.getElementById('order_number');
        
        if (generateBtn && orderNumberInput) {
            generateBtn.addEventListener('click', () => {
                const date = new Date();
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                orderNumberInput.value = `PO-${year}${month}${day}-${random}`;
                orderNumberInput.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }
    });
</script>
@endpush
@endsection