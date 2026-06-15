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
                class="export-center-featured-card group"
            >
                @if(! empty($report['badge']))
                    <span class="export-center-featured-card__badge">{{ $report['badge'] }}</span>
                @endif
                <span class="export-center-featured-card__title">{{ $report['title'] }}</span>
                @if(! empty($report['hint']))
                    <span class="export-center-featured-card__hint">{{ $report['hint'] }}</span>
                @endif
                @if(! empty($report['periodNote']))
                    <span class="export-center-featured-card__hint mt-1">{{ $report['periodNote'] }}</span>
                @endif
            </a>
        @empty
            <div class="col-span-full">
                <x-admin.empty-state title="No reports in this group" />
            </div>
        @endforelse
    </div>
</div>
