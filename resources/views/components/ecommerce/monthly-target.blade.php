@props([
    'currencyCode' => 'BDT',
    'periodLabel' => '',
    'targetBasis' => 'No monthly sales target configured',
    'monthlySalesTarget' => 0,
    'monthlyAchieved' => 0,
    'todayAchieved' => 0,
    'progressPercent' => 0,
    'targetMonthOptions' => [],
])

<div id="dashboard-monthly-target-card" class="shadow-default flex flex-col rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-gray-900 sm:px-5 sm:pb-4 sm:pt-5">
        <div class="flex justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Monthly Sales Target
                </h3>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" data-dashboard-target-form>
                <input type="hidden" name="sales_range" value="{{ request('sales_range', 12) }}">
                <label for="monthly-target-range" class="sr-only">Filter monthly target month</label>
                <select
                    id="monthly-target-range"
                    name="target_month"
                    data-dashboard-target-month
                    class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-theme-xs focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                >
                    @foreach($targetMonthOptions as $option)
                        <option value="{{ $option['value'] }}" @selected(request('target_month', now()->format('Y-m')) === $option['value'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @php
            $targetValue = max((float) $monthlySalesTarget, 0);
            $achievedValue = max((float) $monthlyAchieved, 0);
            $todayValue = max((float) $todayAchieved, 0);
            $progressValue = max(0, min(100, round((float) $progressPercent, 2)));
        @endphp

        <div class="mt-5 flex flex-col items-center gap-3 md:min-h-[220px] md:flex-row md:items-center md:justify-between md:gap-4">
            <div class="relative w-full max-w-[390px] overflow-hidden md:flex-1">
                <div data-target-visual class="min-h-[220px] overflow-hidden pt-4">
                    <div
                        id="chartTwo"
                        class="-ml-2 min-h-[220px]"
                        data-progress-value="{{ $progressValue }}"
                        data-progress-label="{{ $progressValue }}%"
                    ></div>
                </div>
            </div>
            <div class="flex w-full max-w-[220px] flex-col items-center justify-center gap-2 md:min-h-[220px] md:items-start md:justify-center">
                <div class="text-center md:text-left">
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-gray-400 dark:text-gray-500">
                        Target
                    </p>
                    <p data-target-amount class="mt-1 text-lg font-semibold text-gray-800 dark:text-white/90">
                        {{ $currencyCode }} {{ number_format($targetValue, 2) }}
                    </p>
                </div>
                <p data-target-basis class="text-center text-sm text-gray-500 dark:text-gray-400 sm:text-base md:text-left">
                    {{ $targetBasis }}
                </p>
            </div>
        </div>
</div>
