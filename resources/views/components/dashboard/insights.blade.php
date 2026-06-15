@props([
    'insights' => [],
    'currencyCode' => 'BDT',
])

<div class="dash-insights">
    <x-dashboard.analytics-overview
        :charts="$insights['analyticsCharts'] ?? []"
        :currency-code="$currencyCode"
    />

    <x-dashboard.month-comparison
        :items="$insights['monthComparison'] ?? []"
        :currency-code="$currencyCode"
    />

    <x-dashboard.top-performers
        :top-agents="$insights['topAgents'] ?? []"
        :top-products="$insights['topProducts'] ?? []"
        :currency-code="$currencyCode"
    />

    <x-dashboard.supply-chain-snapshot
        :tiles="$insights['supplyChain'] ?? []"
    />

    <div class="dash-insights-split">
        <x-dashboard.treasury-pulse :treasury="$insights['treasury'] ?? []" />
        <x-dashboard.delivery-logistics :delivery="$insights['deliveryLogistics'] ?? []" />
    </div>

    <x-dashboard.commission-watch :commission="$insights['commissionWatch'] ?? []" />

    <x-dashboard.hr-glance :hr="$insights['hrGlance'] ?? []" />

    <x-dashboard.inventory-feed :movements="$insights['inventoryFeed'] ?? []" />

    <x-dashboard.report-shortcuts :reports="$insights['reportShortcuts'] ?? []" />

    <x-dashboard.erp-cycle />
</div>
