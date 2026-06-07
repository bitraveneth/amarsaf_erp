@props([
    'currencyCode' => 'BDT',
    'currentMonthLabel' => '',
    'monthlyAchieved' => 0,
    'collectionsThisMonth' => 0,
    'collectionRate' => 0,
    'outstandingReceivables' => 0,
    'overdueInvoiceCount' => 0,
    'monthlySalesTarget' => 0,
    'targetGap' => 0,
    'todayOrderCount' => 0,
])

@php
    $items = [
        [
            'label' => 'MTD collections',
            'value' => $currencyCode . ' ' . number_format((float) $collectionsThisMonth, 0),
            'caption' => number_format((float) $collectionRate, 1) . '% collected against ' . $currentMonthLabel . ' sales',
            'href' => route('admin.finance.index'),
            'tone' => $collectionRate >= 70 ? 'neutral' : ($collectionRate >= 40 ? 'warning' : 'neutral'),
            'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z',
            'iconTone' => 'neutral',
        ],
        [
            'label' => 'Outstanding receivables',
            'value' => $currencyCode . ' ' . number_format((float) $outstandingReceivables, 0),
            'caption' => $overdueInvoiceCount > 0
                ? number_format($overdueInvoiceCount) . ' overdue invoice(s) need follow-up'
                : 'No overdue invoices right now',
            'href' => route('admin.finance.index'),
            'tone' => $overdueInvoiceCount > 0 ? 'error' : 'neutral',
            'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
            'iconTone' => $overdueInvoiceCount > 0 ? 'error' : 'neutral',
        ],
    ];

    if ((float) $monthlySalesTarget > 0) {
        $items[] = [
            'label' => 'Sales target gap',
            'value' => $currencyCode . ' ' . number_format((float) $targetGap, 0),
            'caption' => 'Remaining to hit ' . $currencyCode . ' ' . number_format((float) $monthlySalesTarget, 0) . ' target',
            'href' => route('admin.sales-targets.index'),
            'tone' => (float) $targetGap > 0 ? 'warning' : 'neutral',
            'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
            'iconTone' => (float) $targetGap > 0 ? 'warning' : 'neutral',
        ];
    } else {
        $items[] = [
            'label' => 'MTD invoiced sales',
            'value' => $currencyCode . ' ' . number_format((float) $monthlyAchieved, 0),
            'caption' => $currentMonthLabel . ' revenue booked so far',
            'href' => route('admin.finance.index'),
            'tone' => 'neutral',
            'icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'iconTone' => 'neutral',
        ];
    }

    $items[] = [
        'label' => 'Orders due today',
        'value' => number_format($todayOrderCount ?? 0),
        'caption' => 'Sales orders scheduled for delivery today',
        'href' => route('admin.orders.index', ['type' => 'sales']),
        'tone' => ($todayOrderCount ?? 0) > 0 ? 'warning' : 'neutral',
        'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0z',
        'iconTone' => 'neutral',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'dash-activity-widget']) }}>
    <div class="dash-performance-widget-header">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5">
                <span class="dash-activity-icon dash-activity-icon-brand">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="dash-performance-widget-title">Finance pulse</h3>
                    <p class="dash-performance-widget-desc !mt-0">Collections, receivables, and sales follow-up</p>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.finance.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
            Finance
        </a>
    </div>

    <div class="dash-activity-widget-body">
        <ul class="space-y-2.5">
            @foreach($items as $item)
                @php
                    $tone = $item['tone'] ?? 'neutral';
                    $iconTone = $item['iconTone'] ?? $tone;
                @endphp
                <li>
                    <a href="{{ $item['href'] }}" class="group dash-alert-card dash-alert-card-{{ $tone }}">
                        <span class="dash-alert-card-icon dash-alert-card-icon-{{ $iconTone }}">
                            <svg class="h-[1.125rem] w-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="flex items-start justify-between gap-3">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['label'] }}</span>
                                <span class="dash-alert-count dash-alert-count-{{ $tone }} text-[11px] !px-2 !py-0.5">
                                    {{ $item['value'] }}
                                </span>
                            </span>
                            <span class="mt-1 block text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $item['caption'] }}</span>
                        </span>

                        <svg class="dash-alert-card-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
