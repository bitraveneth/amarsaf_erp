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
          :sales-range-months="$salesRangeMonths ?? 12"
      />
    </div>
    <div class="col-span-12 xl:col-span-5 xl:h-full">
        <x-ecommerce.monthly-target
            :currency-code="$currencyCode ?? config('app.currency', 'BDT')"
            :period-label="$currentMonthLabel ?? now()->format('F Y')"
            :target-basis="$monthlyTargetBasis ?? 'No monthly sales target configured'"
            :monthly-sales-target="$monthlySalesTarget ?? 0"
            :monthly-achieved="$monthlyAchieved ?? 0"
            :today-achieved="$todayAchieved ?? 0"
            :progress-percent="$monthlyTargetProgress ?? 0"
            :target-month-options="$targetMonthOptions ?? []"
        />
    </div>

    <div class="col-span-12">
      <x-ecommerce.statistics-chart />
    </div>

    <div class="col-span-12">
      <x-ecommerce.recent-orders
          :orders="$recentOrders ?? collect()"
          :currency-code="$currencyCode ?? config('app.currency', 'BDT')"
      />
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
                const production = @json($chartDays->pluck('production'));
                const revenue = @json($chartDays->pluck('revenue'));
                const currencyCode = @json($currencyCode ?? config('app.currency', 'BDT'));

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
                                const seriesName = opts.series[opts.seriesIndex]?.name || '';
                                const currencyPrefix = seriesName.includes('Revenue') ? `${currencyCode} ` : '';
                                return `${currencyPrefix}${val}`;
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
                    colors: ['#465FFF', '#12B76A', '#F79009'],
                    series: [
                        { name: 'Orders', data: orders },
                        { name: 'Production Qty', data: production },
                        { name: `Revenue (${currencyCode})`, data: revenue }
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
                        } else if (mode === 'production') {
                            series = [
                                { name: 'Production Qty', data: production },
                            ];
                        } else if (mode === 'revenue') {
                            series = [
                                { name: `Revenue (${currencyCode})`, data: revenue },
                            ];
                        } else {
                            // overview
                            series = [
                                { name: 'Orders', data: orders },
                                { name: 'Production Qty', data: production },
                                { name: `Revenue (${currencyCode})`, data: revenue },
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
