@props([
    'board' => [],
])

@php
    $groups = $board['groups'] ?? [];
    $unscheduled = $board['unscheduled'] ?? collect();
    $totals = $board['totals'] ?? [];
    $hasContent = count($groups) > 0 || $unscheduled->count() > 0;

    $stops = collect($groups)->flatMap(function (array $group) {
        return collect($group['stops'] ?? [])->map(fn (array $stop) => array_merge($stop, [
            'route_name' => $group['route_name'] ?? '—',
            'vehicle_name' => $group['vehicle_name'] ?? '—',
        ]));
    });
@endphp

<section {{ $attributes->merge(['class' => 'dash-recent-orders-section']) }}>
    <x-dashboard.section-header
        title="Today's dispatch board"
        description="Open runs due today or overdue."
        class="mb-5"
    >
        <x-slot:actions>
            <a href="{{ route('admin.vehicle-schedule.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
                Fleet schedule
            </a>
            <a href="{{ route('admin.deliveries.create') }}" class="erp-btn-primary !px-3 !py-1.5 !text-xs">
                Schedule delivery
            </a>
        </x-slot:actions>
    </x-dashboard.section-header>

    <div class="dash-activity-widget dash-recent-orders-widget"
         x-data="{
            openProgressId: null,
            toggleProgress(id) {
                this.openProgressId = this.openProgressId === id ? null : id;
            },
         }"
         @keydown.escape.window="openProgressId = null">
        <div class="dash-performance-widget-header">
            <div class="min-w-0">
                <div class="flex items-center gap-2.5">
                    <span class="dash-activity-icon dash-activity-icon-brand">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="dash-performance-widget-title">{{ $board['date_label'] ?? today()->format('l, j F Y') }}</h3>
                        <p class="dash-performance-widget-desc !mt-0">
                            {{ number_format($totals['routes'] ?? 0) }} {{ Str::plural('route', $totals['routes'] ?? 0) }}
                            · {{ number_format($totals['deliveries'] ?? 0) }} {{ Str::plural('stop', $totals['deliveries'] ?? 0) }}
                            @if(($totals['unscheduled'] ?? 0) > 0)
                                · {{ number_format($totals['unscheduled']) }} unscheduled
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            @if($stops->count() > 0)
                <span class="dash-activity-summary-badge dash-activity-summary-badge-neutral">
                    {{ number_format($stops->count()) }} on board
                </span>
            @endif
        </div>

        @if(! $hasContent)
            <div class="dash-activity-widget-body flex flex-1 flex-col items-center justify-center py-12 text-center">
                <div class="dash-activity-empty-icon">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 0 0-2.654-9.874A3.75 3.75 0 0 0 17.25 6H9.75a3.75 3.75 0 0 0-3.548 2.524A49.902 49.902 0 0 0 3.506 18.376c-.039.62.469 1.124 1.09 1.124H8.25Z" />
                    </svg>
                </div>
                <p class="erp-body-strong mt-4">No deliveries due today</p>
                <p class="erp-caption mt-1">Schedule a delivery when orders are ready to go out.</p>
                <a href="{{ route('admin.deliveries.create') }}" class="erp-btn-primary mt-4">Schedule a delivery</a>
            </div>
        @else
            @if($unscheduled->count() > 0)
                <div class="wh-dispatch-unscheduled">
                    <p class="wh-dispatch-unscheduled__label">Due today — not scheduled</p>
                    <div class="wh-dispatch-unscheduled__chips">
                        @foreach($unscheduled as $order)
                            <a href="{{ $order['href'] }}" class="wh-dispatch-unscheduled__chip">
                                #{{ $order['order_id'] }} · {{ $order['agent'] ?? '—' }}
                            </a>
                        @endforeach
                        @if(($totals['unscheduled'] ?? 0) > $unscheduled->count())
                            <a href="{{ route('admin.deliveries.create') }}" class="wh-dispatch-unscheduled__more">
                                +{{ ($totals['unscheduled'] ?? 0) - $unscheduled->count() }} more
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if($stops->count() > 0)
                <div class="dash-activity-table-wrap">
                    <table class="dash-activity-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Agent</th>
                                <th>Route</th>
                                <th>Vehicle</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stops as $stop)
                                @php
                                    $agentName = $stop['agent'] ?? '—';
                                    $initials = collect(explode(' ', trim($agentName)))
                                        ->filter()
                                        ->take(2)
                                        ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
                                        ->implode('');
                                    $deliveryStep = match ($stop['status']) {
                                        'scheduled' => 1,
                                        'in_transit' => 3,
                                        'delivered' => 5,
                                        'exception' => 2,
                                        default => 1,
                                    };
                                    $deliveryProgress = ($stop['status'] ?? '') === 'in_transit';
                                @endphp
                                <tr class="dash-order-row" :class="{ 'is-expanded': openProgressId === {{ $stop['id'] }} }">
                                    <td class="whitespace-nowrap">
                                        <a href="{{ $stop['href'] }}" class="group inline-flex items-center gap-2">
                                            <span class="dash-order-id">#{{ $stop['order_id'] }}</span>
                                            @if(! empty($stop['due_label']))
                                                <span class="dash-order-type dash-order-type-return">{{ $stop['due_label'] }}</span>
                                            @endif
                                        </a>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <span class="dash-order-agent-avatar">{{ $initials ?: '—' }}</span>
                                            <p class="erp-body-strong truncate">{{ $agentName }}</p>
                                        </div>
                                    </td>
                                    <td class="erp-table-cell whitespace-nowrap">{{ $stop['route_name'] }}</td>
                                    <td class="erp-table-cell whitespace-nowrap">{{ $stop['vehicle_name'] }}</td>
                                    <td class="dash-order-progress-cell">
                                        <x-dashboard.delivery-progress-trigger
                                            :delivery-id="$stop['id']"
                                            :status="$stop['status']"
                                            :step="$deliveryStep"
                                            :in-progress="$deliveryProgress"
                                        />
                                    </td>
                                </tr>
                                <tr x-show="openProgressId === {{ $stop['id'] }}"
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
                                            :delivery-id="$stop['id']"
                                            :status="$stop['status']"
                                            :step="$deliveryStep"
                                            :in-progress="$deliveryProgress"
                                            :delivery-url="$stop['href']"
                                            :order-id="$stop['order_id']"
                                            :agent-name="$agentName"
                                            :route-label="$stop['route_name'] . ' · ' . $stop['vehicle_name']"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
</section>
