@props([
    'orderId',
    'status',
    'step' => 1,
    'inProgress' => false,
])

@php
    $activeStep = min(max((int) $step, 1), 5);
    $allComplete = $activeStep > 4;
    $stepCount = 4;

    $statusTone = match ($status) {
        'delivered' => 'success',
        'packed', 'picked', 'dispatched' => 'brand',
        'exception', 'cancelled', 'canceled' => 'error',
        'confirmed' => 'warning',
        default => 'neutral',
    };

    $statusLabel = match ($status) {
        'picked', 'packed' => 'Fulfilling',
        'dispatched' => 'Delivering',
        default => ucfirst((string) $status),
    };
@endphp

<button type="button"
        {{ $attributes->merge(['class' => 'dash-order-progress-trigger']) }}
        @click.stop="toggleProgress({{ $orderId }})"
        :aria-expanded="openProgressId === {{ $orderId }}"
        aria-controls="dash-order-progress-{{ $orderId }}"
        aria-label="Show sales flow for order {{ $orderId }}">
    <span class="dash-order-status dash-order-status-{{ $statusTone }}">
        {{ $statusLabel }}
    </span>

    <span class="dash-order-progress-track" aria-hidden="true">
        @for($index = 0; $index < $stepCount; $index++)
            @php
                $segment = $index + 1;
                $isComplete = $allComplete || $activeStep > $segment;
                $isActive = ! $allComplete && $activeStep === $segment;
                $isPulse = $isActive && $inProgress;
            @endphp
            <span @class([
                'dash-order-progress-seg',
                'is-complete' => $isComplete,
                'is-active' => $isActive && ! $isPulse,
                'is-pulse' => $isPulse,
            ])></span>
        @endfor
    </span>

    <span class="dash-order-progress-chevron" :class="{ 'is-open': openProgressId === {{ $orderId }} }" aria-hidden="true">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </span>
</button>
