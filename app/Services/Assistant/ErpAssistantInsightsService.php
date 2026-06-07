<?php

namespace App\Services\Assistant;

use App\Helpers\Permission;
use App\Models\Agent;
use App\Models\Campaign;
use App\Models\CustomerGift;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\SalaryDistribution;
use App\Models\SalesTarget;
use App\Models\StockEntry;
use App\Models\User;
use App\Support\InvoiceRevenueMetrics;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ErpAssistantInsightsService
{
    public function snapshot(?User $user): array
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfDay();
        $currency = config('app.currency', 'BDT');

        $data = [
            'currency' => $currency,
            'period_label' => $today->format('F Y'),
            'permissions' => [
                'finance' => Permission::can($user, 'accounting.manage'),
                'sales' => Permission::can($user, 'sales.manage'),
                'agents' => Permission::can($user, 'control.agents'),
                'production' => Permission::can($user, 'manufacturing.manage'),
                'inventory' => Permission::can($user, 'inventory.manage'),
                'reports' => Permission::can($user, 'reports.view'),
            ],
        ];

        if ($data['permissions']['finance'] && Schema::hasTable('invoices')) {
            $revenueMtd = round(InvoiceRevenueMetrics::sumNetSalesAfterCreditsInRange(
                $monthStart,
                $monthEnd,
                $monthStart,
                $monthEnd
            ), 2);

            $revenueToday = round(InvoiceRevenueMetrics::sumNetSalesAfterCreditsInRange(
                $today,
                $today,
                $today,
                $today
            ), 2);

            $collectionsMtd = round(InvoiceRevenueMetrics::sumReceiptsInRange($monthStart, $monthEnd), 2);
            $receivables = InvoiceRevenueMetrics::sumOutstandingAsOf($today->copy()->endOfDay());
            $overdueInvoices = InvoiceRevenueMetrics::countOverdueInvoices($today);

            $totalExpenses = 0.0;
            $totalPayroll = 0.0;

            if (Schema::hasTable('expenses')) {
                $expenses = (float) Expense::whereDate('date', '>=', $monthStart->toDateString())
                    ->whereDate('date', '<=', $monthEnd->toDateString())
                    ->whereIn('status', [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED, 'paid', 'overdue'])
                    ->sum('amount');

                $giftExpenses = Schema::hasTable('customer_gifts')
                    ? (float) CustomerGift::whereDate('date', '>=', $monthStart->toDateString())
                        ->whereDate('date', '<=', $monthEnd->toDateString())
                        ->whereIn('status', [CustomerGift::STATUS_GIVEN, 'delivered'])
                        ->sum('amount')
                    : 0.0;

                $campaignExpenses = Schema::hasTable('campaigns')
                    ? (float) Campaign::where(function ($query) use ($monthStart, $monthEnd) {
                        $query->whereBetween('created_at', [$monthStart, $monthEnd])
                            ->orWhere(function ($campaignQuery) use ($monthStart, $monthEnd) {
                                $campaignQuery
                                    ->whereDate('start_date', '>=', $monthStart->toDateString())
                                    ->whereDate('start_date', '<=', $monthEnd->toDateString());
                            });
                    })->whereIn('status', [
                        Campaign::STATUS_RUNNING,
                        Campaign::STATUS_COMPLETED,
                        'active',
                        'paused',
                    ])->sum('cost')
                    : 0.0;

                $totalExpenses = $expenses + $giftExpenses + $campaignExpenses;
            }

            if (Schema::hasTable('salary_distributions')) {
                $totalPayroll = (float) SalaryDistribution::whereBetween('period_start', [$monthStart, $monthEnd])
                    ->get()
                    ->sum(fn ($d) => $d->base_salary + $d->bonus + $d->ta_allowances + $d->da_allowances + $d->commission);
            }

            $profitEstimate = round($revenueMtd - ($totalExpenses + $totalPayroll), 2);
            $collectionRate = $revenueMtd > 0
                ? round(min(100, ($collectionsMtd / $revenueMtd) * 100), 1)
                : 0.0;

            $data['finance'] = [
                'revenue_mtd' => $revenueMtd,
                'revenue_today' => $revenueToday,
                'collections_mtd' => $collectionsMtd,
                'receivables' => $receivables,
                'overdue_invoices' => $overdueInvoices,
                'expenses_mtd' => round($totalExpenses, 2),
                'payroll_mtd' => round($totalPayroll, 2),
                'profit_estimate_mtd' => $profitEstimate,
                'collection_rate' => $collectionRate,
            ];
        }

        if ($data['permissions']['sales'] && Schema::hasTable('orders')) {
            $data['sales'] = [
                'orders_today' => Order::query()
                    ->where('order_type', '!=', 'return')
                    ->whereDate('delivery_date', $today)
                    ->count(),
                'orders_mtd' => Order::query()
                    ->where('order_type', '!=', 'return')
                    ->whereYear('delivery_date', $monthStart->year)
                    ->whereMonth('delivery_date', $monthStart->month)
                    ->whereDate('delivery_date', '<=', $monthEnd->toDateString())
                    ->count(),
                'returns_mtd' => Order::query()
                    ->where('order_type', 'return')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count(),
                'pending_deliveries' => Order::query()
                    ->where('order_type', '!=', 'return')
                    ->whereIn('status', ['confirmed', 'picked', 'packed', 'dispatched'])
                    ->count(),
            ];
        }

        if ($data['permissions']['agents'] && Schema::hasTable('agents')) {
            $data['agents'] = [
                'active_count' => Agent::where('is_active', true)->count(),
            ];
        }

        if ($data['permissions']['sales'] && Schema::hasTable('sales_targets')) {
            $activeTargets = SalesTarget::query()
                ->whereDate('period_start', '<=', $monthEnd->toDateString())
                ->whereDate('period_end', '>=', $monthStart->toDateString())
                ->get(['agent_id', 'employee_id', 'target_value']);

            $agentTarget = (float) $activeTargets->whereNotNull('agent_id')->sum('target_value');
            $employeeTarget = (float) $activeTargets->whereNull('agent_id')->whereNotNull('employee_id')->sum('target_value');
            $target = $agentTarget > 0 ? $agentTarget : $employeeTarget;
            $achieved = $data['finance']['revenue_mtd'] ?? 0.0;

            $data['sales_target'] = [
                'target_mtd' => round($target, 2),
                'achieved_mtd' => $achieved,
                'progress_percent' => $target > 0 ? round(min(100, ($achieved / $target) * 100), 1) : 0.0,
                'gap' => round(max(0, $target - $achieved), 2),
            ];
        }

        if ($data['permissions']['production'] && Schema::hasTable('production_runs')) {
            $data['production'] = [
                'quantity_today' => round((float) ProductionRun::query()
                    ->where('qc_status', 'approved')
                    ->whereDate('created_at', $today)
                    ->sum('quantity'), 2),
                'pending_qc' => ProductionRun::query()
                    ->where('qc_status', '!=', 'approved')
                    ->count(),
            ];
        }

        if ($data['permissions']['inventory'] && Schema::hasTable('stock_entries') && Schema::hasTable('products')) {
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

            $data['inventory'] = [
                'low_stock_count' => $lowStock,
            ];
        }

        $tasks = [];
        if (isset($data['production']['pending_qc'])) {
            $tasks['pending_qc'] = $data['production']['pending_qc'];
        }
        if (isset($data['inventory']['low_stock_count'])) {
            $tasks['low_stock'] = $data['inventory']['low_stock_count'];
        }
        if (isset($data['finance']['overdue_invoices'])) {
            $tasks['overdue_invoices'] = $data['finance']['overdue_invoices'];
        }
        if (isset($data['sales']['pending_deliveries'])) {
            $tasks['pending_deliveries'] = $data['sales']['pending_deliveries'];
        }

        $data['tasks'] = $tasks;
        $data['task_total'] = array_sum($tasks);

        return $data;
    }

    public function canAccessMetric(?User $user, string $metricKey): bool
    {
        $snapshot = $this->snapshot($user);

        return match ($metricKey) {
            'revenue_mtd', 'revenue_today', 'collections_mtd', 'receivables', 'overdue_invoices',
            'expenses_mtd', 'payroll_mtd', 'profit_estimate_mtd', 'collection_rate' => $snapshot['permissions']['finance'] ?? false,
            'orders_today', 'orders_mtd', 'returns_mtd', 'pending_deliveries', 'sales_target' => $snapshot['permissions']['sales'] ?? false,
            'active_agents' => $snapshot['permissions']['agents'] ?? false,
            'production_today', 'pending_qc' => $snapshot['permissions']['production'] ?? false,
            'low_stock_count' => $snapshot['permissions']['inventory'] ?? false,
            'ops_tasks', 'business_summary', 'business_health' => true,
            default => false,
        };
    }

    public function permissionDeniedMessage(string $area): string
    {
        return match ($area) {
            'finance' => "You don't have access to finance data. Please ask your administrator for accounting permissions.",
            'sales' => "You don't have access to sales order data. Please ask your administrator for sales permissions.",
            'agents' => "You don't have access to agent data. Please ask your administrator for agent permissions.",
            'production' => "You don't have access to production data. Please ask your administrator for manufacturing permissions.",
            'inventory' => "You don't have access to inventory data. Please ask your administrator for inventory permissions.",
            default => "You don't have permission to view that information.",
        };
    }
}
