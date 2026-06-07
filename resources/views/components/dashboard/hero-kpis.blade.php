@props([
    'currencyCode' => 'BDT',
    'currentMonthLabel' => '',
    'monthlyAchieved' => 0,
    'todayAchieved' => 0,
    'collectionsThisMonth' => 0,
    'collectionRate' => 0,
    'outstandingReceivables' => 0,
    'overdueInvoiceCount' => 0,
    'monthOrderCount' => 0,
])

@php
    $cards = [
        [
            'label' => 'MTD Revenue',
            'value' => $currencyCode . ' ' . number_format((float) $monthlyAchieved, 0),
            'caption' => 'Today ' . $currencyCode . ' ' . number_format((float) $todayAchieved, 0),
            'href' => route('admin.finance.index'),
            'tone' => 'neutral',
            'icon' => 'revenue',
        ],
        [
            'label' => 'MTD Collections',
            'value' => $currencyCode . ' ' . number_format((float) $collectionsThisMonth, 0),
            'caption' => number_format((float) $collectionRate, 1) . '% of invoiced sales',
            'href' => route('admin.finance.index'),
            'tone' => 'neutral',
            'icon' => 'collections',
        ],
        [
            'label' => 'Outstanding AR',
            'value' => $currencyCode . ' ' . number_format((float) $outstandingReceivables, 0),
            'caption' => $overdueInvoiceCount > 0
                ? number_format($overdueInvoiceCount) . ' overdue invoice(s)'
                : 'No overdue invoices',
            'href' => route('admin.finance.index'),
            'tone' => $overdueInvoiceCount > 0 ? 'error' : 'neutral',
            'icon' => 'receivables',
        ],
        [
            'label' => 'MTD Orders',
            'value' => number_format($monthOrderCount ?? 0),
            'caption' => 'Sales orders this month',
            'href' => route('admin.orders.index', ['type' => 'sales']),
            'tone' => 'neutral',
            'icon' => 'orders',
        ],
    ];
@endphp

<div class="dash-kpi-grid">
    @foreach($cards as $card)
        <a href="{{ $card['href'] }}" class="dash-kpi">
            <div class="flex items-start justify-between gap-3">
                <div class="dash-kpi-icon">
                    @switch($card['icon'])
                        @case('revenue')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            @break
                        @case('collections')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                            </svg>
                            @break
                        @case('receivables')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            @break
                        @default
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                    @endswitch
                </div>
            </div>

            <p class="dash-kpi-label mt-3">{{ $card['label'] }}</p>
            <p @class(['dash-kpi-value', 'dash-kpi-value-error' => $card['tone'] === 'error'])>{{ $card['value'] }}</p>
            <p class="dash-kpi-caption">{{ $card['caption'] }}</p>
        </a>
    @endforeach
</div>
