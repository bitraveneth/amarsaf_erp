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

@php
    $progressValue = $monthlySalesTarget > 0
        ? max(0, min(100, round((float) $progressPercent, 1)))
        : 0;
    $targetValue = max((float) $monthlySalesTarget, 0);
    $achievedValue = max((float) $monthlyAchieved, 0);
    $arcRadius = 76;
    $arcStartX = 100 - $arcRadius;
    $arcEndX = 100 + $arcRadius;
    $arcY = 104;
    $arcLength = round(M_PI * $arcRadius, 2);
    $dashOffset = $arcLength * (1 - ($progressValue / 100));
    $remainingValue = max(0, $targetValue - $achievedValue);
    $percentLabel = number_format($progressValue, $progressValue == floor($progressValue) ? 0 : 1);
@endphp

<div
    id="dashboard-monthly-target-card"
    {{ $attributes->merge(['class' => 'dash-performance-widget']) }}
    data-target-value="{{ $targetValue }}"
    data-achieved-value="{{ $achievedValue }}"
    data-today-value="{{ (float) $todayAchieved }}"
    data-progress-value="{{ $progressValue }}"
>
    <div class="dash-performance-widget-header">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7.5 15.75 10.5 12l2.25 2.25L16.5 9.75" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <h3 class="dash-performance-widget-title">Sales target</h3>
                    <p class="dash-performance-widget-desc !mt-0 line-clamp-1" data-target-basis>{{ $targetBasis }}</p>
                </div>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}" data-dashboard-target-form class="shrink-0">
            <input type="hidden" name="sales_range" value="{{ request('sales_range', 12) }}">
            <label for="monthly-target-range" class="sr-only">Filter monthly target month</label>
            <select
                id="monthly-target-range"
                name="target_month"
                data-dashboard-target-month
                class="dash-select !px-3 !py-2 !text-sm"
            >
                @foreach($targetMonthOptions as $option)
                    <option value="{{ $option['value'] }}" @selected(request('target_month', now()->format('Y-m')) === $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($targetValue > 0)
        <div class="dash-performance-widget-body flex flex-1 flex-col justify-between gap-4 pb-5">
            <div class="flex flex-1 flex-col items-center justify-center py-2">
                <div class="dash-target-gauge mx-auto w-full max-w-[14rem]">
                    <svg
                        viewBox="0 0 200 120"
                        class="dash-target-gauge-svg block w-full"
                        aria-hidden="true"
                        role="img"
                        preserveAspectRatio="xMidYMid meet"
                    >
                        <path
                            d="M {{ $arcStartX }} {{ $arcY }} A {{ $arcRadius }} {{ $arcRadius }} 0 0 1 {{ $arcEndX }} {{ $arcY }}"
                            fill="none"
                            stroke="#E4E7EC"
                            stroke-width="12"
                            stroke-linecap="round"
                            class="dark:stroke-gray-800"
                        />
                        <path
                            data-target-arc
                            d="M {{ $arcStartX }} {{ $arcY }} A {{ $arcRadius }} {{ $arcRadius }} 0 0 1 {{ $arcEndX }} {{ $arcY }}"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="12"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $arcLength }}"
                            stroke-dashoffset="{{ $dashOffset }}"
                            class="text-brand-500 transition-all duration-500 ease-out"
                        />
                    </svg>

                    <div class="dash-target-gauge-label pointer-events-none text-center">
                        <p
                            data-target-percent
                            class="text-[1.375rem] font-bold leading-none tracking-tight text-gray-900 dark:text-white"
                        >
                            {{ $percentLabel }}%
                        </p>
                        <p class="mt-1 text-[9px] font-semibold uppercase tracking-[0.12em] text-gray-400 dark:text-gray-500">
                            of target
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="grid grid-cols-3 gap-2">
                    <div class="rounded-xl border border-gray-100 bg-white px-3 py-2.5 text-center shadow-sm dark:border-gray-800 dark:bg-gray-950">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Target</p>
                        <p data-target-total class="mt-1 text-sm font-bold tabular-nums text-gray-900 dark:text-white">
                            {{ number_format($targetValue, 0) }}
                        </p>
                        <p class="mt-0.5 text-[10px] text-gray-400">{{ $currencyCode }}</p>
                    </div>

                    <div class="rounded-xl border border-brand-100 bg-white px-3 py-2.5 text-center shadow-sm ring-1 ring-brand-100 dark:border-brand-500/20 dark:bg-gray-950 dark:ring-brand-500/20">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-400">Achieved</p>
                        <p data-target-achieved class="mt-1 text-sm font-bold tabular-nums text-gray-900 dark:text-white">
                            {{ number_format($achievedValue, 0) }}
                        </p>
                        <p class="mt-0.5 text-[10px] text-brand-500/80">{{ $currencyCode }}</p>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white px-3 py-2.5 text-center shadow-sm dark:border-gray-800 dark:bg-gray-950">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Today</p>
                        <p data-target-today class="mt-1 text-sm font-bold tabular-nums text-gray-900 dark:text-white">
                            {{ number_format((float) $todayAchieved, 0) }}
                        </p>
                        <p class="mt-0.5 text-[10px] text-gray-400">{{ $currencyCode }}</p>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-100 bg-white px-3 py-2.5 text-center shadow-sm dark:border-gray-800 dark:bg-gray-950">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        <span class="font-bold tabular-nums text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($remainingValue, 0) }}</span>
                        <span class="text-gray-400"> remaining to hit target</span>
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="dash-performance-widget-body flex flex-1 items-center justify-center pb-5">
            <div class="flex w-full flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-gray-50/70 px-6 py-10 text-center dark:border-gray-700 dark:bg-white/[0.02]">
                <p class="text-base font-semibold text-gray-800 dark:text-white">No target set</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    No target configured for {{ $periodLabel }}
                </p>
            </div>
        </div>
    @endif
</div>
