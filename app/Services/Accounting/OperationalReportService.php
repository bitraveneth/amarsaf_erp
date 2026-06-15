<?php

namespace App\Services\Accounting;

use App\Models\Agent;
use App\Models\BillPayment;
use App\Models\Delivery;
use App\Models\DeliveryRoute;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FleetExpense;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\LogisticsBill;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\Receipt;
use App\Models\SalesTarget;
use App\Models\Supplier;
use App\Support\CommissionCalculator;
use App\Support\ExportDateRange;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OperationalReportService
{
    /**
     * @return array{from: Carbon, to: Carbon, rows: Collection, total: float, lineCount: int}
     */
    public function expenseSummary(Carbon $from, Carbon $to, ?string $categoryCode = null): array
    {
        $query = Expense::query()
            ->with('expenseCategory')
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->whereIn('status', [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED, 'paid', 'overdue']);

        if ($categoryCode) {
            $query->where(function ($q) use ($categoryCode) {
                $q->where('category', $categoryCode)
                    ->orWhereHas('expenseCategory', fn ($cat) => $cat->where('code', $categoryCode));
            });
        }

        $expenses = $query->get();

        $rows = $expenses
            ->groupBy(fn (Expense $expense) => $expense->expense_category_id ?: $expense->category)
            ->map(function (Collection $group) use ($from, $to) {
                $first = $group->first();
                $category = $first->expenseCategory;
                $code = $category?->code ?? (string) $first->category;

                return [
                    'code' => $code,
                    'name' => $category?->name ?? ucfirst(str_replace('_', ' ', $code)),
                    'line_count' => $group->count(),
                    'total' => round((float) $group->sum('amount'), 2),
                    'drill_url' => route('admin.expenses.index', [
                        'from' => $from->toDateString(),
                        'to' => $to->toDateString(),
                        'category' => $code,
                    ]),
                ];
            })
            ->sortByDesc('total')
            ->values();

        return [
            'from' => $from,
            'to' => $to,
            'rows' => $rows,
            'total' => round((float) $rows->sum('total'), 2),
            'lineCount' => $expenses->count(),
        ];
    }

    /**
     * @return array{from: Carbon, to: Carbon, rows: Collection, total: float, lineCount: int}
     */
    public function utilitiesReport(Carbon $from, Carbon $to): array
    {
        $summary = $this->expenseSummary($from, $to, 'utilities');

        $lines = Expense::query()
            ->with('expenseCategory')
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->where(function ($q) {
                $q->where('category', 'utilities')
                    ->orWhereHas('expenseCategory', fn ($cat) => $cat->where('code', 'utilities'));
            })
            ->whereIn('status', [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED, 'paid', 'overdue'])
            ->orderByDesc('date')
            ->get();

        return array_merge($summary, ['lines' => $lines]);
    }

    /**
     * @return array{from: Carbon, to: Carbon, rows: Collection, totals: array<string, float>}
     */
    public function logisticsBillsSummary(Carbon $from, Carbon $to): array
    {
        $bills = LogisticsBill::query()
            ->with(['transportCarrier', 'deliveryRoute'])
            ->whereDate('bill_date', '>=', $from->toDateString())
            ->whereDate('bill_date', '<=', $to->toDateString())
            ->orderByDesc('bill_date')
            ->get();

        $rows = $bills
            ->groupBy(fn (LogisticsBill $bill) => $bill->transport_carrier_id ?: 'unknown')
            ->map(function (Collection $group) {
                $first = $group->first();
                $gross = (float) $group->sum(fn (LogisticsBill $b) => (float) $b->net_total + (float) $b->vat_amount);
                $unpaid = (float) $group->sum(fn (LogisticsBill $b) => (float) $b->outstanding);

                return [
                    'carrier' => $first->transportCarrier?->name ?? 'Unknown carrier',
                    'bill_count' => $group->count(),
                    'gross_total' => round($gross, 2),
                    'unpaid_total' => round($unpaid, 2),
                    'paid_total' => round($gross - $unpaid, 2),
                ];
            })
            ->sortByDesc('gross_total')
            ->values();

        $gross = round((float) $bills->sum(fn (LogisticsBill $b) => (float) $b->net_total + (float) $b->vat_amount), 2);
        $unpaid = round((float) $bills->sum(fn (LogisticsBill $b) => (float) $b->outstanding), 2);

        return [
            'from' => $from,
            'to' => $to,
            'bills' => $bills,
            'rows' => $rows,
            'totals' => [
                'bill_count' => $bills->count(),
                'gross' => $gross,
                'unpaid' => $unpaid,
                'paid' => round($gross - $unpaid, 2),
            ],
        ];
    }

    /**
     * @return array{from: Carbon, to: Carbon, rows: Collection}
     */
    public function routeCosts(Carbon $from, Carbon $to): array
    {
        $routes = DeliveryRoute::orderBy('name')->get();

        $fleetByRoute = FleetExpense::query()
            ->whereBetween('expense_date', [$from, $to])
            ->whereNotNull('delivery_route_id')
            ->select('delivery_route_id', DB::raw('SUM(amount) as total'))
            ->groupBy('delivery_route_id')
            ->pluck('total', 'delivery_route_id');

        $carrierByRoute = LogisticsBill::query()
            ->whereBetween('bill_date', [$from, $to])
            ->whereNotNull('delivery_route_id')
            ->select('delivery_route_id', DB::raw('SUM(net_total + vat_amount) as total'))
            ->groupBy('delivery_route_id')
            ->pluck('total', 'delivery_route_id');

        $revenueByZone = Order::query()
            ->join('agents', 'orders.agent_id', '=', 'agents.id')
            ->whereBetween('orders.created_at', [$from, $to->copy()->endOfDay()])
            ->whereNotNull('agents.zone')
            ->select('agents.zone as zone', DB::raw('SUM(orders.total) as revenue'))
            ->groupBy('agents.zone')
            ->pluck('revenue', 'zone');

        $rows = $routes->map(function (DeliveryRoute $route) use ($fleetByRoute, $carrierByRoute, $revenueByZone) {
            $fleet = (float) ($fleetByRoute[$route->id] ?? 0);
            $carrier = (float) ($carrierByRoute[$route->id] ?? 0);
            $logistics = $fleet + $carrier;
            $revenue = (float) ($route->zone ? ($revenueByZone[$route->zone] ?? 0) : 0);

            return [
                'route' => $route,
                'fleet_cost' => $fleet,
                'carrier_cost' => $carrier,
                'logistics_cost' => $logistics,
                'zone_revenue' => $revenue,
                'margin_after_logistics' => $revenue - $logistics,
            ];
        })->sortByDesc('logistics_cost')->values();

        return compact('from', 'to', 'rows');
    }

    /**
     * @return array{from: Carbon, to: Carbon, rows: Collection, totals: array<string, float>}
     */
    public function fleetExpenseSummary(Carbon $from, Carbon $to): array
    {
        $expenses = FleetExpense::query()
            ->with(['vehicle', 'deliveryRoute'])
            ->whereDate('expense_date', '>=', $from->toDateString())
            ->whereDate('expense_date', '<=', $to->toDateString())
            ->orderByDesc('expense_date')
            ->get();

        $byType = $expenses
            ->groupBy('expense_type')
            ->map(fn (Collection $group, string $type) => [
                'type' => $type,
                'label' => FleetExpense::types()[$type] ?? ucfirst($type),
                'count' => $group->count(),
                'total' => round((float) $group->sum('amount'), 2),
            ])
            ->sortByDesc('total')
            ->values();

        return [
            'from' => $from,
            'to' => $to,
            'expenses' => $expenses,
            'rows' => $byType,
            'totals' => [
                'count' => $expenses->count(),
                'total' => round((float) $expenses->sum('amount'), 2),
            ],
        ];
    }

    /**
     * @return array{from: Carbon, to: Carbon, rows: Collection}
     */
    public function salesTargets(Carbon $from, Carbon $to): array
    {
        $targets = SalesTarget::with(['agent', 'employee'])
            ->whereDate('period_start', '<=', $to->toDateString())
            ->whereDate('period_end', '>=', $from->toDateString())
            ->get();

        $rows = $targets->map(function (SalesTarget $target) use ($from, $to) {
            $agentId = $target->agent_id;
            $achieved = 0.0;
            $periodFrom = max($from->toDateString(), $target->period_start?->toDateString() ?? $from->toDateString());
            $periodTo = min($to->toDateString(), $target->period_end?->toDateString() ?? $to->toDateString());

            if ($agentId) {
                $achieved = (float) Invoice::query()
                    ->whereHas('order', fn ($q) => $q->where('agent_id', $agentId))
                    ->whereDate('issued_at', '>=', $periodFrom)
                    ->whereDate('issued_at', '<=', $periodTo)
                    ->sum('net_total');
            }

            $targetAmount = (float) $target->target_value;
            $progress = $targetAmount > 0 ? round(($achieved / $targetAmount) * 100, 1) : 0;

            return [
                'target' => $target,
                'name' => $target->agent?->name ?? $target->employee?->name ?? '—',
                'target_amount' => $targetAmount,
                'achieved' => round($achieved, 2),
                'progress' => $progress,
                'gap' => round($targetAmount - $achieved, 2),
            ];
        })->sortByDesc('achieved')->values();

        return compact('from', 'to', 'rows');
    }

    /**
     * @return array{rows: Collection, count: int}
     */
    public function lowStock(): array
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $availableByProduct = \App\Models\StockEntry::query()
            ->selectRaw('product_id, SUM(quantity) as total')
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds))
            ->where('status', 'available')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $rows = Product::stockTracked()
            ->where('is_active', true)
            ->whereNotNull('reorder_level')
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) use ($availableByProduct) {
                $available = (float) ($availableByProduct[$product->id] ?? 0);
                $reorderLevel = (float) $product->reorder_level;

                return [
                    'product' => $product,
                    'available' => $available,
                    'reorder_level' => $reorderLevel,
                    'gap' => max(0.0, $reorderLevel - $available),
                ];
            })
            ->filter(fn (array $row) => $row['gap'] > 0)
            ->sortByDesc('gap')
            ->values();

        return ['rows' => $rows, 'count' => $rows->count()];
    }

    /**
     * @return array{from: Carbon, to: Carbon, rows: Collection, totals: array<string, int|float>}
     */
    public function deliveryPerformance(Carbon $from, Carbon $to): array
    {
        $deliveries = Delivery::query()
            ->with(['order.agent', 'route'])
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->orderByDesc('created_at')
            ->get();

        $podComplete = $deliveries->where('status', 'delivered')->count();

        return [
            'from' => $from,
            'to' => $to,
            'deliveries' => $deliveries,
            'rows' => $deliveries->take(100),
            'totals' => [
                'count' => $deliveries->count(),
                'pod_complete' => $podComplete,
                'pod_rate' => $deliveries->count() > 0 ? round(($podComplete / $deliveries->count()) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * @return array{from: Carbon, to: Carbon, receipts: Collection, payments: Collection, totals: array<string, float>}
     */
    public function bankReconciliationSummary(Carbon $from, Carbon $to): array
    {
        $receipts = Receipt::with('invoice.order.agent')
            ->whereBetween('received_at', [$from, $to->copy()->endOfDay()])
            ->orderBy('received_at')
            ->get();

        $payments = BillPayment::with('bill.supplier')
            ->whereBetween('paid_at', [$from, $to->copy()->endOfDay()])
            ->orderBy('paid_at')
            ->get();

        return [
            'from' => $from,
            'to' => $to,
            'receipts' => $receipts,
            'payments' => $payments,
            'totals' => [
                'receipts_count' => $receipts->count(),
                'receipts_total' => round((float) $receipts->sum('amount'), 2),
                'payments_count' => $payments->count(),
                'payments_total' => round((float) $payments->sum('amount'), 2),
                'net' => round((float) $receipts->sum('amount') - (float) $payments->sum('amount'), 2),
            ],
        ];
    }

    /**
     * @return array{from: Carbon, to: Carbon, agent: ?Agent, rows: Collection, opening: float, closing: float}
     */
    public function customerStatement(Carbon $from, Carbon $to, ?int $agentId): array
    {
        $agent = $agentId ? Agent::find($agentId) : null;
        $rows = collect();

        if ($agent) {
            $invoices = Invoice::with('receipts')
                ->whereHas('order', fn ($q) => $q->where('agent_id', $agent->id))
                ->whereDate('issued_at', '<=', $to->toDateString())
                ->orderBy('issued_at')
                ->get();

            foreach ($invoices as $invoice) {
                if ($invoice->issued_at && $invoice->issued_at->between($from, $to)) {
                    $rows->push([
                        'date' => $invoice->issued_at,
                        'type' => 'Invoice',
                        'reference' => $invoice->number ?? $invoice->id,
                        'debit' => (float) $invoice->net_total + (float) $invoice->vat_amount - (float) $invoice->withholding,
                        'credit' => 0.0,
                    ]);
                }

                foreach ($invoice->receipts as $receipt) {
                    if ($receipt->received_at && $receipt->received_at->between($from, $to)) {
                        $rows->push([
                            'date' => $receipt->received_at,
                            'type' => 'Receipt',
                            'reference' => $receipt->reference ?? $receipt->id,
                            'debit' => 0.0,
                            'credit' => (float) $receipt->amount,
                        ]);
                    }
                }
            }

            $rows = $rows->sortBy('date')->values();
        }

        $opening = $agent
            ? (float) Invoice::whereHas('order', fn ($q) => $q->where('agent_id', $agent->id))
                ->whereDate('issued_at', '<', $from->toDateString())
                ->get()
                ->sum(fn (Invoice $i) => (float) $i->outstanding)
            : 0.0;

        $movement = (float) $rows->sum('debit') - (float) $rows->sum('credit');

        return [
            'from' => $from,
            'to' => $to,
            'agent' => $agent,
            'agents' => Agent::orderBy('name')->get(['id', 'name', 'code']),
            'rows' => $rows,
            'opening' => round($opening, 2),
            'closing' => round($opening + $movement, 2),
        ];
    }

    /**
     * @return array{from: Carbon, to: Carbon, supplier: ?Supplier, rows: Collection, opening: float, closing: float}
     */
    public function supplierStatement(Carbon $from, Carbon $to, ?int $supplierId): array
    {
        $supplier = $supplierId ? Supplier::find($supplierId) : null;
        $rows = collect();

        if ($supplier) {
            $bills = PurchaseBill::with('payments')
                ->where('supplier_id', $supplier->id)
                ->whereDate('bill_date', '<=', $to->toDateString())
                ->orderBy('bill_date')
                ->get();

            foreach ($bills as $bill) {
                if ($bill->bill_date && $bill->bill_date->between($from, $to)) {
                    $rows->push([
                        'date' => $bill->bill_date,
                        'type' => 'Bill',
                        'reference' => $bill->number ?? $bill->id,
                        'debit' => (float) $bill->net_total,
                        'credit' => 0.0,
                    ]);
                }

                foreach ($bill->payments as $payment) {
                    if ($payment->paid_at && $payment->paid_at->between($from, $to)) {
                        $rows->push([
                            'date' => $payment->paid_at,
                            'type' => 'Payment',
                            'reference' => $payment->reference ?? $payment->id,
                            'debit' => 0.0,
                            'credit' => (float) $payment->amount,
                        ]);
                    }
                }
            }

            $rows = $rows->sortBy('date')->values();
        }

        $opening = $supplier
            ? (float) PurchaseBill::where('supplier_id', $supplier->id)
                ->whereDate('bill_date', '<', $from->toDateString())
                ->get()
                ->sum(fn (PurchaseBill $b) => (float) $b->outstanding)
            : 0.0;

        $movement = (float) $rows->sum('debit') - (float) $rows->sum('credit');

        return [
            'from' => $from,
            'to' => $to,
            'supplier' => $supplier,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'rows' => $rows,
            'opening' => round($opening, 2),
            'closing' => round($opening + $movement, 2),
        ];
    }

    /**
     * @return array{month: Carbon, rows: Collection}
     */
    public function commissionSummary(Request $request): array
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();
        $calculator = app(CommissionCalculator::class);

        $agents = Agent::query()
            ->where(function ($query) use ($from, $to) {
                $query->whereHas('orders', function ($orderQuery) use ($from, $to) {
                    $orderQuery->where('status', 'delivered')
                        ->whereHas('invoice', function ($invoiceQuery) use ($from, $to) {
                            $invoiceQuery
                                ->whereDate('issued_at', '>=', $from->toDateString())
                                ->whereDate('issued_at', '<=', $to->toDateString());
                        });
                })->orWhereHas('commissions', function ($commissionQuery) {
                    $commissionQuery->where('frequency', 'monthly');
                });
            })
            ->orderBy('name')
            ->get();

        $rows = collect();

        foreach ($agents as $agent) {
            $summary = $calculator->buildMonthlySummaryForAgent($agent, $from, $to);
            $sales = (float) ($summary['sales'] ?? 0);
            $commission = (float) ($summary['commission'] ?? 0);

            if ($sales <= 0 && $commission <= 0) {
                continue;
            }

            $rows->push([
                'agent' => $agent,
                'sales' => $sales,
                'commission' => $commission,
                'rate' => $sales > 0 ? round(($commission / $sales) * 100, 2) : 0,
            ]);
        }

        return [
            'month' => $month,
            'from' => $from,
            'to' => $to,
            'rows' => $rows->sortByDesc('commission')->values(),
        ];
    }

    /**
     * @return array{from: Carbon, to: Carbon}
     */
    public function resolvePeriod(Request $request): array
    {
        $resolved = ExportDateRange::resolve($request);

        if ($resolved && $resolved['from'] && $resolved['to']) {
            return [$resolved['from']->copy()->startOfDay(), $resolved['to']->copy()->endOfDay()];
        }

        $now = now();

        return [$now->copy()->startOfMonth(), $now->copy()->endOfDay()];
    }
}
