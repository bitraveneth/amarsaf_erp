@props([
    'deliveryId',
    'status',
    'step' => 1,
    'inProgress' => false,
    'deliveryUrl',
    'orderId',
    'agentName' => '—',
    'routeLabel' => '—',
])

@php
    $statusLabel = match ($status) {
        'in_transit' => 'In transit',
        'exception' => 'Exception',
        default => ucfirst(str_replace('_', ' ', (string) $status)),
    };
@endphp

<div id="dash-delivery-progress-{{ $deliveryId }}"
     {{ $attributes->merge(['class' => 'dash-order-progress-panel']) }}
     role="region"
     aria-label="Delivery flow for run {{ $deliveryId }}">
    <div class="dash-order-progress-panel__head">
        <div class="min-w-0">
            <p class="dash-order-progress-panel__eyebrow">Delivery flow</p>
            <p class="dash-order-progress-panel__title">
                Order #{{ $orderId }}
                <span class="dash-order-progress-panel__sep">·</span>
                {{ $statusLabel }}
            </p>
            <p class="dash-order-progress-panel__meta">
                {{ $agentName }}
                <span class="dash-order-progress-panel__sep">·</span>
                {{ $routeLabel }}
            </p>
        </div>

        <a href="{{ $deliveryUrl }}" class="dash-order-progress-panel__link">
            View delivery
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>

    <x-admin.order-workflow
        type="delivery"
        :step="$step"
        :in-progress="$inProgress"
        variant="dash"
    />
</div>
