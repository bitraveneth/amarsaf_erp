@props([
    'warehouses' => [],
])

@php
    $allWarehouses = collect($warehouses);
    $siteTabs = [
        'all' => ['label' => 'All sites', 'items' => $allWarehouses],
        'factory' => ['label' => 'Factories', 'items' => $allWarehouses->where('type', 'factory')->values()],
        'depot' => ['label' => 'Depots', 'items' => $allWarehouses->where('type', 'depot')->values()],
        'other' => ['label' => 'Other', 'items' => $allWarehouses->whereNotIn('type', ['factory', 'depot'])->values()],
    ];
    $typeFilters = [
        'all' => 'All stock',
        'finished' => 'Finished only',
        'raw' => 'Raw only',
    ];
@endphp

<section {{ $attributes->merge(['class' => 'wh-stock-section']) }}
         x-data="{ activeTab: 'all', stockFilter: 'all' }">
    <x-dashboard.section-header
        title="Stock by warehouse"
        description="Available units on hand per site — finished goods vs raw materials."
        class="mb-5"
    >
        <x-slot:actions>
            <a href="{{ route('admin.inventory.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
                Inventory dashboard
            </a>
            <a href="{{ route('admin.warehouses.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
                All warehouses
            </a>
        </x-slot:actions>
    </x-dashboard.section-header>

    @if($allWarehouses->isEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white/50 p-8 text-center dark:border-gray-800 dark:bg-gray-900/50">
            <p class="text-sm font-medium text-gray-900 dark:text-white">No warehouses registered yet</p>
            <a href="{{ route('admin.warehouses.create') }}" class="erp-btn-primary mt-4">Add warehouse</a>
        </div>
    @else
        <div class="wh-stock-tabs" role="tablist" aria-label="Warehouse types">
            @foreach($siteTabs as $key => $tab)
                <button type="button"
                        role="tab"
                        class="wh-stock-tab"
                        :class="{ 'is-active': activeTab === '{{ $key }}' }"
                        :aria-selected="activeTab === '{{ $key }}'"
                        @click="activeTab = '{{ $key }}'">
                    {{ $tab['label'] }}
                    <span class="wh-stock-tab__count">{{ $tab['items']->count() }}</span>
                </button>
            @endforeach
        </div>

        <div class="wh-stock-type-filters" role="group" aria-label="Stock type filter">
            @foreach($typeFilters as $filterKey => $filterLabel)
                <button type="button"
                        class="wh-stock-type-filter"
                        :class="{ 'is-active': stockFilter === '{{ $filterKey }}' }"
                        @click="stockFilter = '{{ $filterKey }}'">
                    {{ $filterLabel }}
                </button>
            @endforeach
        </div>

        @foreach($siteTabs as $key => $tab)
            @php
                $hasFinished = $tab['items']->contains(fn (array $w) => $w['finished_qty'] > 0);
                $hasRaw = $tab['items']->contains(fn (array $w) => $w['raw_qty'] > 0);
            @endphp
            <div x-show="activeTab === '{{ $key }}'"
                 x-cloak
                 style="display: none;"
                 role="tabpanel"
                 class="dash-snapshot-grid wh-stock-grid items-stretch">
                @forelse($tab['items'] as $warehouse)
                    <div class="flex h-full min-h-0"
                         x-show="stockFilter === 'all'
                            || (stockFilter === 'finished' && {{ $warehouse['finished_qty'] > 0 ? 'true' : 'false' }})
                            || (stockFilter === 'raw' && {{ $warehouse['raw_qty'] > 0 ? 'true' : 'false' }})"
                         x-cloak>
                        <x-module.warehouse-stock-card
                            class="w-full"
                            :name="$warehouse['name']"
                            :tone="$warehouse['tone']"
                            :icon="$warehouse['icon']"
                            :href="$warehouse['href']"
                            :materials-href="$warehouse['materials_href']"
                            :total-qty="$warehouse['total_qty']"
                            :finished-qty="$warehouse['finished_qty']"
                            :raw-qty="$warehouse['raw_qty']"
                            :other-qty="$warehouse['other_qty']"
                            :finished-pct="$warehouse['finished_pct']"
                            :raw-pct="$warehouse['raw_pct']"
                            :other-pct="$warehouse['other_pct']"
                        />
                    </div>
                @empty
                    <p class="col-span-full rounded-2xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        No sites in this group.
                    </p>
                @endforelse

                @if($tab['items']->isNotEmpty())
                    <p x-show="stockFilter === 'finished' && {{ $hasFinished ? 'false' : 'true' }}"
                       x-cloak
                       class="col-span-full rounded-2xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        No finished goods in this group.
                    </p>
                    <p x-show="stockFilter === 'raw' && {{ $hasRaw ? 'false' : 'true' }}"
                       x-cloak
                       class="col-span-full rounded-2xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        No raw materials in this group.
                    </p>
                @endif
            </div>
        @endforeach
    @endif
</section>
