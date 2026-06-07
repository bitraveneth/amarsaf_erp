@props([
    'alerts' => [],
])

@php
    $alertIcons = [
        'Pending QC approval' => [
            'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z',
            'iconTone' => 'warning',
        ],
        'Low stock SKUs' => [
            'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
            'iconTone' => 'error',
        ],
        'Overdue invoices' => [
            'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
            'iconTone' => 'error',
        ],
        'Pending deliveries' => [
            'icon' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 00-2.654-.816A48.094 48.094 0 0018 12.75v-1.5m-6 0V9.75A2.25 2.25 0 0012 7.5h-1.5m6 0V6A2.25 2.25 0 0015.75 4.5h-1.5m-6 0h-1.5A2.25 2.25 0 006 6.75v.75m0 0V9a2.25 2.25 0 002.25 2.25h1.5m-6 0h6',
            'iconTone' => 'warning',
        ],
    ];

    $totalAlerts = collect($alerts)->sum(fn (array $alert) => (int) ($alert['value'] ?? 0));
@endphp

<div {{ $attributes->merge(['class' => 'dash-activity-widget']) }}>
    <div class="dash-performance-widget-header">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5">
                <span class="dash-activity-icon dash-activity-icon-warning">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="dash-performance-widget-title">Needs attention</h3>
                    <p class="dash-performance-widget-desc !mt-0">Exceptions that may need action today</p>
                </div>
            </div>
        </div>

        @if(count($alerts) > 0)
            <span class="dash-activity-summary-badge dash-activity-summary-badge-warning">
                {{ number_format($totalAlerts) }} open
            </span>
        @else
            <span class="dash-activity-summary-badge dash-activity-summary-badge-success">
                All clear
            </span>
        @endif
    </div>

    @if(count($alerts) > 0)
        <div class="dash-activity-widget-body">
            <ul class="space-y-2.5">
                @foreach($alerts as $alert)
                    @php
                        $tone = $alert['tone'] ?? 'neutral';
                        $meta = $alertIcons[$alert['label']] ?? ['icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z', 'iconTone' => 'neutral'];
                        $iconTone = $meta['iconTone'] ?? $tone;
                    @endphp

                    <li>
                        <a href="{{ $alert['href'] }}" class="group dash-alert-card dash-alert-card-{{ $tone }}">
                            <span class="dash-alert-card-icon dash-alert-card-icon-{{ $iconTone }}">
                                <svg class="h-[1.125rem] w-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}" />
                                </svg>
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="flex items-start justify-between gap-3">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $alert['label'] }}</span>
                                    <span class="dash-alert-count dash-alert-count-{{ $tone }}">
                                        {{ number_format((int) $alert['value']) }}
                                    </span>
                                </span>
                                <span class="mt-1 block text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $alert['caption'] }}</span>
                            </span>

                            <svg class="dash-alert-card-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="dash-activity-widget-body flex flex-1 flex-col items-center justify-center py-10 text-center">
            <div class="dash-activity-empty-icon">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z" />
                </svg>
            </div>
            <p class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">Nothing urgent right now</p>
            <p class="mt-1 max-w-[16rem] text-xs leading-5 text-gray-500 dark:text-gray-400">
                QC, stock, invoices, and deliveries are all within normal limits.
            </p>
        </div>
    @endif
</div>
