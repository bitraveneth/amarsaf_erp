@extends('layouts.app')

@section('content')
@php
    use App\Support\ProductUnits;

    $snapshotCards = [
        [
            'label' => 'All materials',
            'numeric' => number_format($totalMaterials),
            'caption' => 'Total SKUs in catalog',
            'href' => route('admin.materials.index'),
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'Raw stock',
            'numeric' => number_format($rawCount ?? 0),
            'caption' => 'Stocked inputs',
            'href' => route('admin.materials.index', ['type' => 'raw']),
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
        [
            'label' => 'In-house',
            'numeric' => number_format($inhouseCount ?? 0),
            'caption' => 'Production steps',
            'href' => route('admin.materials.index', ['type' => 'inhouse']),
            'tone' => 'orange',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'RO & chemicals',
            'numeric' => number_format($roChemicalCount ?? 0),
            'caption' => 'Plant & minerals',
            'href' => route('admin.materials.index', ['q' => 'RO']),
            'tone' => 'purple',
            'valueTone' => 'neutral',
            'icon' => 'alert',
        ],
    ];

    $sourcingLabels = [
        'purchased' => 'Purchased',
        'inhouse' => 'In-house',
        'both' => 'Buy + make',
    ];
    $sourcingColors = [
        'purchased' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        'inhouse' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
        'both' => 'bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-400',
    ];

    $typeLabels = ['raw' => 'Raw', 'service' => 'Service', 'inhouse' => 'In-house'];
    $typeColors = [
        'raw' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
        'service' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
        'inhouse' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
    ];

    $groupsWithItems = collect($materialSections ?? [])->filter(fn ($section) => $section['items']->count() > 0)->count();
    $hasActiveFilters = $hasActiveFilters ?? false;
    $filledSections = collect($materialSections ?? [])->filter(fn ($section) => $section['items']->count() > 0)->count();
@endphp

<div class="dash-page space-y-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="erp-dash-h1">Materials</h1>
            <p class="mt-1 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                Master catalog for BOMs and procurement — grouped by category. Expand a section to browse.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.material-categories.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Categories
            </a>
            <a href="{{ route('admin.units.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Units
            </a>
            <a href="{{ route('admin.materials.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600">
                Add Material
            </a>
        </div>
    </div>

    <x-dashboard.snapshot-kpis size="lg" :show-header="false" :cards="$snapshotCards" />

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
         x-data="materialCategoryAccordion(
             @js($autoExpandSections ?? []),
             @js($sectionKeys ?? []),
             @js($hasActiveFilters)
         )">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Browse by group</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    @if($hasActiveFilters)
                        {{ number_format($filteredMaterialsCount) }} of {{ number_format($totalMaterials) }} materials
                        @if($groupsWithItems > 0)
                            · {{ $groupsWithItems }} {{ Str::plural('group', $groupsWithItems) }} with matches
                        @endif
                    @else
                        {{ number_format($totalMaterials) }} materials · {{ $filledSections }} groups with items
                    @endif
                </p>
            </div>

            <form method="GET" action="{{ route('admin.materials.index') }}" class="mt-4 flex flex-wrap items-center gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search SKU, name, chemical…"
                       class="w-full min-w-[12rem] flex-1 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:max-w-[14rem]">
                <select name="category" onchange="this.form.submit()"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All categories</option>
                    @foreach($groupedCategories as $groupName => $items)
                        <optgroup label="{{ $groupName }}">
                            @foreach($items as $cat)
                                <option value="{{ $cat->id }}" @selected((string) request('category') === (string) $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <select name="type" onchange="this.form.submit()"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All types</option>
                    @foreach($typeLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-gray-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600">Apply</button>
                @if($hasActiveFilters)
                    <a href="{{ route('admin.materials.index') }}" class="px-1 text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Clear</a>
                @endif
            </form>
        </div>

        @if(! empty($materialSections))
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($materialSections as $section)
                    @php
                        $itemCount = $section['items']->count();
                        $isEmpty = $itemCount === 0;
                        $isInhouseSection = ($section['section_kind'] ?? '') === 'inhouse';
                        $countBadgeClass = $isEmpty
                            ? 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
                            : ($isInhouseSection
                                ? 'bg-orange-100 text-orange-800 dark:bg-orange-500/20 dark:text-orange-300'
                                : 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400');
                    @endphp
                    <div class="{{ $isInhouseSection ? 'bg-orange-50/20 dark:bg-orange-500/[0.02]' : 'bg-white dark:bg-gray-900' }}">
                        <button type="button"
                                @click="toggle(@js($section['key']))"
                                class="flex w-full items-center gap-3 border-l-4 px-5 py-3.5 text-left transition sm:px-6
                                       {{ $isEmpty && ! $hasActiveFilters
                                            ? 'border-l-transparent opacity-60 hover:opacity-80'
                                            : ($isInhouseSection
                                                ? 'border-l-orange-500 bg-orange-50/40 hover:bg-orange-50/70 dark:border-l-orange-400 dark:bg-orange-500/5 dark:hover:bg-orange-500/10'
                                                : 'border-l-brand-500/80 bg-gray-50/70 hover:bg-gray-100/80 dark:border-l-brand-400/60 dark:bg-gray-800/30 dark:hover:bg-gray-800/50') }}"
                                :aria-expanded="isOpen(@js($section['key']))">
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200"
                                 :class="isOpen(@js($section['key'])) ? 'rotate-90' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="min-w-0 flex-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $section['label'] }}</span>
                            @if($isInhouseSection)
                                <span class="hidden rounded-md bg-orange-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-orange-800 sm:inline dark:bg-orange-500/20 dark:text-orange-300">Steps</span>
                            @endif
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold tabular-nums {{ $countBadgeClass }}">
                                {{ number_format($itemCount) }}
                            </span>
                        </button>

                        <div x-show="isOpen(@js($section['key']))"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-cloak
                             class="border-t border-gray-100 dark:border-gray-800">
                            @if($isEmpty)
                                <div class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                    @if($hasActiveFilters)
                                        No matches in this group.
                                    @else
                                        Empty —
                                        <a href="{{ route('admin.materials.create') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">add material</a>
                                    @endif
                                </div>
                            @else
                                <div class="overflow-x-auto custom-scrollbar">
                                    <table class="w-full min-w-[880px]">
                                        <thead>
                                            <tr class="border-b border-gray-100 bg-white text-left dark:border-gray-800 dark:bg-gray-900/80">
                                                <th class="px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 sm:px-6">SKU</th>
                                                <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                                <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                                                <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                                                <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Source</th>
                                                <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">UOM</th>
                                                <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cost</th>
                                                <th class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 sm:px-6"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            @foreach($section['items'] as $material)
                                                @php
                                                    $type = $material->product_type ?? 'raw';
                                                    $sourcing = $material->sourcing ?? 'purchased';
                                                @endphp
                                                <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                                    <td class="px-5 py-2.5 font-mono text-sm text-gray-900 dark:text-white sm:px-6">{{ $material->sku }}</td>
                                                    <td class="px-4 py-2.5">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $material->name }}</div>
                                                        @if($material->chemical_name)
                                                            <div class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400" title="{{ $material->chemical_name }}">{{ $material->chemical_name }}</div>
                                                        @elseif($material->supplier_name)
                                                            <div class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $material->supplier_name }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-300">{{ $material->materialCategory->name ?? '—' }}</td>
                                                    <td class="px-4 py-2.5">
                                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $typeColors[$type] ?? 'bg-gray-100 text-gray-700' }}">
                                                            {{ $typeLabels[$type] ?? ucfirst($type) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2.5">
                                                        @if($type === 'raw' && $sourcing !== 'purchased')
                                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $sourcingColors[$sourcing] ?? '' }}">
                                                                {{ $sourcingLabels[$sourcing] ?? $sourcing }}
                                                            </span>
                                                        @else
                                                            <span class="text-xs text-gray-400">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300">
                                                        {{ $material->uom ? ProductUnits::label($material->uom) : '—' }}
                                                    </td>
                                                    <td class="px-4 py-2.5 text-sm tabular-nums text-gray-900 dark:text-white">
                                                        {{ $material->standard_cost !== null ? number_format($material->standard_cost, 2) : '—' }}
                                                    </td>
                                                    <td class="px-5 py-2.5 text-right sm:px-6">
                                                        <x-admin.action-group>
                                                            <x-admin.action-view :href="route('admin.materials.show', $material)" />
                                                            <x-admin.action-edit :href="route('admin.materials.edit', $material)" />
                                                            <x-admin.action-delete :action="route('admin.materials.destroy', $material)" confirm="Delete this material?" />
                                                        </x-admin.action-group>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-16 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">No materials in the catalog yet.</p>
                <a href="{{ route('admin.materials.create') }}" class="mt-4 inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Add Material</a>
            </div>
        @endif
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('materialCategoryAccordion', (initialOpen = [], allKeys = [], hasActiveFilters = false) => {
                    const storageKey = 'saf-erp-materials-accordion';

                    const loadStored = () => {
                        try {
                            const raw = localStorage.getItem(storageKey);
                            if (! raw) return {};
                            const parsed = JSON.parse(raw);
                            if (! parsed || typeof parsed !== 'object') return {};
                            const open = {};
                            allKeys.forEach((key) => { if (parsed[key]) open[key] = true; });
                            return open;
                        } catch {
                            return {};
                        }
                    };

                    const open = {};
                    if (hasActiveFilters) {
                        initialOpen.forEach((key) => { open[key] = true; });
                    } else if (initialOpen.length > 0) {
                        initialOpen.forEach((key) => { open[key] = true; });
                    } else {
                        Object.assign(open, loadStored());
                    }

                    return {
                        open,
                        allKeys,
                        hasActiveFilters,
                        isOpen(key) { return !!this.open[key]; },
                        persist() {
                            if (this.hasActiveFilters) return;
                            try { localStorage.setItem(storageKey, JSON.stringify(this.open)); } catch {}
                        },
                        toggle(key) { this.open[key] = !this.open[key]; this.persist(); },
                    };
                });
            });
        </script>
    @endpush
@endonce
@endsection
