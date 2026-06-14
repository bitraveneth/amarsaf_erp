@extends('layouts.app')

@section('content')
@php
    $editingId = old('_packaging_type_id');
    $snapshotCards = [
        [
            'label' => 'Standard types',
            'numeric' => number_format($systemTypes),
            'caption' => '500ml×24, 1L×12, 2L×6, jar',
            'href' => route('admin.packaging.index') . '#packaging-types',
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'Custom types',
            'numeric' => number_format($customTypes),
            'caption' => 'Added when you need more',
            'href' => route('admin.packaging.index') . '#packaging-types',
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'users',
        ],
        [
            'label' => 'Linked products',
            'numeric' => number_format($linkedProducts),
            'caption' => 'Finished SKUs with packaging',
            'href' => route('admin.products.index'),
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
    ];
@endphp

<div class="dash-page">
    <h1 class="erp-dash-h1">Packaging</h1>

    <x-dashboard.snapshot-kpis
        class="mt-2"
        :show-header="false"
        :cards="$snapshotCards"
    />

    <section id="packaging-types"
             class="mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
             x-data="{
                showAddForm: {{ (($errors->has('name') || $errors->has('code')) && ! $errors->has('_packaging_type_id')) ? 'true' : 'false' }},
                editingId: {{ $editingId ? (int) $editingId : 'null' }},
                openAddForm() { this.showAddForm = true; this.editingId = null; },
                closeAddForm() { this.showAddForm = false; this.$refs.addForm?.reset(); },
                startEdit(id) { this.editingId = id; this.showAddForm = false; },
                cancelEdit() { this.editingId = null; }
             }">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Packaging types</h2>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        {{ number_format($packagingTypes->count()) }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Standard and custom packaging linked to products.
                </p>
            </div>
            <button type="button"
                    x-show="!showAddForm"
                    @click="openAddForm()"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Add packaging type
            </button>
        </div>

        <div x-show="showAddForm"
             x-cloak
             class="border-b border-gray-100 bg-gray-50/80 px-5 py-4 dark:border-gray-800 dark:bg-gray-800/30 sm:px-6">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">New packaging type</h3>
                <button type="button" @click="closeAddForm()" class="erp-btn-cancel">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Cancel
                </button>
            </div>
            <form x-ref="addForm" action="{{ route('admin.packaging.store') }}" method="POST"
                  class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                @csrf
                <div class="lg:col-span-2">
                    <label for="code" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Code <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" required
                           placeholder="e.g. CTN-48-500ML"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 font-mono text-sm uppercase text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div class="lg:col-span-3">
                    <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Name <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. Carton 48 x 500ml"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div class="lg:col-span-2">
                    <label for="unit" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
                    <input type="text" id="unit" name="unit" value="{{ old('unit') }}"
                           placeholder="carton"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div class="lg:col-span-3">
                    <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <input type="text" id="description" name="description" value="{{ old('description') }}"
                           placeholder="Optional"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div class="lg:col-span-2 lg:flex lg:justify-end">
                    <button type="submit" class="erp-btn-primary w-full lg:w-auto">Save</button>
                </div>
            </form>
            @if(! old('_packaging_type_id') && ($errors->has('code') || $errors->has('name')))
                <p class="mt-2 text-sm text-error-600 dark:text-error-500">{{ $errors->first('code') ?: $errors->first('name') }}</p>
            @endif
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full table-fixed">
                <colgroup>
                    <col style="width: 14%">
                    <col style="width: 30%">
                    <col style="width: 14%">
                    <col style="width: 10%">
                    <col style="width: 32%">
                </colgroup>
                <thead class="border-b border-gray-100 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Contents</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Products</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($packagingTypes as $type)
                        <tr class="transition hover:bg-gray-50/80 dark:hover:bg-gray-800/40" x-show="editingId !== {{ $type->id }}">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-sm font-medium text-gray-900 dark:text-white" title="{{ $type->code }}">
                                    {{ $type->code }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="truncate text-sm font-medium text-gray-900 dark:text-white" title="{{ $type->name }}">
                                        {{ $type->name }}
                                    </span>
                                    @if($type->is_system)
                                        <span class="inline-flex shrink-0 rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">Standard</span>
                                    @else
                                        <span class="inline-flex shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Custom</span>
                                    @endif
                                </div>
                                @if($type->description)
                                    <p class="mt-1 truncate text-theme-xs text-gray-500 dark:text-gray-400" title="{{ $type->description }}">
                                        {{ $type->description }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                {{ \App\Support\WaterProductLineCatalog::packSummaryForUi($type->unit, $type->units_per_pack) }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($type->products_count > 0)
                                    <a href="{{ route('admin.products.index', ['packaging_type' => $type->id]) }}"
                                       class="text-sm font-medium tabular-nums text-brand-600 hover:text-brand-700 hover:underline dark:text-brand-400">
                                        {{ number_format($type->products_count) }}
                                    </a>
                                @else
                                    <span class="text-sm tabular-nums text-gray-400 dark:text-gray-500">0</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex justify-end">
                                    <x-admin.action-group>
                                        <button type="button"
                                                @click="startEdit({{ $type->id }})"
                                                class="erp-btn-action">
                                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </button>
                                        <x-admin.action-delete
                                            :action="route('admin.packaging.destroy', $type)"
                                            confirm="Delete packaging type {{ $type->name }}?{{ $type->is_system ? ' Standard SAF types are protected and cannot be removed.' : '' }}"
                                        />
                                    </x-admin.action-group>
                                </div>
                            </td>
                        </tr>
                        <tr class="bg-brand-50/30 dark:bg-brand-500/5" x-show="editingId === {{ $type->id }}" x-cloak>
                            <td colspan="5" class="px-5 py-4">
                                <form action="{{ route('admin.packaging.update', $type) }}" method="POST"
                                      class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="_packaging_type_id" value="{{ $type->id }}">
                                    @if($type->is_system)
                                        <div class="lg:col-span-2">
                                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                                            <input type="text" value="{{ $type->code }}" readonly
                                                   class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 py-2.5 font-mono text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-400">
                                        </div>
                                        <div class="lg:col-span-3">
                                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                            <input type="text" value="{{ $type->name }}" readonly
                                                   class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-400">
                                        </div>
                                        <div class="lg:col-span-2">
                                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
                                            <input type="text" name="unit" value="{{ old('unit', $type->unit) }}"
                                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        </div>
                                        <div class="lg:col-span-3">
                                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                            <input type="text" name="description" value="{{ old('description', $type->description) }}"
                                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        </div>
                                    @else
                                        <div class="lg:col-span-2">
                                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                                            <input type="text" name="code" value="{{ old('code', $type->code) }}" required
                                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 font-mono text-sm uppercase text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        </div>
                                        <div class="lg:col-span-3">
                                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                            <input type="text" name="name" value="{{ old('name', $type->name) }}" required
                                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        </div>
                                        <div class="lg:col-span-2">
                                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
                                            <input type="text" name="unit" value="{{ old('unit', $type->unit) }}"
                                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        </div>
                                        <div class="lg:col-span-3">
                                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                            <input type="text" name="description" value="{{ old('description', $type->description) }}"
                                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        </div>
                                    @endif
                                    <div class="flex gap-2 lg:col-span-2 lg:justify-end">
                                        <button type="button" @click="cancelEdit()" class="erp-btn-cancel">
                                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Cancel
                                        </button>
                                        <button type="submit" class="erp-btn-primary">Save</button>
                                    </div>
                                </form>
                                @if(old('_packaging_type_id') == $type->id && $errors->any())
                                    <p class="mt-2 text-sm text-error-600 dark:text-error-500">{{ $errors->first() }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No packaging types yet.</p>
                                <button type="button" @click="openAddForm()"
                                        class="mt-3 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                                    Add packaging type
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
