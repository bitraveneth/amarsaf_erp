@props([
    'title',
    'subtitle' => null,
    'reports' => [],
    'hubAction' => null,
    'range' => 'month',
    'from' => null,
    'to' => null,
    'rangeOptions' => [],
    'periodLabel' => null,
])

<div class="erp-dash-page">
    <x-dashboard.hero :title="$title" :subtitle="$subtitle" :period="$periodLabel">
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2 print:hidden">
                @if($hubAction)
                    <a href="{{ $hubAction }}" class="erp-btn-secondary">All reports</a>
                @endif
                <x-dashboard.period-filter
                    variant="compact"
                    :action="url()->current()"
                    :range="$range"
                    :from="$from"
                    :to="$to"
                    :range-options="$rangeOptions"
                    :period-label="$periodLabel"
                />
            </div>
        </x-slot:actions>
    </x-dashboard.hero>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($reports as $report)
            <a
                href="{{ $report['href'] ?? '#' }}"
                class="group flex min-h-[8.5rem] flex-col justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-brand-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-500/40"
            >
                <div class="min-w-0">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-300">
                            {{ $report['title'] }}
                        </h3>
                        @if(! empty($report['badge']))
                            <span class="shrink-0 rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                {{ $report['badge'] }}
                            </span>
                        @endif
                    </div>
                    @if(! empty($report['hint']))
                        <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">{{ $report['hint'] }}</p>
                    @endif
                </div>
                <div class="mt-4 flex items-center justify-between gap-3">
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        {{ $report['periodNote'] ?? $periodLabel }}
                    </p>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 dark:text-brand-300">
                        Open
                        <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </span>
                </div>
            </a>
        @empty
            <div class="col-span-full">
                <x-admin.empty-state title="No reports in this group" />
            </div>
        @endforelse
    </div>
</div>
