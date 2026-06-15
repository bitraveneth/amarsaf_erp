@props([
    'topAgents' => [],
    'topProducts' => [],
    'currencyCode' => 'BDT',
])

<section {{ $attributes->merge(['class' => 'dash-insights-section']) }}>
    <x-dashboard.section-header
        title="Top performers"
        description="Leading agents and products for the current month."
        class="mb-5"
    />

    <div class="dash-activity-grid dash-activity-grid--pair">
        <div class="dash-performance-widget h-full">
            <div class="dash-performance-widget-header">
                <div>
                    <h3 class="dash-performance-widget-title">Top agents</h3>
                    <p class="dash-performance-widget-desc !mt-0">Net invoiced sales this month</p>
                </div>
                <a href="{{ route('admin.reports.agents') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Report</a>
            </div>
            <div class="dash-performance-widget-body">
                <x-dashboard.rank-list
                    title=""
                    :currency="$currencyCode"
                    :items="$topAgents"
                    empty="No agent sales recorded this month."
                />
            </div>
        </div>

        <div class="dash-performance-widget h-full">
            <div class="dash-performance-widget-header">
                <div>
                    <h3 class="dash-performance-widget-title">Top products</h3>
                    <p class="dash-performance-widget-desc !mt-0">Best sellers by net revenue</p>
                </div>
                <a href="{{ route('admin.reports.sales-register') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Register</a>
            </div>
            <div class="dash-performance-widget-body">
                <x-dashboard.rank-list
                    title=""
                    :currency="$currencyCode"
                    :items="$topProducts"
                    empty="No product sales recorded this month."
                />
            </div>
        </div>
    </div>
</section>
