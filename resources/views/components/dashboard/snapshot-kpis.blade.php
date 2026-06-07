@props([
    'currencyCode' => 'BDT',
    'currentMonthLabel' => '',
    'agentCount' => 0,
    'monthOrderCount' => 0,
    'monthReturnCount' => 0,
    'monthlyAchieved' => 0,
    'outstandingReceivables' => 0,
    'pendingDeliveryCount' => 0,
    'todayProductionQty' => 0,
    'lowStockAlertCount' => 0,
])

@php
    $cards = [
        [
            'label' => 'Agents',
            'numeric' => number_format($agentCount ?? 0),
            'caption' => 'Active selling partners',
            'href' => route('admin.agents.index'),
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'users',
        ],
        [
            'label' => 'Sales orders',
            'numeric' => number_format($monthOrderCount ?? 0),
            'caption' => 'Customer sales this month',
            'href' => route('admin.orders.index', ['type' => 'sales']),
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
        [
            'label' => 'Returns',
            'numeric' => number_format($monthReturnCount ?? 0),
            'caption' => 'Customer return orders',
            'href' => route('admin.orders.index', ['type' => 'return']),
            'tone' => 'error',
            'valueTone' => ($monthReturnCount ?? 0) > 0 ? 'danger' : 'neutral',
            'icon' => 'returns',
        ],
        [
            'label' => 'Revenue',
            'numeric' => number_format((float) $monthlyAchieved, 0),
            'currency' => $currencyCode,
            'caption' => $currentMonthLabel . ' invoiced sales',
            'href' => route('admin.finance.index'),
            'tone' => 'purple',
            'valueTone' => 'brand',
            'icon' => 'revenue',
        ],
        [
            'label' => 'Receivables',
            'numeric' => number_format((float) $outstandingReceivables, 0),
            'currency' => $currencyCode,
            'caption' => 'Outstanding invoice balance',
            'href' => route('admin.finance.index'),
            'tone' => 'orange',
            'valueTone' => (float) $outstandingReceivables > 0 ? 'warning' : 'neutral',
            'icon' => 'receivables',
        ],
        [
            'label' => 'Pending deliveries',
            'numeric' => number_format($pendingDeliveryCount ?? 0),
            'caption' => 'Confirmed to dispatched orders',
            'href' => route('admin.deliveries.index'),
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'delivery',
        ],
        [
            'label' => 'Today production',
            'numeric' => number_format((float) ($todayProductionQty ?? 0), 0),
            'caption' => 'Approved quantity today',
            'href' => route('admin.production.index'),
            'tone' => 'amber',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'Low stock alerts',
            'numeric' => number_format($lowStockAlertCount ?? 0),
            'caption' => 'Sellable items at or below reorder level',
            'href' => route('admin.inventory.low-stock'),
            'tone' => 'error',
            'valueTone' => ($lowStockAlertCount ?? 0) > 0 ? 'danger' : 'neutral',
            'icon' => 'alert',
        ],
    ];

    $valueToneClasses = [
        'neutral' => '',
        'brand' => 'dash-snapshot-metric-value--brand',
        'warning' => 'dash-snapshot-metric-value--warning',
        'danger' => 'dash-snapshot-metric-value--danger',
    ];
@endphp

<div class="dash-snapshot">
    <div class="dash-snapshot-header">
        <div>
            <p class="dash-snapshot-eyebrow">Operations snapshot</p>
            <h2 class="dash-snapshot-title">Today at a glance</h2>
        </div>
        <p class="dash-snapshot-desc max-w-sm sm:text-right">
            Core sales, finance, delivery, and stock signals in one place.
        </p>
    </div>

    <div class="dash-snapshot-grid">
    @foreach($cards as $card)
        @php
            $valueTone = $card['valueTone'] ?? 'neutral';
            $valueClass = trim('dash-snapshot-metric-value ' . ($valueToneClasses[$valueTone] ?? ''));
        @endphp
        <a href="{{ $card['href'] }}" class="dash-snapshot-metric dash-snapshot-metric--{{ $card['tone'] }} group">
            <div class="dash-snapshot-metric-head">
                <p class="dash-snapshot-metric-label">{{ $card['label'] }}</p>
                <span class="dash-snapshot-icon dash-snapshot-icon--{{ $card['tone'] }}">
                    @switch($card['icon'])
                        @case('users')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            @break
                        @case('orders')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            @break
                        @case('returns')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                            </svg>
                            @break
                        @case('revenue')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            @break
                        @case('receivables')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            @break
                        @case('delivery')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 0 0-2.654-9.874A3.75 3.75 0 0 0 17.25 6H9.75a3.75 3.75 0 0 0-3.548 2.524A49.902 49.902 0 0 0 3.506 18.376c-.039.62.469 1.124 1.09 1.124H8.25Z" />
                            </svg>
                            @break
                        @case('production')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                            </svg>
                            @break
                        @default
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                    @endswitch
                </span>
            </div>

            <p class="{{ $valueClass }}" title="{{ isset($card['currency']) ? $card['currency'] . ' ' . $card['numeric'] : $card['numeric'] }}">
                @if(isset($card['currency']))
                    <span class="dash-snapshot-metric-currency">{{ $card['currency'] }}</span>
                @endif
                <span class="dash-snapshot-metric-number">{{ $card['numeric'] }}</span>
            </p>
            <p class="dash-snapshot-metric-caption">{{ $card['caption'] }}</p>
        </a>
    @endforeach
    </div>
</div>
