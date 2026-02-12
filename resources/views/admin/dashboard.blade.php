@extends('layouts.app')

@section('content')
  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6 xl:col-span-7">
      <x-ecommerce.ecommerce-metrics
          :agent-count="$agentCount ?? 0"
          :total-order-count="$totalOrderCount ?? 0"
          :return-order-count="$returnOrderCount ?? 0"
      />

      <x-ecommerce.monthly-sale
          :month-labels="$monthLabels ?? []"
          :monthly-orders="$monthlyOrders ?? []"
      />
    </div>
    <div class="col-span-12 xl:col-span-5">
        <x-ecommerce.monthly-target
            :outstanding-receivables="$outstandingReceivables ?? 0"
            :monthly-receipts="$monthlyReceipts ?? []"
            :today-receipts="$todayReceipts ?? 0"
        />
    </div>

    <div class="col-span-12">
      <x-ecommerce.statistics-chart />
    </div>

    <div class="col-span-12">
      <x-ecommerce.recent-orders :orders="$recentOrders ?? collect()" />
    </div>
  </div>
@endsection

@push('scripts')
    @if(isset($chartDays) && $chartDays instanceof \Illuminate\Support\Collection && $chartDays->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.ApexCharts) return;

                const labels = @json($chartDays->pluck('label'));
                const orders = @json($chartDays->pluck('orders'));
                const receipts = @json($chartDays->pluck('receipts'));

                const options = {
                    chart: {
                        type: 'area',
                        height: 220,
                        toolbar: { show: false },
                        foreColor: '#667085',
                        dropShadow: {
                            enabled: true,
                            top: 4,
                            left: 0,
                            blur: 3,
                            opacity: 0.1
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2.5
                    },
                    dataLabels: { enabled: false },
                    grid: {
                        borderColor: '#E4E7EC',
                        strokeDashArray: 4,
                        row: {
                            opacity: 0.02
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 0.7,
                            opacityFrom: 0.25,
                            opacityTo: 0,
                            stops: [0, 90, 100]
                        }
                    },
                    markers: {
                        size: 3,
                        strokeWidth: 0
                    },
                    tooltip: {
                        shared: true,
                        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                        y: {
                            formatter: (val, opts) => {
                                const seriesName = opts.seriesIndex === 1 ? 'BDT ' : '';
                                return seriesName + val;
                            }
                        }
                    },
                    xaxis: {
                        categories: labels,
                        labels: {
                            style: { fontSize: '11px' }
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: {
                            style: { fontSize: '11px' }
                        }
                    },
                    colors: ['#465FFF', '#12B76A'],
                    series: [
                        { name: 'Orders', data: orders },
                        { name: 'Receipts (BDT)', data: receipts }
                    ],
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left',
                        fontSize: '11px',
                        markers: { radius: 12 }
                    }
                };

                const el = document.querySelector('#chartThree');
                if (!el) return;

                const chart = new window.ApexCharts(el, options);
                chart.render();

                // Expose for debugging if needed
                window.dashboardStatsChart = chart;

                // Wire up Overview / Sales / Revenue tabs
                const tabs = document.querySelectorAll('[data-stats-tab]');
                tabs.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const mode = btn.getAttribute('data-stats-tab') || 'overview';

                        let series;
                        if (mode === 'sales') {
                            series = [
                                { name: 'Orders', data: orders },
                            ];
                        } else if (mode === 'revenue') {
                            series = [
                                { name: 'Receipts (BDT)', data: receipts },
                            ];
                        } else {
                            // overview
                            series = [
                                { name: 'Orders', data: orders },
                                { name: 'Receipts (BDT)', data: receipts },
                            ];
                        }

                        chart.updateOptions({
                            series,
                            legend: {
                                show: series.length > 1,
                                position: 'top',
                                horizontalAlign: 'left',
                                fontSize: '11px',
                                markers: { radius: 12 },
                            },
                        });
                    });
                });
            });
        </script>
    @endif
@endpush
