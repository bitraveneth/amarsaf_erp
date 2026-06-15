export function initDashboardCharts() {
    const dashboardRoot = document.querySelector('[data-dashboard-ajax]');
    if (!dashboardRoot || !window.ApexCharts) {
        return;
    }

    let config;
    try {
        config = JSON.parse(dashboardRoot.dataset.dashboardCharts || '{}');
    } catch {
        return;
    }

    if (!config.monthLabels || !config.monthlyOrders) {
        return;
    }

    const currencyCode = dashboardRoot.dataset.currencyCode || config.currencyCode || 'BDT';
    let currentMonthlySaleData = {
        labels: config.monthLabels,
        orders: config.monthlyOrders,
    };
    const MONTHLY_SALES_CHART_HEIGHT = 220;
    let monthlySalesResizeFrame = null;
    const initialStats = {
        labels: config.statsLabels || [],
        orders: config.statsOrders || [],
        production: config.statsProduction || [],
        revenue: config.statsRevenue || [],
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
        if (!el) {
            return;
        }

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
            series: [{ name: 'Sales (orders)', data: orders }],
            colors: ['#7C3AED'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '42%',
                    borderRadius: 6,
                    borderRadiusApplication: 'end',
                },
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 3, colors: ['transparent'] },
            grid: {
                borderColor: '#E4E7EC',
                strokeDashArray: 4,
                padding: { top: 0, left: 4, right: 8, bottom: 0 },
                yaxis: { lines: { show: true } },
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
                y: { formatter: (val) => val },
            },
            fill: { opacity: 0.95 },
            legend: { show: false },
            responsive: [
                {
                    breakpoint: 1024,
                    options: { plotOptions: { bar: { columnWidth: '48%' } } },
                },
                {
                    breakpoint: 640,
                    options: {
                        plotOptions: { bar: { columnWidth: '58%' } },
                        xaxis: {
                            labels: {
                                rotate: -35,
                                trim: true,
                                style: { fontSize: '10px' },
                            },
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
        if (!el) {
            return;
        }

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
                dropShadow: { enabled: true, top: 4, left: 0, blur: 3, opacity: 0.08 },
            },
            stroke: { curve: 'smooth', width: 2.5 },
            dataLabels: { enabled: false },
            grid: {
                borderColor: '#E4E7EC',
                strokeDashArray: 4,
                padding: { top: 0, right: 8, bottom: 0, left: 4 },
                row: { opacity: 0.02 },
            },
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 0.7, opacityFrom: 0.22, opacityTo: 0, stops: [0, 90, 100] },
            },
            markers: { size: 0, hover: { size: 4 } },
            tooltip: {
                shared: true,
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                y: {
                    formatter: (val, opts) => {
                        const seriesName = opts.series[opts.seriesIndex]?.name || '';
                        const currencyPrefix = seriesName.includes('Revenue') ? `${currencyCode} ` : '';
                        return `${currencyPrefix}${val}`;
                    },
                },
            },
            xaxis: {
                categories: statsPayload.labels,
                labels: { style: { fontSize: '10px', colors: '#98A2B3' } },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: { labels: { style: { fontSize: '10px', colors: '#98A2B3' } } },
            colors: ['#7C3AED', '#16A34A', '#DC2626'],
            series: getStatsSeries(selectedTab, statsPayload),
            legend: {
                show: getStatsSeries(selectedTab, statsPayload).length > 1,
                position: 'top',
                horizontalAlign: 'left',
                fontSize: '11px',
                markers: { radius: 12 },
            },
        });

        chart.render();
        window.dashboardStatsChart = chart;
    };

    const setActiveStatsTab = (mode) => {
        document.querySelectorAll('[data-stats-tab]').forEach((btn) => {
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
        if (!salesRangeSelect || !targetMonthSelect) {
            return;
        }

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
                Accept: 'application/json',
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

    renderMonthlySalesChart(config.monthLabels, config.monthlyOrders);
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

    document.querySelectorAll('[data-stats-tab]').forEach((btn) => {
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
}
