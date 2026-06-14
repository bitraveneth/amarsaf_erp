<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
     id="purchase-categories"
     x-data="{
        showAddForm: {{ ($errors->has('name') && ! $errors->has('product_category_ids')) ? 'true' : 'false' }},
        editingId: null,
        cancelEdit() { this.editingId = null; },
        openAddForm() { this.showAddForm = true; this.editingId = null; },
        closeAddForm() { this.showAddForm = false; }
     }">
    <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Categories</h2>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                {{ $productCategories->count() }}
            </span>
        </div>
        <button type="button"
                @click="showAddForm ? closeAddForm() : openAddForm()"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span x-text="showAddForm ? 'Cancel' : 'Add category'"></span>
        </button>
    </div>

    <div x-show="showAddForm"
         x-cloak
         class="border-b border-gray-100 bg-gray-50/80 px-6 py-5 dark:border-gray-800 dark:bg-gray-800/30">
        <form action="{{ route('admin.supplier-product-categories.store') }}" method="POST"
              class="grid grid-cols-1 gap-4 sm:grid-cols-12 sm:items-end">
            @csrf
            <div class="sm:col-span-4">
                <label for="new_category_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Category name <span class="text-error-500">*</span>
                </label>
                <input type="text" id="new_category_name" name="name" value="{{ old('name') }}" required
                       placeholder="e.g. PET Preforms"
                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div class="sm:col-span-5">
                <label for="new_category_description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <input type="text" id="new_category_description" name="description" value="{{ old('description') }}"
                       placeholder="Optional note"
                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div class="sm:col-span-3 sm:flex sm:justify-end">
                <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 sm:w-auto">
                    Save category
                </button>
            </div>
        </form>
        @error('name')
            <p class="mt-2 text-sm text-error-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px]">
            <thead class="border-b border-gray-100 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/50">
                <tr>
                    <th class="w-12 px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Suppliers</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($productCategories as $index => $category)
                    <tr class="transition hover:bg-gray-50/80 dark:hover:bg-gray-800/40" x-show="editingId !== {{ $category->id }}">
                        <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                        <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $category->name }}</td>
                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $category->description ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $category->suppliers_count }}</td>
                        <td class="px-5 py-4 text-right">
                            <x-admin.action-group>
                                <button type="button"
                                        @click="editingId = {{ $category->id }}; showAddForm = false"
                                        class="erp-btn-action">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </button>
                                <x-admin.action-delete
                                    :action="route('admin.supplier-product-categories.destroy', $category)"
                                    confirm="Delete category {{ $category->name }}? It will be removed from all suppliers."
                                />
                            </x-admin.action-group>
                        </td>
                    </tr>
                    <tr class="bg-brand-50/30 dark:bg-brand-500/5" x-show="editingId === {{ $category->id }}" x-cloak>
                        <td colspan="5" class="px-5 py-4">
                            <form action="{{ route('admin.supplier-product-categories.update', $category) }}" method="POST"
                                  class="grid grid-cols-1 gap-4 sm:grid-cols-12 sm:items-end">
                                @csrf
                                @method('PATCH')
                                <div class="sm:col-span-4">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Category name</label>
                                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div class="sm:col-span-5">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                    <input type="text" name="description" value="{{ old('description', $category->description) }}"
                                           placeholder="Optional"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                                <div class="flex gap-2 sm:col-span-3 sm:justify-end">
                                    <button type="button" @click="cancelEdit()"
                                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                            class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                                        Save
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No categories yet.</p>
                            <button type="button" @click="openAddForm()"
                                    class="mt-3 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add category
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
