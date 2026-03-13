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

<div class="flex h-full min-h-[540px] flex-col rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="shadow-default flex flex-1 flex-col rounded-2xl bg-white px-5 pb-11 pt-5 dark:bg-gray-900 sm:px-6 sm:pt-6">
        <div class="flex justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Monthly Sales Target
                </h3>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                    {{ $periodLabel ?: now()->format('F Y') }}
                </p>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}">
                <input type="hidden" name="sales_range" value="{{ request('sales_range', 12) }}">
                <label for="monthly-target-range" class="sr-only">Filter monthly target month</label>
                <select
                    id="monthly-target-range"
                    name="target_month"
                    onchange="this.form.submit()"
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

        <div class="relative mt-6 flex-1 min-h-[320px]">
            <div
                id="chartTwo"
                class="h-full min-h-[320px]"
                data-progress-value="{{ $progressValue }}"
                data-progress-label="{{ $progressValue }}%"
            ></div>
            <span class="absolute left-1/2 top-[85%] -translate-x-1/2 -translate-y-[85%] rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                {{ $progressValue }}% achieved
            </span>
        </div>
        <p class="mx-auto mt-2 w-full max-w-[420px] text-center text-sm text-gray-500 dark:text-gray-400 sm:text-base">
            {{ $targetBasis }}
        </p>
    </div>

    <div class="flex items-center justify-center gap-5 px-6 py-4 sm:gap-8 sm:py-6">
        <div>
            <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">
                Target
            </p>
            <p
                class="flex items-center justify-center gap-1 text-base font-semibold text-gray-800 dark:text-white/90 sm:text-lg">
                {{ $currencyCode }} {{ number_format($targetValue, 2) }}
            </p>
        </div>

        <div class="h-7 w-px bg-gray-200 dark:bg-gray-800"></div>

        <div>
            <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">
                Revenue
            </p>
            <p
                class="flex items-center justify-center gap-1 text-base font-semibold text-gray-800 dark:text-white/90 sm:text-lg">
                {{ $currencyCode }} {{ number_format($achievedValue, 2) }}
            </p>
        </div>

        <div class="h-7 w-px bg-gray-200 dark:bg-gray-800"></div>

        <div>
            <p class="mb-1 text-center text-theme-xs text-gray-500 dark:text-gray-400 sm:text-sm">
                Today
            </p>
            <p
                class="flex items-center justify-center gap-1 text-base font-semibold text-gray-800 dark:text-white/90 sm:text-lg">
                {{ $currencyCode }} {{ number_format($todayValue, 2) }}
            </p>
        </div>
    </div>
</div>
