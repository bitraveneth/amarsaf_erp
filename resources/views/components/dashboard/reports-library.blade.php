@props([
    'groups' => [],
    'featured' => [],
])

<section
    class="reports-library export-center-panel"
    x-data="reportsLibrary(@js([
        'groups' => $groups,
        'featured' => $featured,
    ]))"
>
    <div class="export-center-panel__head">
        <div>
            <h2 class="erp-h2">Browse all reports</h2>
            <p class="erp-caption mt-1">Open a group, pick a report — set the period on the report page.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" class="erp-btn-secondary !px-3 !py-1.5 !text-xs" @click="expandAll()">Expand all</button>
            <button type="button" class="erp-btn-secondary !px-3 !py-1.5 !text-xs" @click="collapseAll()">Collapse all</button>
            <p class="export-center-group__count" x-text="allReports.length + ' reports'"></p>
        </div>
    </div>

    <div class="reports-library__catalog-only">
        <div class="export-center-builder__module">
            <label class="flex flex-col gap-1.5">
                <span class="erp-label">Search reports</span>
                <input
                    type="search"
                    x-model="query"
                    class="erp-input h-11"
                    placeholder="Income, aging, inventory, ledger…"
                >
            </label>

            <div class="export-center-quick-chips">
                <template x-for="item in categories" :key="item.key">
                    <button
                        type="button"
                        class="export-center-quick-chip"
                        x-bind:class="{ 'border-brand-300 bg-brand-50 text-brand-700 dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-300': category === item.key }"
                        @click="setCategory(item.key)"
                        x-text="item.label + ' (' + item.count + ')'"
                    ></button>
                </template>
            </div>
        </div>

        @if(count($featured) > 0)
            <div class="export-center-builder__featured">
                <p class="erp-label">Quick open</p>
                <p class="erp-caption mt-1">Most used reports</p>
                <div class="export-center-featured-grid">
                    @foreach($featured as $report)
                        <a href="{{ $report['href'] }}" class="export-center-featured-card group">
                            <span class="export-center-featured-card__badge">{{ $report['badge'] }}</span>
                            <span class="export-center-featured-card__title">{{ $report['title'] }}</span>
                            <span class="export-center-featured-card__hint">{{ $report['hint'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="reports-library__accordion">
            <template x-for="group in visibleGroups" :key="group.key">
                <div
                    class="reports-library__group"
                    x-bind:class="{ 'is-open': isGroupOpen(group.key) }"
                    x-bind:id="'reports-group-' + group.key"
                >
                    <button
                        type="button"
                        class="reports-library__group-trigger"
                        x-bind:aria-expanded="isGroupOpen(group.key)"
                        @click="toggleGroup(group.key)"
                    >
                        <span class="reports-library__chevron" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                        <span class="reports-library__group-text">
                            <span class="reports-library__group-title" x-text="group.label"></span>
                            <span class="reports-library__group-desc" x-text="group.description"></span>
                        </span>
                        <span class="export-center-group__count" x-text="group.reports.length"></span>
                    </button>

                    <div
                        class="reports-library__group-body"
                        x-show="isGroupOpen(group.key)"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-cloak
                    >
                        <div class="reports-library__report-list">
                            <template x-for="report in group.reports" :key="report.id">
                                <a
                                    x-bind:href="report.href"
                                    class="reports-library__report-row group"
                                >
                                    <span class="reports-library__report-icon" aria-hidden="true">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2v-4m3 4v-6M4 5h16v14H4V5z" />
                                        </svg>
                                    </span>
                                    <span class="reports-library__report-copy">
                                        <span class="reports-library__report-top">
                                            <span class="reports-library__report-title" x-text="report.title"></span>
                                            <span class="export-center-card__badge !mt-0" x-text="report.badge"></span>
                                        </span>
                                        <span class="reports-library__report-hint" x-text="report.hint"></span>
                                    </span>
                                    <span class="reports-library__report-arrow" aria-hidden="true">→</span>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="visibleGroups.length === 0" x-cloak class="export-center-preview__empty">
                No reports match your search. Try another keyword or category.
            </div>
        </div>
    </div>
</section>
