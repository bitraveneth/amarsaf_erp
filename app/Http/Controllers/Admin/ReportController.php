<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesDashboardPeriod;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Batch;
use App\Models\Campaign;
use App\Models\CustomerGift;
use App\Models\Invoice;
use App\Models\JournalEntryLine;
use App\Models\LedgerEntry;
use App\Models\ProductionRun;
use App\Models\InvoiceItem;
use App\Models\Expense;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\EmployeeAllowance;
use App\Models\BillOfMaterial;
use App\Models\SalaryDistribution;
use App\Models\PurchaseBill;
use App\Services\Accounting\AgingReportService;
use App\Services\Accounting\CashFlowReportService;
use App\Services\Accounting\HierarchicalReportService;
use App\Services\Accounting\IncomeStatementPresenter;
use App\Services\Accounting\FinancialReportExportService;
use App\Services\Accounting\OperationalReportService;
use App\Support\ReportsCatalog;
use App\Services\BatchTraceabilityService;
use App\Services\ProductionVarianceService;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use ResolvesDashboardPeriod;

    public function profitAndLoss(
        Request $request,
        HierarchicalReportService $reports,
        IncomeStatementPresenter $presenter
    ) {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $currencyCode = config('app.currency', 'BDT');
        $compareMode = $request->query('compare', 'prior');
        $statement = $reports->profitAndLoss($from, $to);
        $periodLabel = $this->dashboardPeriodLabel($from, $to);

        $priorStatement = null;
        $priorPeriodLabel = null;
        if ($request->boolean('compare_enabled', true)) {
            [$priorFrom, $priorTo] = $this->resolvePriorPeriod($from, $to, $range, $compareMode);
            $priorStatement = $reports->profitAndLoss($priorFrom, $priorTo);
            $priorPeriodLabel = $this->dashboardPeriodLabel($priorFrom, $priorTo);
        }

        $incomeStatement = $presenter->present(
            $statement,
            $periodLabel,
            $currencyCode,
            $from,
            $to,
            $priorStatement,
            $priorPeriodLabel
        );

        return view('admin.finance.pl', array_merge($statement, [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'compareMode' => $compareMode,
            'currencyCode' => $currencyCode,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $periodLabel,
            'priorPeriodLabel' => $priorPeriodLabel,
            'incomeStatement' => $incomeStatement,
            'sales' => $statement['grossSales'],
            'returns' => $statement['salesReturns'],
            'netSales' => $statement['netRevenue'],
            'commissions' => $statement['commissionExpense'],
            'payroll' => $statement['payrollExpense'],
            'otherExpenses' => $statement['operatingExpenses'],
            'cogs' => $statement['manufacturingCost'],
            'cogsSource' => 'gl',
            'grossProfit' => $statement['grossProfit'],
            'profit' => $statement['netProfit'],
        ]));
    }

    public function manufacturingSchedule(Request $request, HierarchicalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $schedule = $reports->manufacturingSchedule($from, $to);

        return view('admin.finance.manufacturing_schedule', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'schedule' => $schedule,
        ]);
    }

    public function vat(Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')->startOfMonth()
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $outputEntries = LedgerEntry::where('account', 'VAT Payable')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $inputEntries = LedgerEntry::where('account', 'Input VAT')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $outputVat = (float) $outputEntries->sum('credit') - (float) $outputEntries->sum('debit');
        $inputVat = (float) $inputEntries->sum('debit') - (float) $inputEntries->sum('credit');
        $vatCollected = round($outputVat - $inputVat, 2);

        // Detailed per‑invoice breakdown (output VAT)
        $invoices = Invoice::with(['order.agent', 'creditNotes'])
            ->whereDate('issued_at', '>=', $from->toDateString())
            ->whereDate('issued_at', '<=', $to->toDateString())
            ->orderBy('issued_at')
            ->get();

        $invoiceRows = $invoices->map(function (Invoice $invoice) use ($from, $to) {
            $taxable = $invoice->netSalesAfterCreditsInRange($from, $to);
            $vat = max(0.0, (float) $invoice->vat_amount - $invoice->creditNotesVatTotalInRange($from, $to));
            $rate = $taxable > 0 ? round(($vat / $taxable) * 100, 2) : null;

            return [
                'date'        => $invoice->issued_at,
                'number'      => $invoice->number ?? $invoice->id,
                'customer'    => $invoice->order?->agent?->name,
                'taxable'     => $taxable,
                'vat'         => $vat,
                'vat_rate'    => $rate,
            ];
        });

        $totals = [
            'taxable' => $invoiceRows->sum('taxable'),
            'vat'     => $invoiceRows->sum('vat'),
        ];

        $purchaseBills = PurchaseBill::with('supplier')
            ->whereDate('bill_date', '>=', $from->toDateString())
            ->whereDate('bill_date', '<=', $to->toDateString())
            ->orderBy('bill_date')
            ->get();

        $purchaseRows = $purchaseBills->map(function (PurchaseBill $bill) {
            $rate = (float) $bill->net_total > 0
                ? round(((float) $bill->vat_amount / (float) $bill->net_total) * 100, 2)
                : null;

            return [
                'date' => $bill->bill_date,
                'number' => $bill->number,
                'supplier' => $bill->supplier?->name,
                'taxable' => (float) $bill->net_total,
                'vat' => (float) $bill->vat_amount,
                'vat_rate' => $rate,
            ];
        });

        $purchaseTotals = [
            'taxable' => $purchaseRows->sum('taxable'),
            'vat' => $purchaseRows->sum('vat'),
        ];

        return view('admin.finance.vat', [
            'month'        => $month,
            'vatCollected' => $vatCollected,
            'outputVat'    => $outputVat,
            'inputVat'     => $inputVat,
            'from'         => $from,
            'to'           => $to,
            'invoiceRows'  => $invoiceRows,
            'totals'       => $totals,
            'purchaseRows' => $purchaseRows,
            'purchaseTotals' => $purchaseTotals,
        ]);
    }

    public function balanceSheet(Request $request, HierarchicalReportService $reports)
    {
        $asOf = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $sheet = $reports->balanceSheet($asOf);

        return view('admin.finance.bs', array_merge(compact('asOf'), $sheet));
    }

    public function cashflow(Request $request, CashFlowReportService $cashFlow)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $report = $cashFlow->report($from, $to);
        $currencyCode = config('app.currency', 'BDT');
        $rangeOptions = $this->dashboardRangeOptions();
        $periodLabel = $this->dashboardPeriodLabel($from, $to);

        return view('admin.finance.cashflow', array_merge($report, compact(
            'currencyCode',
            'range',
            'rangeOptions',
            'periodLabel'
        )));
    }

    public function operationsHub()
    {
        return $this->renderCategoryHub('operations');
    }

    public function accountantHub()
    {
        return $this->renderCategoryHub('accountant');
    }

    public function costsHub()
    {
        return $this->renderCategoryHub('costs');
    }

    public function logisticsHub()
    {
        return $this->renderCategoryHub('logistics');
    }

    public function salesHub()
    {
        return $this->renderCategoryHub('sales');
    }

    protected function renderCategoryHub(string $menuCategoryKey)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod(request());

        return view('admin.reports.category-hub', array_merge(
            ReportsCatalog::categoryHub(
                $menuCategoryKey,
                request()->only(['range', 'from', 'to'])
            ),
            [
                'range' => $range,
                'from' => $from,
                'to' => $to,
                'rangeOptions' => $this->dashboardRangeOptions(),
                'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            ]
        ));
    }

    public function expenseSummary(Request $request, OperationalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $data = $reports->expenseSummary($from, $to);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.expense_summary', array_merge($data, [
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
        ]));
    }

    public function utilitiesReport(Request $request, OperationalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $data = $reports->utilitiesReport($from, $to);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.utilities_report', array_merge($data, [
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
        ]));
    }

    public function logisticsBillsSummary(Request $request, OperationalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $data = $reports->logisticsBillsSummary($from, $to);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.logistics_bills_summary', array_merge($data, [
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
        ]));
    }

    public function routeCosts(Request $request, OperationalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $data = $reports->routeCosts($from, $to);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.route_costs', array_merge($data, [
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
        ]));
    }

    public function fleetExpensesReport(Request $request, OperationalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $data = $reports->fleetExpenseSummary($from, $to);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.fleet_expenses_report', array_merge($data, [
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
        ]));
    }

    public function salesRegister(Request $request, FinancialReportExportService $exports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $rows = $exports->salesRegister($request);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.sales_register', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
            'rows' => $rows,
            'totalNet' => $rows->sum(fn ($row) => (float) str_replace(',', '', $row[4] ?? 0)),
        ]);
    }

    public function outstandingInvoicesReport(Request $request, FinancialReportExportService $exports)
    {
        $asOf = $request->filled('as_of')
            ? Carbon::parse($request->input('as_of'))->endOfDay()
            : Carbon::today()->endOfDay();
        $rows = $exports->outstandingInvoices($request);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.outstanding_invoices_report', [
            'asOf' => $asOf,
            'currencyCode' => $currencyCode,
            'rows' => $rows,
            'totalOutstanding' => $rows->sum(fn ($row) => (float) str_replace(',', '', $row[7] ?? 0)),
        ]);
    }

    public function outstandingBillsReport(Request $request, FinancialReportExportService $exports)
    {
        $asOf = $request->filled('as_of')
            ? Carbon::parse($request->input('as_of'))->endOfDay()
            : Carbon::today()->endOfDay();
        $rows = $exports->outstandingBills($request);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.outstanding_bills_report', [
            'asOf' => $asOf,
            'currencyCode' => $currencyCode,
            'rows' => $rows,
            'totalOutstanding' => $rows->sum(fn ($row) => (float) str_replace(',', '', $row[7] ?? 0)),
        ]);
    }

    public function commissionsReport(Request $request, OperationalReportService $reports)
    {
        $data = $reports->commissionSummary($request);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.commissions_report', array_merge($data, [
            'currencyCode' => $currencyCode,
            'periodLabel' => $data['from']->format('d M Y') . ' – ' . $data['to']->format('d M Y'),
        ]));
    }

    public function salesTargetsReport(Request $request, OperationalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $data = $reports->salesTargets($from, $to);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.sales_targets_report', array_merge($data, [
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
        ]));
    }

    public function lowStockReport(OperationalReportService $reports)
    {
        $data = $reports->lowStock();
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.low_stock_report', array_merge($data, [
            'currencyCode' => $currencyCode,
            'periodLabel' => 'Current stock levels',
        ]));
    }

    public function deliveryPerformanceReport(Request $request, OperationalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $data = $reports->deliveryPerformance($from, $to);

        return view('admin.finance.delivery_performance_report', array_merge($data, [
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
        ]));
    }

    public function bankReconciliationReport(Request $request, OperationalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $data = $reports->bankReconciliationSummary($from, $to);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.bank_reconciliation_report', array_merge($data, [
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
        ]));
    }

    public function customerStatementReport(Request $request, OperationalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $agentId = $request->integer('agent_id') ?: null;
        $data = $reports->customerStatement($from, $to, $agentId);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.customer_statement_report', array_merge($data, [
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
            'agentId' => $agentId,
        ]));
    }

    public function supplierStatementReport(Request $request, OperationalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $supplierId = $request->integer('supplier_id') ?: null;
        $data = $reports->supplierStatement($from, $to, $supplierId);
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.supplier_statement_report', array_merge($data, [
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
            'supplierId' => $supplierId,
        ]));
    }

    public function journalRegister(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);

        $entries = \App\Models\JournalEntry::query()
            ->with('lines')
            ->where('status', 'posted')
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString())
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->get();

        return view('admin.finance.journal_register', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'entries' => $entries,
        ]);
    }

    public function executiveSummary(Request $request)
    {
        $request->attributes->set('report_executive_mode', true);

        return app(ReportsDashboardController::class)($request);
    }

    public function agentPerformance(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $currencyCode = config('app.currency', 'BDT');
        $periodLabel = $this->dashboardPeriodLabel($from, $to);
        $rangeOptions = $this->dashboardRangeOptions();

        $invoices = Invoice::with(['order.agent', 'receipts', 'creditNotes', 'advanceApplications'])
            ->whereBetween('issued_at', [$from, $to])
            ->whereHas('order.agent')
            ->get();

        $byAgent = [];

        foreach ($invoices as $invoice) {
            $agent = $invoice->order?->agent;

            if (! $agent) {
                continue;
            }

            $agentId = $agent->id;

            if (! isset($byAgent[$agentId])) {
                $byAgent[$agentId] = [
                    'agent' => $agent,
                    'order_ids' => [],
                    'invoice_count' => 0,
                    'invoiced' => 0,
                    'credits' => 0,
                    'receipts' => 0,
                    'advances' => 0,
                ];
            }

            $bucket = &$byAgent[$agentId];

            $gross = ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding;
            $bucket['invoiced'] += $gross;
            $bucket['invoice_count']++;

            if ($invoice->order_id) {
                $bucket['order_ids'][$invoice->order_id] = true;
            }

            $bucket['credits'] += $invoice->creditNotesTotalInRange($from, $to);
            $bucket['advances'] += $invoice->advancesAppliedTotalInRange($from, $to);
            $bucket['receipts'] += $invoice->receiptsTotalInRange($from, $to);
        }

        $rows = collect($byAgent)->map(function (array $bucket) {
            $netSales = $bucket['invoiced'] - $bucket['credits'];
            $outstanding = max(0, $netSales - $bucket['receipts'] - $bucket['advances']);

            return [
                'agent' => $bucket['agent'],
                'order_count' => count($bucket['order_ids']),
                'invoice_count' => $bucket['invoice_count'],
                'invoiced' => $bucket['invoiced'],
                'credits' => $bucket['credits'],
                'net_sales' => $netSales,
                'receipts' => $bucket['receipts'],
                'advances' => $bucket['advances'],
                'outstanding' => $outstanding,
            ];
        })->sortByDesc('net_sales')->values();

        $summary = [
            'net_sales' => round((float) $rows->sum('net_sales'), 2),
            'receipts' => round((float) $rows->sum('receipts'), 2),
            'outstanding' => round((float) $rows->sum('outstanding'), 2),
            'agent_count' => $rows->count(),
            'collection_rate' => $rows->sum('net_sales') > 0
                ? round($rows->sum('receipts') / $rows->sum('net_sales') * 100, 1)
                : 0,
        ];

        $topAgents = $rows->take(5)->map(fn (array $row) => [
            'label' => $row['agent']->name,
            'value' => $row['net_sales'],
        ])->all();

        return view('admin.finance.agent_performance', compact(
            'from',
            'to',
            'range',
            'rangeOptions',
            'periodLabel',
            'currencyCode',
            'rows',
            'summary',
            'topAgents'
        ));
    }

    public function productionSummary(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $currencyCode = config('app.currency', 'BDT');
        $periodLabel = $this->dashboardPeriodLabel($from, $to);
        $rangeOptions = $this->dashboardRangeOptions();

        $production = ProductionRun::with('product')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        // Preload active BOMs (with component standard_cost) for all products
        $productIds = $production->pluck('product_id')->unique()->filter()->all();

        $boms = collect();
        if (
            Schema::hasTable('bill_of_materials')
            && Schema::hasTable('bill_of_material_items')
            && ! empty($productIds)
        ) {
            $boms = BillOfMaterial::whereIn('product_id', $productIds)
                ->where('is_active', true)
                ->with(['items.component'])
                ->orderByDesc('id')
                ->get()
                ->keyBy('product_id');
        }

        $byProduct = $production->groupBy('product_id')->map(function ($runs, $productId) use ($boms) {
            $product  = $runs->first()->product;
            $quantity = $runs->sum('quantity');

            $unitCost  = null;
            $totalCost = null;

            // Prefer persisted costing data if available
            $totalCostFromRuns = $runs->sum('material_total_cost');
            if ($quantity > 0 && $totalCostFromRuns > 0) {
                $totalCost = $totalCostFromRuns;
                $unitCost  = $totalCostFromRuns / (float) $quantity;
            } else {
                // Fallback: estimate from BOM + component standard_cost / BOM unit_cost
                $bom = $boms->get($productId);
                if ($bom && $bom->items->isNotEmpty()) {
                    if ($bom->material_unit_cost !== null && $bom->material_unit_cost > 0) {
                        $unitCost  = (float) $bom->material_unit_cost;
                        $totalCost = $unitCost * (float) $quantity;
                    } else {
                        $accumulator = 0.0;
                        foreach ($bom->items as $item) {
                            $component = $item->component;
                            $baseCost = null;
                            if ($item->unit_cost !== null) {
                                $baseCost = (float) $item->unit_cost;
                            } elseif ($component && $component->standard_cost !== null) {
                                $baseCost = (float) $component->standard_cost;
                            }
                            if ($baseCost !== null) {
                                $accumulator += $baseCost * (float) $item->quantity;
                            }
                        }
                        if ($accumulator > 0) {
                            $unitCost  = $accumulator;
                            $totalCost = $unitCost * (float) $quantity;
                        }
                    }
                }
            }

            return [
                'product'    => $product,
                'runs'       => $runs->count(),
                'quantity'   => $quantity,
                'unit_cost'  => $unitCost,
                'total_cost' => $totalCost,
            ];
        });

        $salesInvoices = Invoice::with('creditNotes')
            ->whereBetween('issued_at', [$from, $to])
            ->get();
        $salesTotal = $salesInvoices->sum(function (Invoice $invoice) use ($from, $to) {
            return $invoice->netSalesAfterCreditsInRange($from, $to);
        });

        $expensesTotal = $this->operatingExpensesTotal($from, $to);

        $materialCostTotal = $byProduct->sum(fn (array $row) => (float) ($row['total_cost'] ?? 0));

        return view('admin.finance.production_summary', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $rangeOptions,
            'periodLabel' => $periodLabel,
            'currencyCode' => $currencyCode,
            'byProduct' => $byProduct,
            'salesTotal' => $salesTotal,
            'expensesTotal' => $expensesTotal,
            'materialCostTotal' => $materialCostTotal,
            'approxProfit' => $salesTotal - $materialCostTotal - $expensesTotal,
            'totalRuns' => (int) $byProduct->sum('runs'),
            'totalQuantity' => (float) $byProduct->sum('quantity'),
        ]);
    }

    public function payrollSummary(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);

        $distributions = SalaryDistribution::with('employee')
            ->whereBetween('period_start', [$from, $to])
            ->orderBy('employee_id')
            ->orderBy('period_start')
            ->get()
            ->groupBy('employee_id');

        $rows = $distributions->map(function ($employeeDistributions) {
            $employee = $employeeDistributions->first()->employee;
            $baseSalary = $employeeDistributions->sum('base_salary');
            $totalTa = $employeeDistributions->sum('ta_allowances');
            $totalDa = $employeeDistributions->sum('da_allowances');
            $totalBonus = $employeeDistributions->sum('bonus');
            $commission = $employeeDistributions->sum('commission');
            $grandTotal = $baseSalary + $totalTa + $totalDa + $totalBonus + $commission;

            return [
                'employee' => $employee,
                'contracts' => collect(),
                'distributions' => $employeeDistributions,
                'base_salary' => $baseSalary,
                'base_ta' => 0,
                'base_da' => 0,
                'base_bonus' => 0,
                'ta_allowances' => $totalTa,
                'da_allowances' => $totalDa,
                'bonus_allowances' => $totalBonus,
                'commission' => $commission,
                'total_salary' => $baseSalary,
                'total_ta' => $totalTa,
                'total_da' => $totalDa,
                'total_bonus' => $totalBonus + $commission,
                'grand_total' => $grandTotal,
            ];
        })->filter(function (array $row) {
            return $row['grand_total'] > 0;
        })->values();

        $totals = [
            'salary' => $rows->sum('total_salary'),
            'ta' => $rows->sum('total_ta'),
            'da' => $rows->sum('total_da'),
            'bonus' => $rows->sum('total_bonus'),
            'grand' => $rows->sum('grand_total'),
        ];

        return view('admin.finance.payroll_summary', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'rows' => $rows,
            'totals' => $totals,
        ]);
    }

    protected function resolveDateRange(Request $request, Carbon $defaultFrom, Carbon $defaultTo): array
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : $defaultFrom->copy()->startOfDay();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : $defaultTo->copy()->endOfDay();

        return [$from, $to];
    }

    protected function operatingExpensesTotal(Carbon $from, Carbon $to): float
    {
        $expenses = (float) Expense::whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->whereIn('status', [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED, 'paid', 'overdue'])
            ->sum('amount');

        $giftExpenses = (float) CustomerGift::whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->whereIn('status', [CustomerGift::STATUS_GIVEN, 'delivered'])
            ->sum('amount');

        $campaignExpenses = (float) Campaign::where(function ($query) use ($from, $to) {
            $query->whereBetween('created_at', [$from, $to])
                ->orWhere(function ($campaignQuery) use ($from, $to) {
                    $campaignQuery
                        ->whereDate('start_date', '>=', $from->toDateString())
                        ->whereDate('start_date', '<=', $to->toDateString());
                });
        })->whereIn('status', [
            Campaign::STATUS_RUNNING,
            Campaign::STATUS_COMPLETED,
            'active',
            'paused',
        ])->sum('cost');

        return $expenses + $giftExpenses + $campaignExpenses;
    }

    public function trialBalance(Request $request, HierarchicalReportService $reports)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);

        $expandAll = $request->boolean('expand', true);
        $rows = $reports->trialBalance($from, $to, $expandAll);
        $tree = $reports->trialBalanceTree($from, $to);

        $leaves = $rows->where('is_group', false);
        $totals = [
            'opening_debit' => $leaves->sum('opening_debit'),
            'opening_credit' => $leaves->sum('opening_credit'),
            'period_debit' => $leaves->sum('period_debit'),
            'period_credit' => $leaves->sum('period_credit'),
            'closing_debit' => $leaves->sum('closing_debit'),
            'closing_credit' => $leaves->sum('closing_credit'),
        ];

        $isBalanced = abs($totals['period_debit'] - $totals['period_credit']) < 0.01
            && abs($totals['closing_debit'] - $totals['closing_credit']) < 0.01;

        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.trial_balance', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'rows' => $rows,
            'tree' => $tree,
            'totals' => $totals,
            'expandAll' => $expandAll,
            'isBalanced' => $isBalanced,
            'currencyCode' => $currencyCode,
        ]);
    }

    public function generalLedger(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);

        $activeAccounts = $this->generalLedgerActiveAccounts($from, $to);
        if ($activeAccounts->isEmpty()) {
            $activeAccounts = $this->generalLedgerActiveAccounts(
                $from->copy()->subYear(),
                $to
            );
        }

        $autoSelectedAccount = false;
        $selectedAccount = $request->filled('account_id')
            ? Account::find($request->query('account_id'))
            : null;

        if (! $selectedAccount && $activeAccounts->isNotEmpty()) {
            $selectedAccount = $activeAccounts->first()['account'];
            $autoSelectedAccount = ! $request->has('account_id');
        }

        if ($autoSelectedAccount && $selectedAccount) {
            return redirect()->route('admin.reports.general-ledger', array_merge(
                $request->only(['range', 'from', 'to']),
                ['account_id' => $selectedAccount->id],
            ));
        }

        if ($selectedAccount) {
            $selectedAccount->loadMissing([
                'parent',
                'parent.parent',
                'parent.parent.parent',
                'parent.parent.parent.parent',
                'parent.parent.parent.parent.parent',
            ]);
        }

        $accountOptions = $activeAccounts->pluck('account');
        if ($selectedAccount && ! $accountOptions->pluck('id')->contains($selectedAccount->id)) {
            $accountOptions = $accountOptions->prepend($selectedAccount)->values();
        }

        $lines = collect();
        $runningBalance = 0.0;
        $openingBalance = 0.0;
        $periodDebit = 0.0;
        $periodCredit = 0.0;

        if ($selectedAccount) {
            $openingDebit = $this->accountDebitTotal($selectedAccount->id, null, $from->copy()->subDay()->endOfDay());
            $openingCredit = $this->accountCreditTotal($selectedAccount->id, null, $from->copy()->subDay()->endOfDay());
            $openingBalance = round($openingDebit - $openingCredit, 2);
            $runningBalance = $openingBalance;

            $entries = JournalEntryLine::query()
                ->with(['journalEntry', 'account'])
                ->where('account_id', $selectedAccount->id)
                ->whereHas('journalEntry', function ($query) use ($from, $to) {
                    $query->where('status', 'posted')
                        ->whereDate('entry_date', '>=', $from->toDateString())
                        ->whereDate('entry_date', '<=', $to->toDateString());
                })
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->orderBy('journal_entries.entry_date')
                ->orderBy('journal_entries.id')
                ->orderBy('journal_entry_lines.line_number')
                ->select('journal_entry_lines.*')
                ->get();

            $lines = $entries->map(function (JournalEntryLine $line) use (&$runningBalance) {
                $runningBalance = round($runningBalance + (float) $line->debit - (float) $line->credit, 2);

                return [
                    'date' => $line->journalEntry->entry_date,
                    'journal' => $line->journalEntry,
                    'description' => $line->description ?: $line->journalEntry->description,
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                    'balance' => $runningBalance,
                ];
            });

            $periodDebit = round((float) $lines->sum('debit'), 2);
            $periodCredit = round((float) $lines->sum('credit'), 2);
        }

        $closingBalance = $runningBalance;
        $currencyCode = config('app.currency', 'BDT');

        return view('admin.finance.general_ledger', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'selectedAccount' => $selectedAccount,
            'accountOptions' => $accountOptions,
            'activeAccounts' => $activeAccounts,
            'autoSelectedAccount' => $autoSelectedAccount,
            'lines' => $lines,
            'openingBalance' => $openingBalance,
            'periodDebit' => $periodDebit,
            'periodCredit' => $periodCredit,
            'closingBalance' => $closingBalance,
            'currencyCode' => $currencyCode,
        ]);
    }

    /**
     * @return Collection<int, array{account: Account, line_count: int, period_debit: float, period_credit: float}>
     */
    protected function generalLedgerActiveAccounts(Carbon $from, Carbon $to): Collection
    {
        $accountIds = JournalEntryLine::query()
            ->whereHas('journalEntry', function ($query) use ($from, $to) {
                $query->where('status', 'posted')
                    ->whereDate('entry_date', '>=', $from->toDateString())
                    ->whereDate('entry_date', '<=', $to->toDateString());
            })
            ->selectRaw('account_id, COUNT(*) as line_count, COALESCE(SUM(debit), 0) as period_debit, COALESCE(SUM(credit), 0) as period_credit')
            ->groupBy('account_id')
            ->orderByDesc('line_count')
            ->get();

        $accounts = Account::query()
            ->whereIn('id', $accountIds->pluck('account_id'))
            ->get()
            ->keyBy('id');

        return $accountIds
            ->map(function ($row) use ($accounts) {
                $account = $accounts->get($row->account_id);
                if (! $account) {
                    return null;
                }

                return [
                    'account' => $account,
                    'line_count' => (int) $row->line_count,
                    'period_debit' => round((float) $row->period_debit, 2),
                    'period_credit' => round((float) $row->period_credit, 2),
                ];
            })
            ->filter()
            ->values();
    }

    protected function accountDebitTotal(int $accountId, ?Carbon $from, Carbon $to): float
    {
        return (float) JournalEntryLine::query()
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function ($query) use ($from, $to) {
                $query->where('status', 'posted')
                    ->when($from, fn ($q) => $q->whereDate('entry_date', '>=', $from->toDateString()))
                    ->whereDate('entry_date', '<=', $to->toDateString());
            })
            ->sum('debit');
    }

    protected function accountCreditTotal(int $accountId, ?Carbon $from, Carbon $to): float
    {
        return (float) JournalEntryLine::query()
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function ($query) use ($from, $to) {
                $query->where('status', 'posted')
                    ->when($from, fn ($q) => $q->whereDate('entry_date', '>=', $from->toDateString()))
                    ->whereDate('entry_date', '<=', $to->toDateString());
            })
            ->sum('credit');
    }

    public function inventoryValuation(Request $request)
    {
        $summary = app(InventoryCostingService::class)->valuationSummary();
        $asOf = Carbon::today();
        $periodLabel = 'As of ' . $asOf->format('d M Y');

        return view('admin.finance.inventory_valuation', array_merge($summary, [
            'asOf' => $asOf,
            'periodLabel' => $periodLabel,
        ]));
    }

    public function receivableAging(Request $request)
    {
        $asOf = $request->filled('as_of')
            ? Carbon::parse($request->input('as_of'))->endOfDay()
            : Carbon::today()->endOfDay();

        $report = app(AgingReportService::class)->receivableAging($asOf);
        $bucketLabels = AgingReportService::BUCKET_LABELS;

        return view('admin.finance.ar_aging', array_merge($report, compact('bucketLabels', 'asOf')));
    }

    public function payableAging(Request $request)
    {
        $asOf = $request->filled('as_of')
            ? Carbon::parse($request->input('as_of'))->endOfDay()
            : Carbon::today()->endOfDay();

        $report = app(AgingReportService::class)->payableAging($asOf);
        $bucketLabels = AgingReportService::BUCKET_LABELS;

        return view('admin.finance.ap_aging', array_merge($report, compact('bucketLabels', 'asOf')));
    }

    public function productionVariance(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);

        $rows = app(ProductionVarianceService::class)->report($from, $to);
        $totals = [
            'variance' => round((float) $rows->sum('variance_total'), 2),
            'actual' => round((float) $rows->sum(fn (array $row) => $row['actual_unit_cost'] * $row['quantity']), 2),
            'standard' => round((float) $rows->sum(fn (array $row) => $row['standard_unit_cost'] * $row['quantity']), 2),
        ];

        return view('admin.finance.production_variance', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'rows' => $rows,
            'totals' => $totals,
        ]);
    }

    public function batchTraceLookup(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $batches = $query !== ''
            ? Batch::with('product')->where('batch_code', 'like', '%' . $query . '%')->limit(20)->get()
            : Batch::with('product')->latest('id')->limit(20)->get();

        return view('admin.finance.batch_trace_lookup', compact('query', 'batches'));
    }

    public function batchTrace(Batch $batch, BatchTraceabilityService $traceability)
    {
        $trace = $traceability->trace($batch);

        return view('admin.finance.batch_trace', $trace);
    }

    public function vatExport(Request $request): StreamedResponse
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')->startOfMonth()
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $invoices = Invoice::with(['order.agent', 'creditNotes'])
            ->whereDate('issued_at', '>=', $from->toDateString())
            ->whereDate('issued_at', '<=', $to->toDateString())
            ->orderBy('issued_at')
            ->get();

        $purchaseBills = PurchaseBill::with('supplier')
            ->whereDate('bill_date', '>=', $from->toDateString())
            ->whereDate('bill_date', '<=', $to->toDateString())
            ->orderBy('bill_date')
            ->get();

        $filename = 'vat-return-' . $month->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($invoices, $purchaseBills, $from, $to) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Type', 'Date', 'Number', 'Party', 'Taxable', 'VAT', 'Rate']);

            foreach ($invoices as $invoice) {
                $taxable = $invoice->netSalesAfterCreditsInRange($from, $to);
                $vat = max(0.0, (float) $invoice->vat_amount - $invoice->creditNotesVatTotalInRange($from, $to));
                $rate = $taxable > 0 ? round(($vat / $taxable) * 100, 2) : 0;

                fputcsv($handle, [
                    'Output VAT',
                    $invoice->issued_at?->format('Y-m-d'),
                    $invoice->number,
                    $invoice->order?->agent?->name,
                    number_format($taxable, 2, '.', ''),
                    number_format($vat, 2, '.', ''),
                    number_format($rate, 2, '.', ''),
                ]);
            }

            foreach ($purchaseBills as $bill) {
                $rate = (float) $bill->net_total > 0
                    ? round(((float) $bill->vat_amount / (float) $bill->net_total) * 100, 2)
                    : 0;

                fputcsv($handle, [
                    'Input VAT',
                    $bill->bill_date?->format('Y-m-d'),
                    $bill->number,
                    $bill->supplier?->name,
                    number_format((float) $bill->net_total, 2, '.', ''),
                    number_format((float) $bill->vat_amount, 2, '.', ''),
                    number_format($rate, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
