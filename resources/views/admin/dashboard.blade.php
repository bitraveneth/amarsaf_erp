@extends('layouts.app')

@section('content')
  <div
    class="dash-page"
    data-dashboard-ajax
    data-dashboard-url="{{ route('admin.dashboard') }}"
    data-currency-code="{{ $currencyCode ?? config('app.currency', 'BDT') }}"
  >
    <x-dashboard.page-header title="Dashboard" />

    <x-dashboard.quick-actions class="mt-1" />

    <x-dashboard.snapshot-kpis
        :currency-code="$currencyCode ?? config('app.currency', 'BDT')"
        :current-month-label="$currentMonthLabel ?? now()->format('F Y')"
        :agent-count="$agentCount ?? 0"
        :month-order-count="$monthOrderCount ?? 0"
        :month-return-count="$monthReturnCount ?? 0"
        :monthly-achieved="$monthlyAchieved ?? 0"
        :outstanding-receivables="$outstandingReceivables ?? 0"
        :pending-delivery-count="$pendingDeliveryCount ?? 0"
        :today-production-qty="$todayProductionQty ?? 0"
        :low-stock-alert-count="$lowStockAlertCount ?? 0"
    />

    <section class="dash-performance mt-2">
      <x-dashboard.section-header
          title="Sales performance"
          description="Monthly order volume, agent targets, and the last seven days of activity."
          class="mb-5"
      />

      <div class="dash-performance-grid">
        <div class="dash-performance-row">
          <div class="col-span-12 xl:col-span-7">
            <x-ecommerce.monthly-sale
                class="h-full"
                :month-labels="$monthLabels ?? []"
                :monthly-orders="$monthlyOrders ?? []"
                :sales-range-months="$salesRangeMonths ?? 12"
            />
          </div>

          <div class="col-span-12 xl:col-span-5">
            <x-ecommerce.monthly-target
                class="h-full"
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
        </div>

        <x-ecommerce.statistics-chart />
      </div>
    </section>

    <section class="dash-recent-orders-section">
      <x-dashboard.section-header
          title="Recent orders"
          description="Latest sales and return orders from your agents."
          class="mb-5"
      >
        <x-slot:actions>
          <a href="{{ route('admin.orders.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">
            See all orders
          </a>
        </x-slot:actions>
      </x-dashboard.section-header>

      <x-ecommerce.recent-orders
          :show-header="false"
          :orders="$recentOrders ?? collect()"
          :currency-code="$currencyCode ?? config('app.currency', 'BDT')"
      />
    </section>

    <section class="dash-activity-panel">
      <x-dashboard.section-header
          title="Activity"
          description="Operational alerts and finance items that need follow-up today."
          class="mb-5"
      />

      <div class="dash-activity-grid dash-activity-grid--pair">
        <x-dashboard.ops-alerts
            class="h-full"
            :alerts="$opsAlerts ?? []"
        />

        <x-dashboard.finance-pulse
            class="h-full"
            :currency-code="$currencyCode ?? config('app.currency', 'BDT')"
            :current-month-label="$currentMonthLabel ?? now()->format('F Y')"
            :monthly-achieved="$monthlyAchieved ?? 0"
            :collections-this-month="$collectionsThisMonth ?? 0"
            :collection-rate="$collectionRate ?? 0"
            :outstanding-receivables="$outstandingReceivables ?? 0"
            :overdue-invoice-count="$overdueInvoiceCount ?? 0"
            :monthly-sales-target="$monthlySalesTarget ?? 0"
            :target-gap="$targetGap ?? 0"
            :today-order-count="$todayOrderCount ?? 0"
        />
      </div>
    </section>
  </div>
@endsection

@push('scripts')
    @if(isset($chartDays) && $chartDays instanceof \Illuminate\Support\Collection && $chartDays->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.ApexCharts) return;

                const dashboardRoot = document.querySelector('[data-dashboard-ajax]');
                if (!dashboardRoot) return;

                const currencyCode = dashboardRoot.dataset.currencyCode || @json($currencyCode ?? config('app.currency', 'BDT'));
                let currentMonthlySaleData = {
                    labels: @json($monthLabels),
                    orders: @json($monthlyOrders),
                };
                const MONTHLY_SALES_CHART_HEIGHT = 220;
                let monthlySalesResizeFrame = null;
                const initialStats = {
                    labels: @json($chartDays->pluck('label')),
                    orders: @json($chartDays->pluck('orders')),
                    production: @json($chartDays->pluck('production')),
                    revenue: @json($chartDays->pluck('revenue')),
                };

                const getStatsSeries = (mode, statsPayload) => {
                    if (mode === 'sales') {
                        return [{ name: 'Orders', data: statsPayload.orders }];
                    }

                    if (mode === 'production') {
                        return [{ name: 'Production Qty', data: statsPayload.production }];
                    }

                    if (mode === 'revenue') {
                        return [{ name: `Revenue (${currencyCode})`, data: statsPayload.revenue }];
                    }

                    return [
                        { name: 'Orders', data: statsPayload.orders },
                        { name: 'Production Qty', data: statsPayload.production },
                        { name: `Revenue (${currencyCode})`, data: statsPayload.revenue },
                    ];
                };

                const renderMonthlySalesChart = (labels, orders) => {
                    const el = document.querySelector('#chartOne');
                    if (!el) return;

                    currentMonthlySaleData = { labels, orders };

                    if (window.dashboardMonthlySalesChart) {
                        window.dashboardMonthlySalesChart.destroy();
                    }

                    const chart = new window.ApexCharts(el, {
                        chart: {
                            type: 'bar',
                            height: MONTHLY_SALES_CHART_HEIGHT,
                            toolbar: { show: false },
                            foreColor: '#667085',
                            redrawOnParentResize: false,
                            redrawOnWindowResize: true,
                            parentHeightOffset: 0,
                        },
                        series: [{
                            name: 'Sales (orders)',
                            data: orders,
                        }],
                        colors: ['#7C3AED'],
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '42%',
                                borderRadius: 6,
                                borderRadiusApplication: 'end',
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
                            padding: { top: 0, left: 4, right: 8, bottom: 0 },
                            yaxis: { lines: { show: true } }
                        },
                        xaxis: {
                            categories: labels,
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: {
                                trim: true,
                                hideOverlappingLabels: false,
                                rotate: 0,
                                style: { fontSize: '10px', colors: '#98A2B3' },
                            },
                        },
                        yaxis: {
                            labels: { style: { fontSize: '10px', colors: '#98A2B3' } },
                            title: { text: undefined },
                        },
                        tooltip: {
                            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                            y: {
                                formatter: (val) => val,
                            }
                        },
                        fill: { opacity: 0.95 },
                        legend: { show: false },
                        responsive: [
                            {
                                breakpoint: 1024,
                                options: {
                                    plotOptions: {
                                        bar: {
                                            columnWidth: '48%',
                                        }
                                    },
                                },
                            },
                            {
                                breakpoint: 640,
                                options: {
                                    plotOptions: {
                                        bar: {
                                            columnWidth: '58%',
                                        }
                                    },
                                    xaxis: {
                                        labels: {
                                            rotate: -35,
                                            trim: true,
                                            style: { fontSize: '10px' },
                                        }
                                    },
                                },
                            },
                        ],
                    });

                    chart.render();
                    window.dashboardMonthlySalesChart = chart;
                };

                const scheduleMonthlySalesRerender = () => {
                    if (monthlySalesResizeFrame !== null) {
                        window.cancelAnimationFrame(monthlySalesResizeFrame);
                    }

                    monthlySalesResizeFrame = window.requestAnimationFrame(() => {
                        monthlySalesResizeFrame = null;
                        renderMonthlySalesChart(
                            currentMonthlySaleData.labels || [],
                            currentMonthlySaleData.orders || []
                        );
                    });
                };

                const bindMonthlySalesResponsiveResize = () => {
                    window.addEventListener('resize', scheduleMonthlySalesRerender);
                    window.addEventListener('app:sidebar-changed', () => {
                        window.setTimeout(scheduleMonthlySalesRerender, 320);
                    });
                };

                const renderStatsChart = (statsPayload) => {
                    const el = document.querySelector('#chartThree');
                    if (!el) return;

                    if (window.dashboardStatsChart) {
                        window.dashboardStatsChart.destroy();
                    }

                    const selectedTab = document.querySelector('[data-stats-tab].dashboard-stats-active')?.getAttribute('data-stats-tab') || 'overview';

                    const chart = new window.ApexCharts(el, {
                        chart: {
                            type: 'area',
                            height: 280,
                            toolbar: { show: false },
                            foreColor: '#667085',
                            dropShadow: {
                                enabled: true,
                                top: 4,
                                left: 0,
                                blur: 3,
                                opacity: 0.08
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
                            padding: { top: 0, right: 8, bottom: 0, left: 4 },
                            row: {
                                opacity: 0.02
                            }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 0.7,
                                opacityFrom: 0.22,
                                opacityTo: 0,
                                stops: [0, 90, 100]
                            }
                        },
                        markers: {
                            size: 0,
                            hover: { size: 4 }
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
                            categories: statsPayload.labels,
                            labels: { style: { fontSize: '10px', colors: '#98A2B3' } },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: { style: { fontSize: '10px', colors: '#98A2B3' } }
                        },
                        colors: ['#7C3AED', '#16A34A', '#DC2626'],
                        series: getStatsSeries(selectedTab, statsPayload),
                        legend: {
                            show: getStatsSeries(selectedTab, statsPayload).length > 1,
                            position: 'top',
                            horizontalAlign: 'left',
                            fontSize: '11px',
                            markers: { radius: 12 }
                        }
                    });

                    chart.render();
                    window.dashboardStatsChart = chart;
                };

                const setActiveStatsTab = (mode) => {
                    const tabs = document.querySelectorAll('[data-stats-tab]');
                    tabs.forEach((btn) => {
                        const isActive = (btn.getAttribute('data-stats-tab') || 'overview') === mode;
                        btn.classList.toggle('dashboard-stats-active', isActive);
                    });
                };

                const syncDashboardWidgets = (payload) => {
                    if (payload.monthlySale) {
                        renderMonthlySalesChart(payload.monthlySale.labels || [], payload.monthlySale.orders || []);
                    }
                };

                const fetchDashboardData = async (section) => {
                    const salesRangeSelect = document.querySelector('[data-dashboard-sales-range]');
                    const targetMonthSelect = document.querySelector('[data-dashboard-target-month]');
                    if (!salesRangeSelect || !targetMonthSelect) return;

                    const params = new URLSearchParams({
                        ajax: '1',
                        sales_range: salesRangeSelect.value,
                        target_month: targetMonthSelect.value,
                    });

                    if (section) {
                        params.set('section', section);
                    }

                    const url = `${dashboardRoot.dataset.dashboardUrl}?${params.toString()}`;
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Dashboard request failed');
                    }

                    const payload = await response.json();
                    syncDashboardWidgets(payload);

                    const nextUrl = new URL(window.location.href);
                    nextUrl.searchParams.set('sales_range', salesRangeSelect.value);
                    nextUrl.searchParams.set('target_month', targetMonthSelect.value);
                    window.history.replaceState({}, '', nextUrl.toString());
                };

                renderMonthlySalesChart(@json($monthLabels), @json($monthlyOrders));
                renderStatsChart(initialStats);
                setActiveStatsTab('overview');
                bindMonthlySalesResponsiveResize();

                const salesRangeSelect = document.querySelector('[data-dashboard-sales-range]');
                const targetMonthSelect = document.querySelector('[data-dashboard-target-month]');

                salesRangeSelect?.addEventListener('change', () => {
                    fetchDashboardData('sales').catch(() => {
                        window.location.href = `${dashboardRoot.dataset.dashboardUrl}?sales_range=${encodeURIComponent(salesRangeSelect.value)}&target_month=${encodeURIComponent(targetMonthSelect?.value || '')}`;
                    });
                });

                targetMonthSelect?.addEventListener('change', () => {
                    window.location.href = `${dashboardRoot.dataset.dashboardUrl}?sales_range=${encodeURIComponent(salesRangeSelect?.value || 12)}&target_month=${encodeURIComponent(targetMonthSelect.value)}`;
                });

                const tabs = document.querySelectorAll('[data-stats-tab]');
                tabs.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const mode = btn.getAttribute('data-stats-tab') || 'overview';
                        setActiveStatsTab(mode);
                        window.dashboardStatsChart?.updateOptions({
                            series: getStatsSeries(mode, initialStats),
                            legend: {
                                show: getStatsSeries(mode, initialStats).length > 1,
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
