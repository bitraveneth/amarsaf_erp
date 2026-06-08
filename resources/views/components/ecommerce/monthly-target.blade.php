@props([
    'currencyCode' => 'BDT',
    'periodLabel' => '',
    'targetBasis' => 'Target set for each month',
    'monthlySalesTarget' => 0,
    'monthlyAchieved' => 0,
    'todayAchieved' => 0,
    'progressPercent' => 0,
    'progressPercentRaw' => null,
    'todayChangePercent' => null,
    'targetMonthOptions' => [],
])

@php
    $progressRaw = (float) ($progressPercentRaw ?? $progressPercent);
    $arcProgress = $monthlySalesTarget > 0
        ? max(0, min(100, round((float) $progressPercent, 2)))
        : 0;
    $targetValue = max((float) $monthlySalesTarget, 0);
    $achievedValue = max((float) $monthlyAchieved, 0);
    $todayValue = max((float) $todayAchieved, 0);
    $remainingValue = max(0, $targetValue - $achievedValue);
    $percentLabel = number_format(
        $progressRaw,
        fmod($progressRaw, 1.0) === 0.0 ? 0 : 2
    );
    $arcRadius = 86;
    $arcStartX = 100 - $arcRadius;
    $arcEndX = 100 + $arcRadius;
    $arcY = 96;
    $arcLength = round(M_PI * $arcRadius, 2);
    $dashOffset = $arcLength * (1 - ($arcProgress / 100));
    $delta = $todayChangePercent;
    $deltaLabel = $delta === null
        ? null
        : (($delta >= 0 ? '+' : '') . number_format($delta, fmod(abs($delta), 1.0) === 0.0 ? 0 : 1) . '%');
    $todayLine = $todayValue > 0
        ? "You invoiced {$currencyCode} " . number_format($todayValue, 0) . ' today'
        : 'No invoiced sales recorded yet today';
    if ($delta !== null && $todayValue > 0) {
        $todayLine .= $delta >= 0
            ? ", up {$deltaLabel} from yesterday."
            : ", down " . ltrim($deltaLabel, '+') . ' from yesterday.';
    } else {
        $todayLine .= '.';
    }
@endphp

<div
    id="dashboard-monthly-target-card"
    {{ $attributes->merge(['class' => 'dash-performance-widget dash-target-widget']) }}
    data-target-value="{{ $targetValue }}"
    data-achieved-value="{{ $achievedValue }}"
    data-today-value="{{ $todayValue }}"
    data-progress-value="{{ $arcProgress }}"
    data-progress-raw="{{ $progressRaw }}"
>
    <div class="dash-performance-widget-header dash-target-widget__header">
        <div class="min-w-0">
            <h3 class="dash-performance-widget-title">Monthly target</h3>
            <p class="dash-performance-widget-desc !mt-0.5 line-clamp-2" data-target-basis>{{ $targetBasis }}</p>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}" data-dashboard-target-form class="shrink-0">
            <input type="hidden" name="sales_range" value="{{ request('sales_range', 12) }}">
            <label for="monthly-target-range" class="sr-only">Filter monthly target month</label>
            <select
                id="monthly-target-range"
                name="target_month"
                data-dashboard-target-month
                class="dash-target-widget__select"
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
        <div class="dash-performance-widget-body dash-target-widget__body">
            <div class="dash-target-ring">
                <svg
                    viewBox="0 0 200 118"
                    class="dash-target-ring__svg"
                    aria-hidden="true"
                    role="img"
                    preserveAspectRatio="xMidYMid meet"
                >
                    <defs>
                        <linearGradient id="dash-target-ring-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#465FFF" />
                            <stop offset="55%" stop-color="#6284FF" />
                            <stop offset="100%" stop-color="#9CB9FF" />
                        </linearGradient>
                    </defs>
                    <path
                        d="M {{ $arcStartX }} {{ $arcY }} A {{ $arcRadius }} {{ $arcRadius }} 0 0 1 {{ $arcEndX }} {{ $arcY }}"
                        fill="none"
                        class="dash-target-ring__track"
                        stroke-width="12"
                        stroke-linecap="round"
                    />
                    <path
                        data-target-arc
                        d="M {{ $arcStartX }} {{ $arcY }} A {{ $arcRadius }} {{ $arcRadius }} 0 0 1 {{ $arcEndX }} {{ $arcY }}"
                        fill="none"
                        stroke="url(#dash-target-ring-gradient)"
                        stroke-width="12"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $arcLength }}"
                        stroke-dashoffset="{{ $dashOffset }}"
                        class="dash-target-ring__progress"
                    />
                </svg>

                <div class="dash-target-ring__center">
                    <p data-target-percent class="dash-target-ring__percent">{{ $percentLabel }}%</p>
                    @if($deltaLabel !== null)
                        <span
                            data-target-delta
                            class="dash-target-ring__delta {{ $delta >= 0 ? 'dash-target-ring__delta--up' : 'dash-target-ring__delta--down' }}"
                        >
                            {{ $deltaLabel }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="dash-target-widget__summary">
                <p class="dash-target-widget__today" data-target-today-line>{{ $todayLine }}</p>
            </div>

            <div class="dash-target-widget__stats">
                <div class="dash-target-widget__stat">
                    <span class="dash-target-widget__stat-label">Target</span>
                    <span data-target-total class="dash-target-widget__stat-value">{{ $currencyCode }} {{ number_format($targetValue, 0) }}</span>
                </div>
                <div class="dash-target-widget__stat dash-target-widget__stat--accent">
                    <span class="dash-target-widget__stat-label">Achieved</span>
                    <span data-target-achieved class="dash-target-widget__stat-value">{{ $currencyCode }} {{ number_format($achievedValue, 0) }}</span>
                </div>
                <div class="dash-target-widget__stat">
                    <span class="dash-target-widget__stat-label">Remaining</span>
                    <span data-target-remaining class="dash-target-widget__stat-value">{{ $currencyCode }} {{ number_format($remainingValue, 0) }}</span>
                </div>
            </div>
        </div>
    @else
        <div class="dash-performance-widget-body flex flex-1 items-center justify-center pb-5">
            <div class="dash-target-widget__empty">
                <div class="dash-target-ring dash-target-ring--empty">
                    <svg viewBox="0 0 200 118" class="dash-target-ring__svg" aria-hidden="true">
                        <path
                            d="M {{ $arcStartX }} {{ $arcY }} A {{ $arcRadius }} {{ $arcRadius }} 0 0 1 {{ $arcEndX }} {{ $arcY }}"
                            fill="none"
                            class="dash-target-ring__track"
                            stroke-width="12"
                            stroke-linecap="round"
                        />
                    </svg>
                    <div class="dash-target-ring__center">
                        <p class="dash-target-ring__percent text-gray-300 dark:text-gray-600">—</p>
                    </div>
                </div>
                <p class="mt-4 text-base font-semibold text-gray-800 dark:text-white">No target set</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    No target configured for {{ $periodLabel }}
                </p>
                <a href="{{ route('admin.sales-targets.index') }}" class="mt-4 inline-flex text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    Set sales targets
                </a>
            </div>
        </div>
    @endif
</div>
