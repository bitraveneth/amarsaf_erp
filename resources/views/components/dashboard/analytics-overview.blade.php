@props([
    'charts' => [],
    'currencyCode' => 'BDT',
])

<section
    {{ $attributes->merge(['class' => 'dash-insights-section dash-analytics-overview']) }}
    data-dashboard-insights-charts="{{ json_encode(array_merge($charts, ['currencyCode' => $currencyCode])) }}"
>
    <x-dashboard.section-header
        title="Analytics overview"
        description="Visual breakdown of sales mix, cash position, momentum, and operational load."
        class="mb-4"
    />

    <div class="dash-analytics-grid">
        <div class="dash-analytics-card">
            <div class="dash-analytics-card__head">
                <h3 class="dash-analytics-card__title erp-h3">Revenue by product</h3>
                <p class="dash-analytics-card__desc erp-caption">Share of net sales this month</p>
            </div>
            <div class="dash-analytics-card__body dash-analytics-mix">
                <div id="insights-chart-product-mix" class="dash-analytics-chart dash-analytics-chart--donut"></div>
                <ul id="insights-legend-product-mix" class="dash-analytics-legend" hidden></ul>
            </div>
        </div>

        <div class="dash-analytics-card">
            <div class="dash-analytics-card__head">
                <h3 class="dash-analytics-card__title erp-h3">Revenue by agent</h3>
                <p class="dash-analytics-card__desc erp-caption">Top partner contribution</p>
            </div>
            <div class="dash-analytics-card__body dash-analytics-mix">
                <div id="insights-chart-agent-mix" class="dash-analytics-chart dash-analytics-chart--donut"></div>
                <ul id="insights-legend-agent-mix" class="dash-analytics-legend" hidden></ul>
            </div>
        </div>

        <div class="dash-analytics-card">
            <div class="dash-analytics-card__head">
                <h3 class="dash-analytics-card__title erp-h3">Cash snapshot</h3>
                <p class="dash-analytics-card__desc erp-caption">Invoiced, collected, and open balances</p>
            </div>
            <div class="dash-analytics-card__body">
                <div id="insights-chart-cash" class="dash-analytics-chart"></div>
            </div>
        </div>

        <div class="dash-analytics-card">
            <div class="dash-analytics-card__head">
                <h3 class="dash-analytics-card__title erp-h3">Month momentum</h3>
                <p class="dash-analytics-card__desc erp-caption">Percent change vs {{ $charts['previousMonthLabel'] ?? 'last month' }}</p>
            </div>
            <div class="dash-analytics-card__body">
                <div id="insights-chart-momentum" class="dash-analytics-chart"></div>
            </div>
        </div>

        <div class="dash-analytics-card dash-analytics-card--wide">
            <div class="dash-analytics-card__head">
                <h3 class="dash-analytics-card__title erp-h3">Operations load</h3>
                <p class="dash-analytics-card__desc erp-caption">QC, stock, batches, and delivery pipeline</p>
            </div>
            <div class="dash-analytics-card__body">
                <div id="insights-chart-operations" class="dash-analytics-chart dash-analytics-chart--wide"></div>
            </div>
        </div>
    </div>
</section>
