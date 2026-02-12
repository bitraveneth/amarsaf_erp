@props([
    'monthLabels' => [],
    'monthlyOrders' => [],
])

<div
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Monthly Sales
        </h3>

        <!-- Dropdown Menu -->
        <x-common.dropdown-menu />
        <!-- End Dropdown Menu -->
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
