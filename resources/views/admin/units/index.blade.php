@extends('layouts.app')

@section('content')
@php
    $editingId = old('_unit_id');
    $snapshotCards = [
        [
            'label' => 'Standard units',
            'numeric' => number_format($systemUnits),
            'caption' => 'Piece, litre, kg, day…',
            'href' => route('admin.units.index') . '#units-list',
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'Custom units',
            'numeric' => number_format($customUnits),
            'caption' => 'Added when you need more',
            'href' => route('admin.units.index') . '#units-list',
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'users',
        ],
        [
            'label' => 'Used on materials',
            'numeric' => number_format($linkedMaterials),
            'caption' => 'Distinct UOM codes in use',
            'href' => route('admin.materials.index'),
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
    ];
@endphp

<div class="dash-page">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="erp-dash-h1">Units of Measure</h1>
        <a href="{{ route('admin.materials.index') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            Back to Materials
        </a>
    </div>

    <x-dashboard.snapshot-kpis class="mt-2" :show-header="false" :cards="$snapshotCards" />

    <section id="units-list"
             class="mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
             x-data="{
                showAddForm: {{ (($errors->has('code') || $errors->has('name')) && ! $errors->has('_unit_id')) ? 'true' : 'false' }},
                editingId: {{ $editingId ? (int) $editingId : 'null' }},
                openAddForm() { this.showAddForm = true; this.editingId = null; },
                closeAddForm() { this.showAddForm = false; },
                startEdit(id) { this.editingId = id; this.showAddForm = false; },
                cancelEdit() { this.editingId = null; }
             }">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">UOM master</h2>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        {{ number_format($units->count()) }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add custom units anytime. Custom units can be deleted when not linked to materials; standard units (piece, litre, kg…) are protected.</p>
            </div>
            <button type="button" x-show="!showAddForm" @click="openAddForm()"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                Add unit
            </button>
        </div>

        <div x-show="showAddForm" x-cloak class="border-b border-gray-100 bg-gray-50/80 px-5 py-4 dark:border-gray-800 dark:bg-gray-800/30 sm:px-6">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">New unit</h3>
                <button type="button" @click="closeAddForm()" class="erp-btn-cancel">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    Cancel
                </button>
            </div>
            <form action="{{ route('admin.units.store') }}" method="POST" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                @csrf
                <div class="lg:col-span-2">
                    <label for="code" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Code <span class="text-error-500">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" required placeholder="e.g. bundle"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 font-mono text-sm lowercase dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div class="lg:col-span-3">
                    <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Name <span class="text-error-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Bundle"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div class="lg:col-span-2">
                    <label for="symbol" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Symbol</label>
                    <input type="text" id="symbol" name="symbol" value="{{ old('symbol') }}" placeholder="optional"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div class="lg:col-span-2 lg:flex lg:justify-end">
                    <button type="submit" class="erp-btn-primary w-full lg:w-auto">Save</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full table-fixed">
                <colgroup>
                    <col style="width: 16%">
                    <col style="width: 28%">
                    <col style="width: 12%">
                    <col style="width: 12%">
                    <col style="width: 32%">
                </colgroup>
                <thead class="border-b border-gray-100 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Symbol</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Materials</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($units as $unit)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40" x-show="editingId !== {{ $unit->id }}">
                            <td class="px-5 py-3.5 font-mono text-sm font-medium text-gray-900 dark:text-white">{{ $unit->code }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $unit->name }}</span>
                                    @if($unit->is_system)
                                        <span class="rounded-full bg-brand-50 px-2 py-0.5 text-theme-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">Standard</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-sm text-gray-600 dark:text-gray-400">{{ $unit->symbol ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center text-sm tabular-nums text-gray-700 dark:text-gray-300">{{ number_format($unit->linked_products_count) }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex justify-end">
                                    <x-admin.action-group>
                                        <button type="button" @click="startEdit({{ $unit->id }})" class="erp-btn-action">Edit</button>
                                        @if($unit->is_system)
                                            <span class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400" title="Standard units are protected">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                Protected
                                            </span>
                                        @elseif($unit->linked_products_count > 0)
                                            <span class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-500 dark:border-gray-700 dark:text-gray-400"
                                                  title="Reassign {{ number_format($unit->linked_products_count) }} material(s) to another UOM before deleting">
                                                Delete (in use)
                                            </span>
                                        @else
                                            <x-admin.action-delete
                                                :action="route('admin.units.destroy', $unit)"
                                                confirm="Delete unit «{{ $unit->name }}»? This cannot be undone."
                                            />
                                        @endif
                                    </x-admin.action-group>
                                </div>
                            </td>
                        </tr>
                        <tr class="bg-brand-50/30 dark:bg-brand-500/5" x-show="editingId === {{ $unit->id }}" x-cloak>
                            <td colspan="5" class="px-5 py-4">
                                <form action="{{ route('admin.units.update', $unit) }}" method="POST" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="_unit_id" value="{{ $unit->id }}">
                                    <div class="lg:col-span-2">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                                        <input type="text" name="code" value="{{ old('code', $unit->code) }}" @readonly($unit->is_system)
                                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 font-mono text-sm lowercase dark:border-gray-700 dark:bg-gray-900 dark:text-white {{ $unit->is_system ? 'cursor-not-allowed bg-gray-100 dark:bg-gray-800/50' : '' }}">
                                    </div>
                                    <div class="lg:col-span-3">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                        <input type="text" name="name" value="{{ old('name', $unit->name) }}" required
                                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    </div>
                                    <div class="lg:col-span-2">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Symbol</label>
                                        <input type="text" name="symbol" value="{{ old('symbol', $unit->symbol) }}"
                                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
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
    </section>
</div>
@endsection
