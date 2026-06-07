@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Material Categories</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Define what kind of raw material you buy — preforms, labels, minerals, caps, and more. Add new types anytime.
            </p>
        </div>
        <a href="{{ route('admin.materials.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            Back to Materials
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add Category</h3>
        </div>
        <form action="{{ route('admin.material-categories.store') }}" method="POST" class="p-6">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Category name <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. Preform, BOPP Label, Potassium"
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('name')
                        <p class="mt-1 text-sm text-error-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="group" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Group (optional)</label>
                    <input type="text" id="group" name="group" value="{{ old('group') }}" list="category-groups"
                           placeholder="e.g. Minerals"
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <datalist id="category-groups">
                        @foreach($groups as $group)
                            <option value="{{ $group }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                        Add category
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($categories->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Defined Categories</h3>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $categories->total() }} {{ Str::plural('category', $categories->total()) }}
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Group</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Materials</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($categories as $category)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $category->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $category->group ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $category->products_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form action="{{ route('admin.material-categories.destroy', $category) }}" method="POST"
                                          onsubmit="return confirm('Delete category {{ $category->name }}?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="erp-btn-action-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($categories, 'links'))
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-400">No categories yet. Add your first one above.</p>
        </div>
    @endif
</div>
@endsection
