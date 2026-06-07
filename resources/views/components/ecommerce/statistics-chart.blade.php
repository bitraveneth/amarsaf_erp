@php
    $rangeLabel = 'Last 7 days';
    $options = [
        ['value' => 'overview', 'label' => 'Overview'],
        ['value' => 'sales', 'label' => 'Sales'],
        ['value' => 'production', 'label' => 'Production'],
        ['value' => 'revenue', 'label' => 'Revenue'],
    ];
@endphp

<div class="dash-performance-widget min-h-[24rem]">
    <div class="dash-performance-widget-header">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                    </svg>
                </span>
                <div>
                    <h3 class="dash-performance-widget-title">Last 7 days</h3>
                    <p class="dash-performance-widget-desc !mt-0">Daily orders, production, and revenue trend</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="dash-segment !p-1">
                @foreach ($options as $option)
                    <button
                        type="button"
                        data-stats-tab="{{ $option['value'] }}"
                        @class(['dash-segment-btn !px-3.5 !py-2 !text-sm', 'dashboard-stats-active' => $option['value'] === 'overview'])
                    >
                        {{ $option['label'] }}
                    </button>
                @endforeach
            </div>

            <span class="dash-chip !px-3 !py-2 !text-xs">{{ $rangeLabel }}</span>
        </div>
    </div>

    <div class="dash-performance-widget-body pb-5">
        <div id="chartThree" class="h-[280px] w-full"></div>
    </div>
</div>
