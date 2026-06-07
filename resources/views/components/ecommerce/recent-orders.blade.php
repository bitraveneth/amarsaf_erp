@props([
    'orders' => [],
    'currencyCode' => 'BDT',
    'showHeader' => true,
])

@php
    $orderCount = count($orders);
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="dash-performance-widget-title">Recent orders</h3>
                        <p class="dash-performance-widget-desc !mt-0">Latest sales and return activity</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($orderCount > 0)
                    <span class="dash-activity-summary-badge dash-activity-summary-badge-neutral">
                        {{ number_format($orderCount) }} shown
                    </span>
                @endif
                <a href="{{ route('admin.orders.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
                    See all
                </a>
            </div>
        </div>
    @elseif($orderCount > 0)
        <div class="dash-recent-orders-widget__meta border-b border-gray-100/80 px-5 py-3 text-right dark:border-white/10">
            <span class="dash-activity-summary-badge dash-activity-summary-badge-neutral">
                {{ number_format($orderCount) }} shown
            </span>
        </div>
    @endif

    @if($orderCount > 0)
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
                    @foreach($orders as $order)
                        @php
                            $type = $order->order_type ?? 'regular';
                            $isReturn = $type === 'return';
                            $agentName = $order->agent->name ?? '—';
                            $initials = collect(explode(' ', trim($agentName)))
                                ->filter()
                                ->take(2)
                                ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
                                ->implode('');
                            $salesStep = match ($order->status) {
                                'draft' => 2,
                                'confirmed' => 3,
                                'picked', 'packed' => 3,
                                'dispatched' => 4,
                                'delivered' => 5,
                                default => 1,
                            };
                            $salesProgress = in_array($order->status, ['picked', 'packed', 'dispatched'], true);
                        @endphp
                        <tr class="dash-order-row" :class="{ 'is-expanded': openProgressId === {{ $order->id }} }">
                            <td class="whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="group inline-flex items-center gap-2">
                                    <span class="dash-order-id">#{{ $order->id }}</span>
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
                                        @if($order->agent?->area || $order->agent?->zone)
                                            <p class="erp-caption truncate">
                                                {{ $order->agent->area }}{{ $order->agent->area && $order->agent->zone ? ', ' : '' }}{{ $order->agent->zone }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="erp-table-cell whitespace-nowrap">
                                {{ optional($order->delivery_date)->format('d M Y') ?? '—' }}
                            </td>
                            <td class="is-right whitespace-nowrap">
                                <span @class([
                                    'erp-table-num',
                                    'text-error-600 dark:text-error-400' => $isReturn,
                                ])>
                                    {{ $currencyCode }} {{ number_format($order->total ?? 0, 0) }}
                                </span>
                            </td>
                            <td class="dash-order-progress-cell">
                                <x-dashboard.order-progress-trigger
                                    :order-id="$order->id"
                                    :status="$order->status"
                                    :step="$salesStep"
                                    :in-progress="$salesProgress"
                                />
                            </td>
                        </tr>
                        <tr x-show="openProgressId === {{ $order->id }}"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="dash-order-progress-row">
                            <td colspan="5" class="!p-0">
                                <x-dashboard.order-progress-detail
                                    :order-id="$order->id"
                                    :status="$order->status"
                                    :step="$salesStep"
                                    :in-progress="$salesProgress"
                                    :order-url="route('admin.orders.show', $order)"
                                    :agent-name="$agentName"
                                    :delivery-date="optional($order->delivery_date)->format('d M Y') ?? '—'"
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                </svg>
            </div>
            <p class="erp-body-strong mt-4">No recent orders yet</p>
            <p class="erp-caption mt-1">New orders will appear here as they come in.</p>
        </div>
    @endif
</div>
