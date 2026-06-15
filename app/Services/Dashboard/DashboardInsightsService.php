<?php

namespace App\Services\Dashboard;

use App\Models\AgentCommissionSettlement;
use App\Models\Batch;
use App\Models\Delivery;
use App\Models\EmployeeLeave;
use App\Models\EmployeeOvertime;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\PurchaseBill;
use App\Models\SalaryDistribution;
use App\Models\SalesTarget;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Services\Accounting\OperationalReportService;
use App\Support\DashboardChartBuilder;
use App\Support\InvoiceRevenueMetrics;
use App\Support\ReportsCatalog;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DashboardInsightsService
{
    /**
     * @return array<string, mixed>
     */
    public function build(CarbonInterface $today, string $currencyCode): array
    {
        $currentMonthStart = $today->copy()->startOfMonth();
        $currentMonthEnd = $today->copy()->endOfDay();
        $prevMonthStart = $currentMonthStart->copy()->subMonth()->startOfMonth();
        $prevMonthEnd = $currentMonthStart->copy()->subDay()->endOfDay();

        $topAgents = $this->topAgents($currentMonthStart, $currentMonthEnd);
        $topProducts = $this->topProducts($currentMonthStart, $currentMonthEnd);
        $monthComparison = $this->monthComparison($today, $currentMonthStart, $currentMonthEnd, $prevMonthStart, $prevMonthEnd);
        $supplyChain = $this->supplyChain($today);
        $treasury = $this->treasury($today, $currentMonthStart, $currentMonthEnd, $currencyCode);
        $deliveryLogistics = $this->deliveryLogistics($today);

        return [
            'monthComparison' => $monthComparison,
            'topAgents' => $topAgents,
            'topProducts' => $topProducts,
            'supplyChain' => $supplyChain,
            'treasury' => $treasury,
            'deliveryLogistics' => $deliveryLogistics,
            'commissionWatch' => $this->commissionWatch($currentMonthStart, $currentMonthEnd),
            'hrGlance' => $this->hrGlance($today, $currentMonthStart, $currentMonthEnd),
            'inventoryFeed' => $this->inventoryFeed(),
            'reportShortcuts' => $this->reportShortcuts(),
            'analyticsCharts' => $this->analyticsCharts(
                $monthComparison,
                $topAgents,
                $topProducts,
                $treasury,
                $supplyChain,
                $deliveryLogistics,
                $currentMonthStart,
                $prevMonthStart,
            ),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $monthComparison
     * @param  array<int, array<string, mixed>>  $topAgents
     * @param  array<int, array<string, mixed>>  $topProducts
     * @param  array<string, mixed>  $treasury
     * @param  array<int, array<string, mixed>>  $supplyChain
     * @param  array<string, mixed>  $deliveryLogistics
     * @return array<string, mixed>
     */
    protected function analyticsCharts(
        array $monthComparison,
        array $topAgents,
        array $topProducts,
        array $treasury,
        array $supplyChain,
        array $deliveryLogistics,
        Carbon $currentMonthStart,
        Carbon $prevMonthStart,
    ): array {
        $productMix = $this->mixChartFromRankList($topProducts);
        $agentMix = $this->mixChartFromRankList($topAgents);

        $momentumLabels = [];
        $momentumValues = [];
        foreach ($monthComparison as $row) {
            $momentumLabels[] = $row['label'] ?? 'Metric';
            $momentumValues[] = (float) ($row['delta'] ?? 0);
        }

        $supplyMap = collect($supplyChain)->keyBy('label');

        $charts = [
            'currentMonthLabel' => $currentMonthStart->format('M Y'),
            'previousMonthLabel' => $prevMonthStart->format('M Y'),
            'productMix' => $productMix,
            'agentMix' => $agentMix,
            'momentum' => [
                'labels' => $momentumLabels,
                'values' => $momentumValues,
            ],
            'cashSnapshot' => [
                'labels' => ['Invoiced MTD', 'Collected MTD', 'Outstanding AR', 'Outstanding AP'],
                'values' => [
                    round((float) ($treasury['invoiced'] ?? 0), 2),
                    round((float) ($treasury['collections'] ?? 0), 2),
                    round((float) ($treasury['outstanding_ar'] ?? 0), 2),
                    round((float) ($treasury['outstanding_ap'] ?? 0), 2),
                ],
            ],
            'operations' => [
                'labels' => ['Open QC', 'Low stock SKUs', 'Expiring batches', 'In transit', 'Orders due week'],
                'values' => [
                    (int) str_replace(',', '', (string) ($supplyMap->get('Open QC')['value'] ?? 0)),
                    (int) str_replace(',', '', (string) ($supplyMap->get('Low stock SKUs')['value'] ?? 0)),
                    (int) str_replace(',', '', (string) ($supplyMap->get('Expiring batches')['value'] ?? 0)),
                    (int) ($deliveryLogistics['in_transit'] ?? 0),
                    (int) ($deliveryLogistics['orders_due_week'] ?? 0),
                ],
            ],
        ];

        return $charts;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    protected function mixChartFromRankList(array $items): array
    {
        if ($items === []) {
            return ['labels' => [], 'values' => []];
        }

        $labels = [];
        $values = [];

        foreach ($items as $item) {
            $labels[] = (string) ($item['label'] ?? 'Unknown');
            $values[] = round((float) ($item['value'] ?? 0), 2);
        }

        return compact('labels', 'values');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function monthComparison(
        CarbonInterface $today,
        Carbon $currentMonthStart,
        Carbon $currentMonthEnd,
        Carbon $prevMonthStart,
        Carbon $prevMonthEnd,
    ): array {
        $metrics = [];

        if (Schema::hasTable('invoices')) {
            $currentRevenue = InvoiceRevenueMetrics::sumNetSalesAfterCreditsInRange(
                $currentMonthStart,
                $currentMonthEnd,
                $currentMonthStart,
                $currentMonthEnd
            );
            $prevRevenue = InvoiceRevenueMetrics::sumNetSalesAfterCreditsInRange(
                $prevMonthStart,
                $prevMonthEnd,
                $prevMonthStart,
                $prevMonthEnd
            );

            $metrics[] = $this->comparisonMetric('Revenue', $currentRevenue, $prevRevenue, true);
        }

        if (Schema::hasTable('orders')) {
            $currentOrders = Order::query()
                ->where('order_type', '!=', 'return')
                ->whereYear('delivery_date', $currentMonthStart->year)
                ->whereMonth('delivery_date', $currentMonthStart->month)
                ->whereDate('delivery_date', '<=', $currentMonthEnd->toDateString())
                ->count();

            $prevOrders = Order::query()
                ->where('order_type', '!=', 'return')
                ->whereYear('delivery_date', $prevMonthStart->year)
                ->whereMonth('delivery_date', $prevMonthStart->month)
                ->count();

            $metrics[] = $this->comparisonMetric('Sales orders', $currentOrders, $prevOrders, false);
        }

        if (Schema::hasTable('production_runs')) {
            $currentProduction = (float) ProductionRun::query()
                ->where('qc_status', 'approved')
                ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('quantity');

            $prevProduction = (float) ProductionRun::query()
                ->where('qc_status', 'approved')
                ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
                ->sum('quantity');

            $metrics[] = $this->comparisonMetric('Production qty', $currentProduction, $prevProduction, false);
        }

        return $metrics;
    }

    /**
     * @return array<string, mixed>
     */
    protected function comparisonMetric(string $label, float|int $current, float|int $previous, bool $isCurrency): array
    {
        $delta = $previous > 0
            ? round((($current - $previous) / $previous) * 100, 1)
            : ($current > 0 ? 100.0 : 0.0);

        return [
            'label' => $label,
            'current' => $current,
            'previous' => $previous,
            'delta' => $delta,
            'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat'),
            'is_currency' => $isCurrency,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function topAgents(Carbon $from, Carbon $to): array
    {
        if (! Schema::hasTable('invoices')) {
            return [];
        }

        $invoices = Invoice::query()
            ->with(['order.agent'])
            ->whereBetween('issued_at', [$from, $to])
            ->get();

        return DashboardChartBuilder::normalizeRankList(
            $invoices
                ->filter(fn (Invoice $invoice) => $invoice->order?->agent)
                ->groupBy(fn (Invoice $invoice) => (string) $invoice->order->agent->id)
                ->map(function (Collection $group) use ($from, $to) {
                    $agent = $group->first()->order->agent;

                    return [
                        'label' => $agent->name ?? 'Unknown agent',
                        'value' => $group->sum(fn (Invoice $invoice) => $invoice->netSalesAfterCreditsInRange($from, $to)),
                        'meta' => $agent->zone ?? null,
                        'href' => route('admin.agents.show', $agent),
                    ];
                })
                ->values()
        )->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function topProducts(Carbon $from, Carbon $to): array
    {
        if (! Schema::hasTable('invoices')) {
            return [];
        }

        $invoices = Invoice::query()
            ->with(['items.product'])
            ->whereBetween('issued_at', [$from, $to])
            ->get();

        return DashboardChartBuilder::normalizeRankList(
            $invoices
                ->flatMap(function (Invoice $invoice) use ($from, $to) {
                    $invoiceNet = max((float) $invoice->net_total, 0.0);
                    $creditNet = min($invoice->creditNotesNetTotalInRange($from, $to), $invoiceNet);

                    return $invoice->items->map(function (InvoiceItem $item) use ($invoiceNet, $creditNet) {
                        $lineTotal = (float) $item->line_total;
                        $creditShare = $invoiceNet > 0
                            ? $creditNet * ($lineTotal / $invoiceNet)
                            : 0.0;

                        return [
                            'product_id' => $item->product_id,
                            'label' => $item->product?->name ?? 'Unknown product',
                            'qty' => (float) $item->quantity,
                            'value' => max(0.0, $lineTotal - $creditShare),
                        ];
                    });
                })
                ->groupBy(fn (array $row) => (string) ($row['product_id'] ?? 'unknown'))
                ->map(function (Collection $group) {
                    $first = $group->first();

                    return [
                        'label' => $first['label'],
                        'value' => $group->sum('value'),
                        'meta' => 'Qty ' . number_format($group->sum('qty'), 0),
                    ];
                })
                ->values()
        )->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function supplyChain(CarbonInterface $today): array
    {
        $todayProduction = 0.0;
        $openQc = 0;
        $lowStock = 0;
        $expiringBatches = 0;

        if (Schema::hasTable('production_runs')) {
            $todayProduction = (float) ProductionRun::query()
                ->where('qc_status', 'approved')
                ->whereDate('created_at', $today)
                ->sum('quantity');

            $openQc = ProductionRun::query()
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) {
                    $query->whereNull('qc_status')
                        ->orWhere('qc_status', 'pending');
                })
                ->count();
        }

        if (Schema::hasTable('stock_entries') && Schema::hasTable('products')) {
            $availableByProduct = StockEntry::query()
                ->selectRaw('product_id, SUM(quantity) as qty')
                ->where('status', 'available')
                ->groupBy('product_id')
                ->pluck('qty', 'product_id');

            $lowStock = Product::query()
                ->sellable()
                ->stockTracked()
                ->get(['id', 'reorder_level'])
                ->filter(function ($product) use ($availableByProduct) {
                    $threshold = (int) ($product->reorder_level ?? 0);
                    if ($threshold <= 0) {
                        $threshold = 10;
                    }

                    return (float) ($availableByProduct[$product->id] ?? 0) <= $threshold;
                })
                ->count();
        }

        if (Schema::hasTable('batches')) {
            $expiringBatches = Batch::query()
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [$today->toDateString(), $today->copy()->addDays(60)->toDateString()])
                ->count();
        }

        return [
            [
                'label' => 'Production today',
                'value' => number_format($todayProduction, 0),
                'caption' => 'Approved output today',
                'href' => route('admin.production.index'),
                'tone' => 'brand',
            ],
            [
                'label' => 'Open QC',
                'value' => number_format($openQc),
                'caption' => 'Runs awaiting quality check',
                'href' => route('admin.production.index'),
                'tone' => $openQc > 0 ? 'warning' : 'neutral',
            ],
            [
                'label' => 'Low stock SKUs',
                'value' => number_format($lowStock),
                'caption' => 'At or below reorder level',
                'href' => route('admin.inventory.low-stock'),
                'tone' => $lowStock > 0 ? 'error' : 'neutral',
            ],
            [
                'label' => 'Expiring batches',
                'value' => number_format($expiringBatches),
                'caption' => 'Lots expiring in 60 days',
                'href' => route('admin.batches.index'),
                'tone' => $expiringBatches > 0 ? 'warning' : 'neutral',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function treasury(CarbonInterface $today, Carbon $from, Carbon $to, string $currencyCode): array
    {
        $collections = 0.0;
        $invoiced = 0.0;
        $outstandingAr = 0.0;
        $outstandingAp = 0.0;
        $billsDueWeek = 0;

        if (Schema::hasTable('invoices')) {
            $invoiced = InvoiceRevenueMetrics::sumNetSalesAfterCreditsInRange($from, $to, $from, $to);
            $collections = InvoiceRevenueMetrics::sumReceiptsInRange($from, $to);
            $outstandingAr = InvoiceRevenueMetrics::sumOutstandingAsOf($today->copy()->endOfDay());
        }

        if (Schema::hasTable('purchase_bills')) {
            $openBills = PurchaseBill::query()
                ->with('payments')
                ->whereIn('status', ['open', 'part_paid'])
                ->get();

            $outstandingAp = round((float) $openBills->sum(fn (PurchaseBill $bill) => $bill->outstanding), 2);

            $weekEnd = $today->copy()->addDays(7)->endOfDay();
            $billsDueWeek = $openBills
                ->filter(fn (PurchaseBill $bill) => $bill->due_date && $bill->due_date->between($today, $weekEnd))
                ->count();
        }

        $collectionRate = $invoiced > 0 ? round(min(100, ($collections / $invoiced) * 100), 1) : 0.0;
        $netPosition = round($outstandingAr - $outstandingAp, 2);

        return [
            'currency_code' => $currencyCode,
            'collections' => $collections,
            'invoiced' => $invoiced,
            'collection_rate' => $collectionRate,
            'outstanding_ar' => $outstandingAr,
            'outstanding_ap' => $outstandingAp,
            'bills_due_week' => $billsDueWeek,
            'net_position' => $netPosition,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function deliveryLogistics(CarbonInterface $today): array
    {
        $ordersDueToday = 0;
        $ordersDueWeek = 0;
        $inTransit = 0;
        $podRate = 0.0;

        if (Schema::hasTable('orders')) {
            $ordersDueToday = Order::query()
                ->where('order_type', '!=', 'return')
                ->whereDate('delivery_date', $today)
                ->count();

            $ordersDueWeek = Order::query()
                ->where('order_type', '!=', 'return')
                ->whereBetween('delivery_date', [$today->toDateString(), $today->copy()->addDays(7)->toDateString()])
                ->count();
        }

        if (Schema::hasTable('deliveries')) {
            $inTransit = Delivery::query()
                ->whereIn('status', ['scheduled', 'in_transit', 'exception'])
                ->count();

            if (class_exists(OperationalReportService::class)) {
                $from = Carbon::parse($today)->copy()->subDays(6)->startOfDay();
                $to = Carbon::parse($today)->copy()->endOfDay();
                $performance = app(OperationalReportService::class)->deliveryPerformance($from, $to);
                $podRate = (float) ($performance['totals']['pod_rate'] ?? 0);
            }
        }

        return [
            'orders_due_today' => $ordersDueToday,
            'orders_due_week' => $ordersDueWeek,
            'in_transit' => $inTransit,
            'pod_rate' => $podRate,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function commissionWatch(Carbon $from, Carbon $to): array
    {
        $belowTarget = [];
        $pendingSettlements = 0;

        if (Schema::hasTable('sales_targets') && Schema::hasTable('invoices')) {
            $targets = SalesTarget::query()
                ->with('agent')
                ->whereNotNull('agent_id')
                ->whereDate('period_start', '<=', $to->toDateString())
                ->whereDate('period_end', '>=', $from->toDateString())
                ->get();

            foreach ($targets as $target) {
                if (! $target->agent || (float) $target->target_value <= 0) {
                    continue;
                }

                $achieved = Invoice::query()
                    ->whereHas('order', fn ($q) => $q->where('agent_id', $target->agent_id))
                    ->whereBetween('issued_at', [$from, $to])
                    ->get()
                    ->sum(fn (Invoice $invoice) => $invoice->netSalesAfterCreditsInRange($from, $to));

                $progress = round(($achieved / (float) $target->target_value) * 100, 1);

                if ($progress < 50) {
                    $belowTarget[] = [
                        'label' => $target->agent->name ?? 'Agent #' . $target->agent_id,
                        'value' => $progress,
                        'meta' => number_format($progress, 1) . '% of target',
                        'href' => route('admin.agents.show', $target->agent),
                    ];
                }
            }

            usort($belowTarget, fn ($a, $b) => $a['value'] <=> $b['value']);
            $belowTarget = array_slice($belowTarget, 0, 5);
        }

        if (Schema::hasTable('agent_commission_settlements')) {
            $pendingSettlements = AgentCommissionSettlement::query()
                ->whereIn('status', ['open', 'approved', 'accrued'])
                ->count();
        }

        return [
            'below_target' => $belowTarget,
            'pending_settlements' => $pendingSettlements,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function hrGlance(CarbonInterface $today, Carbon $from, Carbon $to): array
    {
        $onLeaveToday = 0;
        $pendingOvertime = 0;
        $payrollRuns = 0;

        if (Schema::hasTable('employee_leaves')) {
            $onLeaveToday = EmployeeLeave::query()
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count();
        }

        if (Schema::hasTable('employee_overtime')) {
            $pendingOvertime = EmployeeOvertime::query()
                ->where('status', EmployeeOvertime::STATUS_PENDING)
                ->count();
        }

        if (Schema::hasTable('salary_distributions')) {
            $payrollRuns = SalaryDistribution::query()
                ->whereDate('period_start', '<=', $to->toDateString())
                ->whereDate('period_end', '>=', $from->toDateString())
                ->count();
        }

        return [
            'on_leave_today' => $onLeaveToday,
            'pending_overtime' => $pendingOvertime,
            'payroll_runs' => $payrollRuns,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function inventoryFeed(): array
    {
        if (! Schema::hasTable('stock_movements')) {
            return [];
        }

        return StockMovement::query()
            ->with(['stockEntry.product', 'order'])
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(function (StockMovement $movement) {
                $meta = StockMovement::typeMeta($movement->type);
                $product = $movement->stockEntry?->product?->name ?? 'Stock movement';

                return [
                    'label' => $product,
                    'value' => (float) $movement->quantity,
                    'meta' => ($meta['label'] ?? $movement->type) . ' · ' . $movement->created_at?->diffForHumans(),
                    'href' => route('admin.stock.movements'),
                    'type' => $movement->type,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function reportShortcuts(): array
    {
        $pinned = [
            'income-statement',
            'cash-flow',
            'ar-aging',
            'inventory-valuation',
            'production-variance',
            'agent-performance',
        ];

        return collect(ReportsCatalog::flat())
            ->filter(fn (array $report) => in_array($report['id'] ?? '', $pinned, true))
            ->sortBy(fn (array $report) => array_search($report['id'], $pinned, true))
            ->values()
            ->all();
    }
}
