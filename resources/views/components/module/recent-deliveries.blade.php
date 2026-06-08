@props([
    'deliveries' => [],
    'currencyCode' => 'BDT',
    'showHeader' => true,
])

@php
    $deliveryCount = count($deliveries);
@endphp

<div {{ $attributes->merge(['class' => 'dash-activity-widget dash-recent-orders-widget']) }}
     x-data="{
        openProgressId: null,
        toggleProgress(id) {
            this.openProgressId = this.openProgressId === id ? null : id;
        },
     }"
     @keydown.escape.window="openProgressId = null">
    @if($showHeader)
        <div class="dash-performance-widget-header">
            <div class="min-w-0">
                <div class="flex items-center gap-2.5">
                    <span class="dash-activity-icon dash-activity-icon-brand">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 0 0-2.654-9.874A3.75 3.75 0 0 0 17.25 6H9.75a3.75 3.75 0 0 0-3.548 2.524A49.902 49.902 0 0 0 3.506 18.376c-.039.62.469 1.124 1.09 1.124H8.25Z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="dash-performance-widget-title">Recent deliveries</h3>
                        <p class="dash-performance-widget-desc !mt-0">Latest dispatch and POD activity</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($deliveryCount > 0)
                    <span class="dash-activity-summary-badge dash-activity-summary-badge-neutral">
                        {{ number_format($deliveryCount) }} shown
                    </span>
                @endif
                <a href="{{ route('admin.deliveries.pod-index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
                    See all
                </a>
            </div>
        </div>
    @elseif($deliveryCount > 0)
        <div class="dash-recent-orders-widget__meta border-b border-gray-100/80 px-5 py-3 text-right dark:border-white/10">
            <span class="dash-activity-summary-badge dash-activity-summary-badge-neutral">
                {{ number_format($deliveryCount) }} shown
            </span>
        </div>
    @endif

    @if($deliveryCount > 0)
        <div class="dash-activity-table-wrap">
            <table class="dash-activity-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Agent</th>
                        <th>Delivery</th>
                        <th class="is-right">Total</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliveries as $delivery)
                        @php
                            $order = $delivery->order;
                            $type = $order?->order_type ?? 'regular';
                            $isReturn = $type === 'return';
                            $agentName = $order?->agent?->name ?? '—';
                            $initials = collect(explode(' ', trim($agentName)))
                                ->filter()
                                ->take(2)
                                ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
                                ->implode('');
                            $routeLabel = $delivery->route?->name ?? '—';
                            if ($delivery->vehicle) {
                                $routeLabel .= ' · ' . $delivery->vehicle->name;
                            }
                            $deliveryStep = match ($delivery->status) {
                                'scheduled' => 1,
                                'in_transit' => 3,
                                'delivered' => 5,
                                'exception' => 2,
                                default => 1,
                            };
                            $deliveryProgress = $delivery->status === 'in_transit';
                        @endphp
                        <tr class="dash-order-row" :class="{ 'is-expanded': openProgressId === {{ $delivery->id }} }">
                            <td class="whitespace-nowrap">
                                <a href="{{ route('admin.deliveries.show', $delivery) }}" class="group inline-flex items-center gap-2">
                                    <span class="dash-order-id">#{{ $delivery->order_id }}</span>
                                    <span @class([
                                        'dash-order-type',
                                        'dash-order-type-return' => $isReturn,
                                        'dash-order-type-regular' => ! $isReturn,
                                    ])>
                                        {{ $type }}
                                    </span>
                                </a>
                            </td>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <span class="dash-order-agent-avatar">{{ $initials ?: '—' }}</span>
                                    <div class="min-w-0">
                                        <p class="erp-body-strong truncate">{{ $agentName }}</p>
                                        @if($order?->agent?->area || $order?->agent?->zone)
                                            <p class="erp-caption truncate">
                                                {{ $order->agent->area }}{{ $order->agent->area && $order->agent->zone ? ', ' : '' }}{{ $order->agent->zone }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="erp-table-cell whitespace-nowrap">
                                {{ optional($order?->delivery_date)->format('d M Y') ?? '—' }}
                            </td>
                            <td class="is-right whitespace-nowrap">
                                <span @class([
                                    'erp-table-num',
                                    'text-error-600 dark:text-error-400' => $isReturn,
                                ])>
                                    {{ $currencyCode }} {{ number_format($order?->total ?? 0, 0) }}
                                </span>
                            </td>
                            <td class="dash-order-progress-cell">
                                <x-dashboard.delivery-progress-trigger
                                    :delivery-id="$delivery->id"
                                    :status="$delivery->status"
                                    :step="$deliveryStep"
                                    :in-progress="$deliveryProgress"
                                />
                            </td>
                        </tr>
                        <tr x-show="openProgressId === {{ $delivery->id }}"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="dash-order-progress-row">
                            <td colspan="5" class="!p-0">
                                <x-dashboard.delivery-progress-detail
                                    :delivery-id="$delivery->id"
                                    :status="$delivery->status"
                                    :step="$deliveryStep"
                                    :in-progress="$deliveryProgress"
                                    :delivery-url="route('admin.deliveries.show', $delivery)"
                                    :order-id="$delivery->order_id"
                                    :agent-name="$agentName"
                                    :route-label="$routeLabel"
                                />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="dash-activity-widget-body flex flex-1 flex-col items-center justify-center py-12 text-center">
            <div class="dash-activity-empty-icon dash-activity-empty-icon-neutral">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 0 0-2.654-9.874A3.75 3.75 0 0 0 17.25 6H9.75a3.75 3.75 0 0 0-3.548 2.524A49.902 49.902 0 0 0 3.506 18.376c-.039.62.469 1.124 1.09 1.124H8.25Z" />
                </svg>
            </div>
            <p class="erp-body-strong mt-4">No deliveries recorded yet</p>
            <p class="erp-caption mt-1">New dispatch runs will appear here as they are created.</p>
        </div>
    @endif
</div>
