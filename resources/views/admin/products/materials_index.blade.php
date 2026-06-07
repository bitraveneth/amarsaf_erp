@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                Materials
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Manage raw materials, services, and in‑house steps used in BOMs and production.
            </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <form method="GET" action="{{ route('admin.materials.index') }}" class="flex flex-wrap gap-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search SKU, name, or supplier…"
                        aria-label="Search materials"
                        class="w-full sm:w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                </div>
                <button type="submit" 
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Search
                </button>
                @if(request()->filled('q'))
                    <a href="{{ route('admin.materials.index', request()->only('category')) }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        Clear
                    </a>
                @endif
                <select name="category" onchange="this.form.submit()"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All categories</option>
                    @foreach($materialCategories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) request('category') === (string) $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.material-categories.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Categories
            </a>
            <a href="{{ route('admin.materials.create') }}" 
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Material
            </a>
        </div>
    </div>

    <!-- Status Message -->

    <!-- Materials Table -->
    @if($materials->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                SKU
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Name
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Category
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Type
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                UOM
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Standard Cost
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Supplier
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Status
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($materials as $material)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $material->sku }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $material->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    @if($material->materialCategory)
                                        <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                                            {{ $material->materialCategory->name }}
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            Uncategorized
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $type = $material->product_type ?? 'raw';
                                        $typeLabels = [
                                            'raw' => 'Raw Material',
                                            'service' => 'Service',
                                            'inhouse' => 'In-house',
                                        ];
                                        $typeColors = [
                                            'raw' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                            'service' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                                            'inhouse' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                        ];
                                        $typeLabel = $typeLabels[$type] ?? ucfirst($type);
                                        $typeColor = $typeColors[$type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeColor }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $material->uom ? ucfirst($material->uom) : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $material->standard_cost !== null ? 'BDT ' . number_format($material->standard_cost, 2) : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $material->supplier_name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($material->is_active)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                            <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                                <circle cx="3" cy="3" r="3" />
                                            </svg>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                            <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                                <circle cx="3" cy="3" r="3" />
                                            </svg>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <x-admin.action-group>
                                        <x-admin.action-view :href="route('admin.materials.show', $material)" />
                                        <x-admin.action-edit :href="route('admin.materials.edit', $material)" />
                                        <x-admin.action-delete
                                            :action="route('admin.materials.destroy', $material)"
                                            confirm="Delete this material? This action cannot be undone."
                                        />
                                    </x-admin.action-group>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $materials->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No materials found</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                @if(request()->filled('q'))
                    No materials match your search criteria. Try a different search term.
                @else
                    No materials defined yet. Click "Add Material" to start building your BOM library.
                @endif
            </p>
            <a href="{{ route('admin.materials.create') }}" 
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Material
            </a>
        </div>
    @endif
</div>
@endsection