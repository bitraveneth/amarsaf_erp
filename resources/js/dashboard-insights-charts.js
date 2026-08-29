const CHART_HEIGHT = 280;
const DONUT_CHART_HEIGHT = 220;
const WIDE_CHART_HEIGHT = 260;

function cssVar(name, fallback) {
    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return value || fallback;
}

/**
 * Saf ERP chart palette — distinct hues from design tokens so mix slices
 * stay readable for super admin and every other dashboard role.
 * Reads live CSS variables (Admin → Settings → Colors).
 */
function erpChartPalette() {
    return [
        cssVar('--color-brand-500', '#5f4bff'),
        cssVar('--color-success-500', '#22b573'),
        cssVar('--color-blue-light-500', '#4b7fff'),
        cssVar('--color-warning-500', '#ff8a24'),
        cssVar('--color-brand-700', '#3f2ec4'),
        cssVar('--color-success-400', '#3ecf8e'),
        cssVar('--color-blue-light-300', '#8fb8ff'),
        cssVar('--color-orange-400', '#fd853a'),
    ];
}

function chartTheme() {
    const isDark = document.documentElement.classList.contains('dark');

    return {
        isDark,
        foreColor: isDark ? cssVar('--color-gray-400', '#94a3b8') : cssVar('--color-gray-500', '#64748b'),
        labelColor: isDark ? cssVar('--color-app-text-dark', '#f3f4f6') : cssVar('--color-gray-700', '#334155'),
        totalColor: isDark ? cssVar('--color-app-text-dark', '#f3f4f6') : cssVar('--color-app-text-light', '#0e0f14'),
        gridColor: isDark ? cssVar('--color-gray-800', '#1e293b') : cssVar('--color-gray-100', '#eceef2'),
        tooltipTheme: isDark ? 'dark' : 'light',
        brand: cssVar('--color-brand-500', '#5f4bff'),
        success: cssVar('--color-success-500', '#22b573'),
        error: cssVar('--color-error-500', '#ef4444'),
        info: cssVar('--color-blue-light-500', '#4b7fff'),
        warning: cssVar('--color-warning-500', '#ff8a24'),
    };
}

function baseChartOptions(type, height) {
    const theme = chartTheme();

    return {
        chart: {
            type,
            height,
            toolbar: { show: false },
            foreColor: theme.foreColor,
            fontFamily: window.erpUiFontStack,
        },
        grid: {
            borderColor: theme.gridColor,
            strokeDashArray: 0,
            padding: { top: 4, right: 8, bottom: 0, left: 4 },
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
        },
        tooltip: {
            theme: theme.tooltipTheme,
            x: { show: false },
        },
        noData: {
            text: 'No data for this period yet',
            align: 'center',
            verticalAlign: 'middle',
            style: {
                color: theme.foreColor,
                fontSize: '13px',
                fontFamily: window.erpUiFontStack,
            },
        },
        dataLabels: { enabled: false },
    };
}

function destroyInsightCharts() {
    (window.dashboardInsightCharts || []).forEach((chart) => chart?.destroy?.());
    window.dashboardInsightCharts = [];
}

function pushChart(chart) {
    window.dashboardInsightCharts = window.dashboardInsightCharts || [];
    window.dashboardInsightCharts.push(chart);
}

function formatCurrency(value, currencyCode) {
    const amount = Number(value || 0);
    return `${currencyCode} ${amount.toLocaleString(undefined, { maximumFractionDigits: 0 })}`;
}

/** Short centre label so long BDT totals do not touch the donut ring. */
function formatCompactAmount(value) {
    const amount = Number(value || 0);
    const abs = Math.abs(amount);

    if (abs >= 10_000_000) {
        return `${(amount / 10_000_000).toFixed(1)} Cr`;
    }

    if (abs >= 100_000) {
        return `${(amount / 100_000).toFixed(1)} L`;
    }

    if (abs >= 1000) {
        return `${(amount / 1000).toFixed(1)}K`;
    }

    return amount.toLocaleString(undefined, { maximumFractionDigits: 0 });
}

function compactCenterFontSize(display) {
    const length = String(display).replace(/\s/g, '').length;

    if (length <= 4) {
        return '20px';
    }

    if (length <= 7) {
        return '18px';
    }

    return '16px';
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function renderMixLegend(legendEl, labels, values, colors, currencyCode) {
    if (!legendEl) {
        return;
    }

    const total = values.reduce((sum, value) => sum + Number(value || 0), 0);
    const hasData = values.some((value) => Number(value) > 0);

    if (!hasData) {
        legendEl.innerHTML = '';
        legendEl.hidden = true;
        return;
    }

    legendEl.hidden = false;
    legendEl.innerHTML = labels
        .map((label, index) => {
            const amount = Number(values[index] || 0);
            const percent = total > 0 ? Math.round((amount / total) * 100) : 0;
            const color = colors[index % colors.length];

            return `<li class="dash-analytics-legend__row">
                <span class="dash-analytics-legend__swatch" style="background:${color}"></span>
                <span class="dash-analytics-legend__name" title="${escapeHtml(label)}">${escapeHtml(label)}</span>
                <span class="dash-analytics-legend__meta">
                    <span class="dash-analytics-legend__amount" title="${escapeHtml(formatCurrency(amount, currencyCode))}">${escapeHtml(formatCompactAmount(amount))}</span>
                    <span class="dash-analytics-legend__share">${percent}%</span>
                </span>
            </li>`;
        })
        .join('');
}

function renderDonut(el, labels, values, currencyCode, legendEl) {
    if (!el || !window.ApexCharts) {
        return;
    }

    const theme = chartTheme();
    const colors = erpChartPalette();
    const hasData = values.some((value) => Number(value) > 0);
    const total = values.reduce((sum, value) => sum + Number(value || 0), 0);
    const centerTotal = formatCompactAmount(total);
    const sliceGap = theme.isDark
        ? cssVar('--color-gray-900', '#151821')
        : cssVar('--color-white', '#ffffff');

    const chart = new window.ApexCharts(el, {
        ...baseChartOptions('donut', DONUT_CHART_HEIGHT),
        series: hasData ? values : [],
        labels: hasData ? labels : [],
        colors,
        stroke: {
            show: true,
            width: 3,
            colors: [sliceGap],
        },
        legend: { show: false },
        plotOptions: {
            pie: {
                expandOnClick: false,
                donut: {
                    size: '78%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '11px',
                            fontWeight: 600,
                            color: theme.foreColor,
                            offsetY: 18,
                        },
                        value: {
                            show: true,
                            fontSize: compactCenterFontSize(centerTotal),
                            fontWeight: 700,
                            color: theme.totalColor,
                            offsetY: -6,
                            formatter: (val) => formatCompactAmount(val),
                        },
                        total: {
                            show: true,
                            label: `${currencyCode} total`,
                            fontSize: '11px',
                            fontWeight: 600,
                            color: theme.foreColor,
                            formatter: () => centerTotal,
                        },
                    },
                },
            },
        },
        tooltip: {
            theme: theme.tooltipTheme,
            y: {
                formatter: (val) => formatCurrency(val, currencyCode),
            },
        },
    });

    chart.render();
    pushChart(chart);
    renderMixLegend(legendEl, labels, values, colors, currencyCode);
}

function renderCashBar(el, labels, values, currencyCode) {
    if (!el || !window.ApexCharts) {
        return;
    }

    const theme = chartTheme();

    const chart = new window.ApexCharts(el, {
        ...baseChartOptions('bar', CHART_HEIGHT),
        series: [{ name: 'Amount', data: values }],
        colors: [theme.brand, theme.success, theme.info, theme.warning],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '39%',
                borderRadius: 5,
                borderRadiusApplication: 'end',
                distributed: true,
            },
        },
        stroke: {
            show: true,
            width: 4,
            colors: ['transparent'],
        },
        fill: { opacity: 1 },
        xaxis: {
            categories: labels,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                trim: true,
                style: { fontSize: '12px', colors: theme.foreColor, fontFamily: window.erpUiFontStack },
            },
        },
        yaxis: {
            title: { text: undefined },
            labels: {
                style: { fontSize: '12px', colors: theme.foreColor, fontFamily: window.erpUiFontStack },
                formatter: (val) => formatCurrency(val, currencyCode),
            },
        },
        legend: { show: false },
        tooltip: {
            theme: theme.tooltipTheme,
            y: {
                formatter: (val) => formatCurrency(val, currencyCode),
            },
        },
    });

    chart.render();
    pushChart(chart);
}

function renderMomentumBar(el, labels, values) {
    if (!el || !window.ApexCharts) {
        return;
    }

    const theme = chartTheme();
    const colors = values.map((value) => (Number(value) >= 0 ? theme.success : theme.error));

    const chart = new window.ApexCharts(el, {
        ...baseChartOptions('bar', CHART_HEIGHT),
        series: [{ name: 'Change', data: values }],
        colors,
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '39%',
                borderRadius: 5,
                borderRadiusApplication: 'end',
                distributed: true,
            },
        },
        stroke: {
            show: true,
            width: 4,
            colors: ['transparent'],
        },
        fill: { opacity: 1 },
        xaxis: {
            categories: labels,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: { fontSize: '12px', colors: theme.foreColor, fontFamily: window.erpUiFontStack },
            },
        },
        yaxis: {
            title: { text: undefined },
            labels: {
                style: { fontSize: '12px', colors: theme.foreColor, fontFamily: window.erpUiFontStack },
                formatter: (val) => `${val}%`,
            },
        },
        legend: { show: false },
        tooltip: {
            theme: theme.tooltipTheme,
            y: {
                formatter: (val) => `${val}% vs last month`,
            },
        },
    });

    chart.render();
    pushChart(chart);
}

function renderOperationsBar(el, labels, values) {
    if (!el || !window.ApexCharts) {
        return;
    }

    const theme = chartTheme();

    const chart = new window.ApexCharts(el, {
        ...baseChartOptions('bar', WIDE_CHART_HEIGHT),
        chart: {
            ...baseChartOptions('bar', WIDE_CHART_HEIGHT).chart,
            type: 'bar',
        },
        series: [{ name: 'Open items', data: values }],
        colors: [theme.brand],
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 5,
                borderRadiusApplication: 'end',
                barHeight: '55%',
            },
        },
        stroke: {
            show: true,
            width: 4,
            colors: ['transparent'],
        },
        fill: { opacity: 1 },
        xaxis: {
            categories: labels,
            labels: {
                style: { fontSize: '12px', colors: theme.foreColor, fontFamily: window.erpUiFontStack },
            },
        },
        yaxis: {
            labels: {
                style: { fontSize: '12px', colors: theme.foreColor, fontFamily: window.erpUiFontStack },
            },
        },
        grid: {
            ...baseChartOptions('bar', WIDE_CHART_HEIGHT).grid,
            padding: { top: 0, right: 12, bottom: 0, left: 4 },
        },
        legend: { show: false },
        tooltip: {
            theme: theme.tooltipTheme,
            y: {
                formatter: (val) => `${val} open`,
            },
        },
    });

    chart.render();
    pushChart(chart);
}

export function initDashboardInsightsCharts() {
    const root = document.querySelector('[data-dashboard-insights-charts]');
    if (!root || !window.ApexCharts) {
        return;
    }

    let config;
    try {
        config = JSON.parse(root.dataset.dashboardInsightsCharts || '{}');
    } catch {
        return;
    }

    destroyInsightCharts();

    const currencyCode = config.currencyCode || 'BDT';

    renderDonut(
        document.querySelector('#insights-chart-product-mix'),
        config.productMix?.labels || [],
        config.productMix?.values || [],
        currencyCode,
        document.querySelector('#insights-legend-product-mix'),
    );

    renderDonut(
        document.querySelector('#insights-chart-agent-mix'),
        config.agentMix?.labels || [],
        config.agentMix?.values || [],
        currencyCode,
        document.querySelector('#insights-legend-agent-mix'),
    );

    renderCashBar(
        document.querySelector('#insights-chart-cash'),
        config.cashSnapshot?.labels || [],
        config.cashSnapshot?.values || [],
        currencyCode,
    );

    renderMomentumBar(
        document.querySelector('#insights-chart-momentum'),
        config.momentum?.labels || [],
        config.momentum?.values || [],
    );

    renderOperationsBar(
        document.querySelector('#insights-chart-operations'),
        config.operations?.labels || [],
        config.operations?.values || [],
    );

    const rerender = () => {
        window.setTimeout(() => initDashboardInsightsCharts(), 80);
    };

    if (!window.dashboardInsightsChartsBound) {
        window.dashboardInsightsChartsBound = true;
        window.addEventListener('app:sidebar-changed', rerender);
    }
}
