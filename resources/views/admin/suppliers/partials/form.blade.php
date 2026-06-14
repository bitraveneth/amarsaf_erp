@php

    $supplier = $supplier ?? null;

    $fieldClass = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400';

    $labelClass = 'mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300';

@endphp



<div class="space-y-8">

    <div>

        <h3 class="text-base font-medium text-gray-900 dark:text-white">Contact & tax details</h3>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Basic vendor information for purchase orders and bills.</p>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div class="sm:col-span-2">

                <label for="name" class="{{ $labelClass }}">Name <span class="text-error-500">*</span></label>

                <input type="text" id="name" name="name" value="{{ old('name', $supplier->name ?? '') }}" required

                       class="{{ $fieldClass }}" placeholder="e.g., ABC Corporation Ltd.">

                @error('name')

                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>

                @enderror

            </div>



            <div>

                <label for="contact_person" class="{{ $labelClass }}">Contact person</label>

                <input type="text" id="contact_person" name="contact_person"

                       value="{{ old('contact_person', $supplier->contact_person ?? '') }}"

                       class="{{ $fieldClass }}" placeholder="e.g., Md. Rahim Uddin">

                @error('contact_person')

                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>

                @enderror

            </div>



            <div>

                <label for="email" class="{{ $labelClass }}">Email</label>

                <input type="email" id="email" name="email"

                       value="{{ old('email', $supplier->email ?? '') }}"

                       class="{{ $fieldClass }}" placeholder="contact@supplier.com">

                @error('email')

                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>

                @enderror

            </div>



            <div>

                <label for="phone" class="{{ $labelClass }}">Phone</label>

                <input type="text" id="phone" name="phone"

                       value="{{ old('phone', $supplier->phone ?? '') }}"

                       class="{{ $fieldClass }}" placeholder="+880 1XXX-XXXXXX">

                @error('phone')

                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>

                @enderror

            </div>



            <div>

                <label for="tax_id" class="{{ $labelClass }}">Tax ID / BIN</label>

                <input type="text" id="tax_id" name="tax_id"

                       value="{{ old('tax_id', $supplier->tax_id ?? '') }}"

                       class="{{ $fieldClass }}" placeholder="e.g., 123456789012">

                @error('tax_id')

                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>

                @enderror

            </div>



            <div class="sm:col-span-2">

                <label for="address" class="{{ $labelClass }}">Address</label>

                <textarea id="address" name="address" rows="3" class="{{ $fieldClass }}"

                          placeholder="Street address, city, postal code">{{ old('address', $supplier->address ?? '') }}</textarea>

                @error('address')

                    <p class="mt-1 text-sm text-error-600">{{ $message }}</p>

                @enderror

            </div>

        </div>

    </div>



    @if(!empty($productCategories) && $productCategories->isNotEmpty())

        @php

            $selectedCategoryIds = old('product_category_ids', isset($supplier) ? $supplier->productCategories->pluck('id')->all() : []);

            $categoryOptions = $productCategories->map(fn ($category) => [

                'id' => $category->id,

                'name' => $category->name,

                'description' => $category->description,

            ])->values();

        @endphp

        <div class="border-t border-gray-100 pt-8 dark:border-gray-800"

             x-data="supplierCategoryPicker(@js($categoryOptions), @js(array_values(array_map('intval', $selectedCategoryIds))))">

            <h3 class="text-base font-medium text-gray-900 dark:text-white">Purchase categories</h3>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select a category from the dropdown and add it as a line item.</p>



            <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">

                <div class="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/80 p-4 dark:border-gray-800 dark:bg-gray-800/40 sm:flex-row sm:items-center">

                    <select id="purchase_category_picker" x-model="picker"

                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-900 dark:text-white sm:flex-1">

                        <option value="">Select purchase category…</option>

                        <template x-for="category in availableCategories()" :key="category.id">

                            <option :value="category.id" x-text="category.name"></option>

                        </template>

                    </select>

                    <button type="button" @click="addSelected()"

                            :disabled="!picker"

                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>

                        </svg>

                        Add

                    </button>

                </div>



                <table class="w-full">

                    <thead class="border-b border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">

                        <tr>

                            <th class="w-12 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>

                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>

                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>

                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">

                        <template x-if="selected.length === 0">

                            <tr>

                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">

                                    No categories added yet.

                                </td>

                            </tr>

                        </template>

                        <template x-for="(item, index) in selected" :key="item.id">

                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40">

                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400" x-text="index + 1"></td>

                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white" x-text="item.name"></td>

                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400" x-text="item.description || '—'"></td>

                                <td class="px-4 py-3 text-right">

                                    <input type="hidden" name="product_category_ids[]" :value="item.id">

                                    <x-admin.action-group class="justify-end">

                                        <button type="button" @click="remove(item.id)" class="erp-btn-action-danger">

                                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>

                                            </svg>

                                            Delete

                                        </button>

                                    </x-admin.action-group>

                                </td>

                            </tr>

                        </template>

                    </tbody>

                </table>

            </div>



            @error('product_category_ids')

                <p class="mt-2 text-sm text-error-600">{{ $message }}</p>

            @enderror

            @error('product_category_ids.*')

                <p class="mt-2 text-sm text-error-600">{{ $message }}</p>

            @enderror

        </div>

    @else

        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/50 p-5 dark:border-gray-700 dark:bg-gray-800/30">

            <p class="text-sm text-gray-600 dark:text-gray-400">

                No purchase categories defined yet.

                <a href="{{ route('admin.suppliers.index') }}#purchase-categories" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Add categories</a>

                on the suppliers page first.

            </p>

        </div>

    @endif

</div>



@once

    @push('scripts')

        <script>

            document.addEventListener('alpine:init', () => {

                Alpine.data('supplierCategoryPicker', (allCategories, initialIds) => ({

                    allCategories,

                    selected: [],

                    picker: '',



                    init() {

                        const idSet = new Set((initialIds || []).map(Number));

                        this.selected = this.allCategories.filter((category) => idSet.has(Number(category.id)));

                    },



                    availableCategories() {

                        const selectedIds = new Set(this.selected.map((item) => Number(item.id)));

                        return this.allCategories.filter((category) => !selectedIds.has(Number(category.id)));

                    },



                    addSelected() {

                        const id = Number(this.picker);

                        if (!id) return;



                        const category = this.allCategories.find((item) => Number(item.id) === id);

                        if (!category) return;



                        this.selected.push({ ...category });

                        this.picker = '';

                    },



                    remove(id) {

                        this.selected = this.selected.filter((item) => Number(item.id) !== Number(id));

                    },

                }));

            });

        </script>

    @endpush

@endonce

