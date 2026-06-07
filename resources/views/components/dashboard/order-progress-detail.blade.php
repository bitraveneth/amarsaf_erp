@props([
    'orderId',
    'status',
    'step' => 1,
    'inProgress' => false,
    'orderUrl',
    'agentName' => '—',
    'deliveryDate' => '—',
])

@php
    $statusLabel = match ($status) {
        'picked', 'packed' => 'Fulfilling',
        'dispatched' => 'Delivering',
        default => ucfirst((string) $status),
    };
@endphp

<div id="dash-order-progress-{{ $orderId }}"
     {{ $attributes->merge(['class' => 'dash-order-progress-panel']) }}
     role="region"
     aria-label="Sales flow for order {{ $orderId }}">
    <div class="dash-order-progress-panel__head">
        <div class="min-w-0">
            <p class="dash-order-progress-panel__eyebrow">Sales flow</p>
            <p class="dash-order-progress-panel__title">
                Order #{{ $orderId }}
                <span class="dash-order-progress-panel__sep">·</span>
                {{ $statusLabel }}
            </p>
            <p class="dash-order-progress-panel__meta">
                {{ $agentName }}
                <span class="dash-order-progress-panel__sep">·</span>
                Delivery {{ $deliveryDate }}
            </p>
        </div>

        <a href="{{ $orderUrl }}" class="dash-order-progress-panel__link">
            View order
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>

    <x-admin.order-workflow
        type="sales"
        :step="$step"
        :in-progress="$inProgress"
        variant="dash"
    />
</div>
