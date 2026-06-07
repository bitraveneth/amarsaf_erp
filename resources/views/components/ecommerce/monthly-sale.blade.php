@props([
    'monthLabels' => [],
    'monthlyOrders' => [],
    'salesRangeMonths' => 12,
])

<div
    id="dashboard-monthly-sales-card"
    {{ $attributes->merge(['class' => 'dash-performance-widget']) }}
>
    <div class="dash-performance-widget-header">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="dash-performance-widget-title">Monthly orders</h3>
                    <p class="dash-performance-widget-desc !mt-0">Sales order count by delivery month</p>
                </div>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}" data-dashboard-sales-form class="shrink-0">
            <input type="hidden" name="target_month" value="{{ request('target_month', now()->format('Y-m')) }}">
            <label for="monthly-sales-range" class="sr-only">Filter monthly sales range</label>
            <select
                id="monthly-sales-range"
                name="sales_range"
                data-dashboard-sales-range
                class="dash-select !px-3 !py-2 !text-sm"
            >
                <option value="3" @selected((int) $salesRangeMonths === 3)>Last 3 months</option>
                <option value="6" @selected((int) $salesRangeMonths === 6)>Last 6 months</option>
                <option value="12" @selected((int) $salesRangeMonths === 12)>Last 12 months</option>
            </select>
        </form>
    </div>

    <div class="dash-performance-widget-body pb-5">
        <div class="dash-performance-chart overflow-hidden">
            <div id="chartOne" class="h-[220px] w-full"></div>
        </div>
    </div>
</div>
