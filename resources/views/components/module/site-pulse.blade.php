@props([
    'items' => [],
    'totalStock' => 0,
    'warehouseCount' => 0,
])

<div {{ $attributes->merge(['class' => 'dash-activity-widget']) }}>
    <div class="dash-performance-widget-header">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5">
                <span class="dash-activity-icon dash-activity-icon-brand">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5a1.125 1.125 0 0 0-1.125-1.125H3.375a1.125 1.125 0 0 0-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="dash-performance-widget-title">Site stock pulse</h3>
                    <p class="dash-performance-widget-desc !mt-0">
                        {{ number_format($totalStock, 0) }} units across {{ $warehouseCount }} {{ Str::plural('site', $warehouseCount) }}
                    </p>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.warehouses.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
            All sites
        </a>
    </div>

    @if(count($items) > 0)
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
    @else
        <div class="dash-activity-widget-body px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
            No warehouses registered yet.
        </div>
    @endif
</div>
