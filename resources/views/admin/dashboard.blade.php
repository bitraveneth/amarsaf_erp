@extends('layouts.app')

@section('content')
  <div
    class="dash-page"
    data-dashboard-ajax
    data-dashboard-url="{{ route('admin.dashboard') }}"
    data-currency-code="{{ $currencyCode ?? config('app.currency', 'BDT') }}"
    @if(isset($chartDays) && $chartDays instanceof \Illuminate\Support\Collection && $chartDays->isNotEmpty())
    data-dashboard-charts="{{ json_encode([
        'currencyCode' => $currencyCode ?? config('app.currency', 'BDT'),
        'monthLabels' => $monthLabels ?? [],
        'monthlyOrders' => $monthlyOrders ?? [],
        'statsLabels' => $chartDays->pluck('label')->values()->all(),
        'statsOrders' => $chartDays->pluck('orders')->values()->all(),
        'statsProduction' => $chartDays->pluck('production')->values()->all(),
        'statsRevenue' => $chartDays->pluck('revenue')->values()->all(),
    ]) }}"
    @endif
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

    <x-dashboard.procurement-inbox />

    <x-dashboard.fulfillment-inbox />

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
                :progress-percent-raw="$monthlyTargetProgressRaw ?? 0"
                :today-change-percent="$todayChangePercent ?? null"
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
