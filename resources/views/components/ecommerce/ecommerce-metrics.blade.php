@props([
    'agentCount' => 0,
    'totalOrderCount' => 0,
    'returnOrderCount' => 0,
    'currencyCode' => 'BDT',
    'monthlyRevenue' => 0,
    'outstandingReceivables' => 0,
    'pendingDeliveryCount' => 0,
    'todayProductionQty' => 0,
    'lowStockAlertCount' => 0,
])

@php
    $cards = [
        [
            'label' => 'Agents',
            'value' => number_format($agentCount ?? 0),
            'caption' => 'Active selling partners',
            'tone' => 'neutral',
            'icon' => 'agent',
            'href' => route('admin.agents.index'),
        ],
        [
            'label' => 'Sales Orders',
            'value' => number_format($totalOrderCount ?? 0),
            'caption' => 'Customer sales only',
            'tone' => 'neutral',
            'icon' => 'orders',
            'href' => route('admin.orders.index', ['type' => 'sales']),
        ],
        [
            'label' => 'Returns',
            'value' => number_format($returnOrderCount ?? 0),
            'caption' => 'Customer return orders',
            'tone' => 'error',
            'icon' => 'returns',
            'href' => route('admin.orders.index', ['type' => 'return']),
        ],
        [
            'label' => 'Revenue',
            'value' => $currencyCode . ' ' . number_format((float) ($monthlyRevenue ?? 0), 0),
            'caption' => 'Selected month invoiced sales',
            'tone' => 'neutral',
            'icon' => 'revenue',
            'href' => route('admin.finance.index'),
        ],
        [
            'label' => 'Receivables',
            'value' => $currencyCode . ' ' . number_format((float) ($outstandingReceivables ?? 0), 0),
            'caption' => 'Outstanding invoice balance',
            'tone' => 'warning',
            'icon' => 'receivables',
            'href' => route('admin.finance.index'),
        ],
        [
            'label' => 'Pending Deliveries',
            'value' => number_format($pendingDeliveryCount ?? 0),
            'caption' => 'Confirmed to dispatched orders',
            'tone' => 'neutral',
            'icon' => 'delivery',
            'href' => route('admin.deliveries.index'),
        ],
        [
            'label' => 'Today Production',
            'value' => number_format((float) ($todayProductionQty ?? 0), 0),
            'caption' => 'Approved quantity today',
            'tone' => 'neutral',
            'icon' => 'production',
            'href' => route('admin.production.index'),
        ],
        [
            'label' => 'Low Stock Alerts',
            'value' => number_format($lowStockAlertCount ?? 0),
            'caption' => 'Sellable items at or below reorder level',
            'tone' => 'error',
            'icon' => 'alert',
            'href' => route('admin.inventory.low-stock'),
        ],
    ];

    $valueClasses = [
        'neutral' => 'text-gray-800 dark:text-white/90',
        'error' => 'text-error-600 dark:text-error-500',
        'warning' => 'text-orange-600 dark:text-orange-400',
        'success' => 'text-success-600 dark:text-success-500',
    ];
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
    @foreach($cards as $card)
        @php
            $valueClass = $valueClasses[$card['tone']] ?? $valueClasses['neutral'];
        @endphp

        <a href="{{ $card['href'] }}"
           class="ta-card-padded block transition hover:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:hover:border-brand-500/30">
            <div class="ta-metric-icon">
                @if($card['icon'] === 'agent')
                    <svg class="h-6 w-6 fill-gray-800 dark:fill-white/90" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.804 5.602a2.197 2.197 0 1 0 0 4.394 2.197 2.197 0 0 0 0-4.394ZM5.107 7.799a3.697 3.697 0 1 1 7.394 0 3.697 3.697 0 0 1-7.394 0Zm-.245 7.522C4.087 16.088 3.703 17.061 3.516 17.861c-.033.142.004.256.09.35.095.103.26.188.47.188h9.349c.209 0 .374-.085.468-.188.086-.094.124-.208.09-.35-.186-.8-.57-1.773-1.345-2.541-.756-.749-1.948-1.366-3.888-1.366-1.94 0-3.132.617-3.888 1.366Z" />
                    </svg>
                @elseif($card['icon'] === 'orders')
                    <svg class="h-6 w-6 fill-gray-800 dark:fill-white/90" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.665 3.756a.75.75 0 0 1 .671 0l6.446 3.223-6.446 3.223a.75.75 0 0 1-.672 0L5.22 6.979l6.445-3.223Zm-7.629 4.436V16.095c0 .284.16.544.414.671l6.542 3.271V11.65a2.6 2.6 0 0 1-.256-.108l-6.7-3.35Zm8.714 12.282 6.543-3.272a.75.75 0 0 0 .413-.671V8.192l-6.7 3.35a2.6 2.6 0 0 1-.256.108v8.824Z" />
                    </svg>
                @elseif($card['icon'] === 'returns')
                    <svg class="h-6 w-6 fill-gray-800 dark:fill-white/90" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.53 4.47a.75.75 0 0 1 0 1.06L7.81 7.25H13a5.75 5.75 0 0 1 0 11.5h-3a.75.75 0 0 1 0-1.5h3a4.25 4.25 0 0 0 0-8.5H7.81l1.72 1.72a.75.75 0 1 1-1.06 1.06l-3-3a.75.75 0 0 1 0-1.06l3-3a.75.75 0 0 1 1.06 0Z" />
                    </svg>
                @elseif($card['icon'] === 'receivables')
                    <svg class="h-6 w-6 stroke-gray-800 dark:stroke-white/90" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                    </svg>
                @elseif($card['icon'] === 'revenue')
                    <svg class="h-6 w-6 stroke-gray-800 dark:stroke-white/90" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 17.25V6.75m5.25 10.5V9.75m5.25 7.5v-4.5m5.25 4.5V4.5" />
                    </svg>
                @elseif($card['icon'] === 'delivery')
                    <svg class="h-6 w-6 stroke-gray-800 dark:stroke-white/90" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 18.75a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0Zm10.5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0ZM8.25 18.75h7.5M3.75 5.25h10.5v9.75H3.75V5.25Zm10.5 3h3.129c.398 0 .779.158 1.061.439l1.371 1.372c.281.281.439.663.439 1.06V15h-6V8.25Z" />
                    </svg>
                @elseif($card['icon'] === 'production')
                    <svg class="h-6 w-6 stroke-gray-800 dark:stroke-white/90" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 19.5h15m-13.5 0V9.75l4.5-3 4.5 3v9.75m-9 0h9m-4.5 0V14.25" />
                    </svg>
                @else
                    <svg class="h-6 w-6 stroke-gray-800 dark:stroke-white/90" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.57 3.862l-7.5 13.5A1.5 1.5 0 004.38 19.5h15.24a1.5 1.5 0 001.31-2.138l-7.5-13.5a1.5 1.5 0 00-2.62 0Z" />
                    </svg>
                @endif
            </div>

            <div class="mt-5">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $card['label'] }}</span>
                <h4 class="mt-2 font-bold text-title-sm {{ $valueClass }}">{{ $card['value'] }}</h4>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $card['caption'] }}</p>
            </div>
        </a>
    @endforeach
</div>
