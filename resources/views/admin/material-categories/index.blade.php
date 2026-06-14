@extends('layouts.app')

@section('content')
@php
    use App\Support\RawMaterialLineCatalog;

    $groupOptions = RawMaterialLineCatalog::categoryGroups();
    $editingId = old('_material_category_id');
    $snapshotCards = [
        [
            'label' => 'Categories',
            'numeric' => number_format($categories->count()),
            'caption' => '16 standard types',
            'href' => route('admin.material-categories.index') . '#categories-list',
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'Groups',
            'numeric' => number_format($groupedCategories->count()),
            'caption' => 'Preforms, Labels, Chemicals…',
            'href' => route('admin.material-categories.index') . '#categories-list',
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'users',
        ],
        [
            'label' => 'Linked materials',
            'numeric' => number_format($linkedMaterials),
            'caption' => 'Raw / service / in-house',
            'href' => route('admin.materials.index'),
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
    ];
@endphp

<div class="dash-page">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="erp-dash-h1">Material Categories</h1>
        <a href="{{ route('admin.materials.index') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            Back to Materials
        </a>
    </div>

    <x-dashboard.snapshot-kpis class="mt-2" :show-header="false" :cards="$snapshotCards" />

    <section id="categories-list"
             class="mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
             x-data="{
                showAddForm: {{ (($errors->has('code') || $errors->has('name')) && ! $errors->has('_material_category_id')) ? 'true' : 'false' }},
                editingId: {{ $editingId ? (int) $editingId : 'null' }},
                startEdit(id) { this.editingId = id; this.showAddForm = false; },
                cancelEdit() { this.editingId = null; }
             }">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Category list</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Same groups as the material form dropdown.</p>
            </div>
            <button type="button" x-show="!showAddForm" @click="showAddForm = true"
                    class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                Add category
            </button>
        </div>

        <div x-show="showAddForm" x-cloak class="border-b border-gray-100 bg-gray-50/80 px-5 py-4 dark:border-gray-800 dark:bg-gray-800/30 sm:px-6">
            <form action="{{ route('admin.material-categories.store') }}" method="POST" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                @csrf
                <div class="lg:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="MY-CAT"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 font-mono text-sm uppercase dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div class="lg:col-span-3">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. BOPP Label"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div class="lg:col-span-3">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Group</label>
                    <select name="group" class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @foreach($groupOptions as $group)
                            <option value="{{ $group }}" @selected(old('group') === $group)>{{ $group }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 lg:col-span-2 lg:justify-end">
                    <button type="button" @click="showAddForm = false" class="erp-btn-cancel">Cancel</button>
                    <button type="submit" class="erp-btn-primary">Save</button>
                </div>
            </form>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($groupedCategories as $groupName => $items)
                <div>
                    <div class="bg-gray-50/80 px-5 py-3 text-sm font-semibold text-gray-900 dark:bg-gray-800/50 dark:text-white">
                        {{ $groupName }}
                        <span class="ml-2 text-xs font-normal text-gray-500 dark:text-gray-400">({{ $items->count() }})</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b border-gray-100 dark:border-gray-800">
                                <tr>
                                    <th class="px-5 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Code</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Category</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Materials</th>
                                    <th class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($items as $category)
                                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30" x-show="editingId !== {{ $category->id }}">
                                        <td class="px-5 py-3 font-mono text-sm text-gray-900 dark:text-white">{{ $category->code ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $category->name }}</span>
                                                @if($category->is_system)
                                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">Standard</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm tabular-nums">{{ number_format($category->products_count) }}</td>
                                        <td class="px-5 py-3 text-right">
                                            <x-admin.action-group>
                                                <button type="button" @click="startEdit({{ $category->id }})" class="erp-btn-action">Edit</button>
                                                <x-admin.action-delete
                                                    :action="route('admin.material-categories.destroy', $category)"
                                                    confirm="Delete {{ $category->name }}?"
                                                />
                                            </x-admin.action-group>
                                        </td>
                                    </tr>
                                    <tr class="bg-brand-50/30 dark:bg-brand-500/5" x-show="editingId === {{ $category->id }}" x-cloak>
                                        <td colspan="4" class="px-5 py-4">
                                            <form action="{{ route('admin.material-categories.update', $category) }}" method="POST" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="_material_category_id" value="{{ $category->id }}">
                                                <div class="lg:col-span-2">
                                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                                                    <input type="text" name="code" value="{{ old('code', $category->code) }}" @readonly($category->is_system)
                                                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 font-mono text-sm uppercase dark:border-gray-700 dark:bg-gray-900 dark:text-white {{ $category->is_system ? 'cursor-not-allowed bg-gray-100 dark:bg-gray-800/50' : '' }}">
                                                </div>
                                                <div class="lg:col-span-3">
                                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                                    <input type="text" name="name" value="{{ old('name', $category->name) }}" @readonly($category->is_system) required
                                                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white {{ $category->is_system ? 'cursor-not-allowed bg-gray-100 dark:bg-gray-800/50' : '' }}">
                                                </div>
                                                <div class="lg:col-span-3">
                                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Group</label>
                                                    <select name="group" class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                                        @foreach($groupOptions as $group)
                                                            <option value="{{ $group }}" @selected(old('group', $category->group) === $group)>{{ $group }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="flex gap-2 lg:col-span-2 lg:justify-end">
                                                    <button type="button" @click="cancelEdit()" class="erp-btn-cancel">Cancel</button>
                                                    <button type="submit" class="erp-btn-primary">Save</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
