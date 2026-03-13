@props([
    'monthLabels' => [],
    'monthlyOrders' => [],
    'salesRangeMonths' => 12,
])

<div
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Monthly Sales
            </h3>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                Current month: {{ now()->format('F Y') }}
            </p>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}">
            <label for="monthly-sales-range" class="sr-only">Filter monthly sales range</label>
            <select
                id="monthly-sales-range"
                name="sales_range"
                onchange="this.form.submit()"
                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-theme-xs focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            >
                <option value="3" @selected((int) $salesRangeMonths === 3)>Last 3 months</option>
                <option value="6" @selected((int) $salesRangeMonths === 6)>Last 6 months</option>
                <option value="12" @selected((int) $salesRangeMonths === 12)>Last 12 months</option>
            </select>
        </form>
    </div>

    <div class="max-w-full">
        <div id="chartOne" class="h-56 w-full"></div>
    </div>
</div>

@push('scripts')
    @if(!empty($monthLabels ?? []) && !empty($monthlyOrders ?? []))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.ApexCharts) return;

                const labels = @json($monthLabels);
                const orders = @json($monthlyOrders);

                const options = {
                    chart: {
                        type: 'bar',
                        height: 220,
                        toolbar: { show: false },
                        foreColor: '#667085',
                    },
                    series: [{
                        name: 'Sales (orders)',
                        data: orders,
                    }],
                    colors: ['#465FFF'],
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '39%',
                            borderRadius: 5,
                            borderRadiusApplication: 'end'
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: {
                        show: true,
                        width: 3,
                        colors: ['transparent']
                    },
                    grid: {
                        borderColor: '#E4E7EC',
                        strokeDashArray: 4,
                        yaxis: { lines: { show: true } }
                    },
                    xaxis: {
                        categories: labels,
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                    },
                    yaxis: {
                        labels: { style: { fontSize: '11px' } },
                        title: { text: undefined },
                    },
                    tooltip: {
                        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                        y: {
                            formatter: (val) => val,
                        }
                    },
                    fill: {
                        opacity: 0.95,
                    },
                    legend: {
                        show: false,
                    },
                };

                const el = document.querySelector('#chartOne');
                if (!el) return;

                const chart = new window.ApexCharts(el, options);
                chart.render();
            });
        </script>
    @endif
@endpush
