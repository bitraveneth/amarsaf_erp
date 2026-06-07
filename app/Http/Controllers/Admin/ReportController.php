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
use App\Services\Accounting\InventoryCostingService;
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

    public function profitAndLoss(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $currencyCode = config('app.currency', 'BDT');

        $entries = LedgerEntry::whereBetween('created_at', [$from, $to])->get();

        $sales = $entries->where('account', 'Sales Revenue')->sum('credit');
        $returns = $entries->where('account', 'Sales Returns')->sum('debit');
        $commissions = $entries->where('account', 'Commission Expense')->sum('debit');

        // Include simple period expenses recorded in the expenses module.
        $otherExpenses = $this->operatingExpensesTotal($from, $to);
        $payroll = SalaryDistribution::whereBetween('period_start', [$from, $to])
            ->get()
            ->sum(function (SalaryDistribution $distribution) {
                return (float) $distribution->base_salary
                    + (float) $distribution->bonus
                    + (float) $distribution->ta_allowances
                    + (float) $distribution->da_allowances
                    + (float) $distribution->commission;
            });

        $netSales = $sales - $returns;

        // --- Approximate COGS using production material cost snapshot ---
        $invoiceItems = InvoiceItem::with(['invoice', 'product'])
            ->whereHas('invoice', function ($q) use ($from, $to) {
                $q
                    ->whereDate('issued_at', '>=', $from->toDateString())
                    ->whereDate('issued_at', '<=', $to->toDateString());
            })
            ->get();

        // Average material_unit_cost per product from all runs where it is set
        $runsWithCost = ProductionRun::whereNotNull('material_unit_cost')->get();
        $costByProduct = $runsWithCost->groupBy('product_id')->map(
            fn ($group) => (float) $group->avg('material_unit_cost')
        );

        $cogsEstimated = 0.0;
        foreach ($invoiceItems as $item) {
            if (! $item->product_id) {
                continue;
            }
            $unitCost = $costByProduct->get($item->product_id);
            if ($unitCost === null) {
                continue;
            }
            $cogsEstimated += $unitCost * (float) $item->quantity;
        }

        $cogsGl = (float) LedgerEntry::where('account', config('accounting.accounts.cogs', 'Cost of Goods Sold'))
            ->whereBetween('created_at', [$from, $to])
            ->sum('debit');

        $cogs = $cogsGl > 0 ? $cogsGl : $cogsEstimated;
        $cogsSource = $cogsGl > 0 ? 'gl' : 'estimated';

        $grossProfit = $netSales - $cogs;
        $profit = $grossProfit - $commissions - $otherExpenses - $payroll;

        // Detailed ledger breakdown by account for the period
        $accountRows = $entries->groupBy('account')->map(function (Collection $rows, string $account) {
            $debit  = (float) $rows->sum('debit');
            $credit = (float) $rows->sum('credit');
            $net    = $credit - $debit; // income-style: positive = income, negative = expense

            return [
                'account' => $account,
                'debit'   => $debit,
                'credit'  => $credit,
                'net'     => $net,
            ];
        })->sortBy('account')->values();

        return view('admin.finance.pl', compact(
            'from',
            'to',
            'range',
            'currencyCode',
            'sales',
            'returns',
            'commissions',
            'otherExpenses',
            'payroll',
            'netSales',
            'cogs',
            'cogsEstimated',
            'cogsGl',
            'cogsSource',
            'grossProfit',
            'profit',
            'accountRows'
        ))->with([
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
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

    public function balanceSheet(Request $request)
    {
        $asOf = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $endOfDay = $asOf->copy()->endOfDay();

        $entries = LedgerEntry::where('created_at', '<=', $endOfDay)->get();

        $balances = [];
        foreach ($entries as $entry) {
            $account = $entry->account;
            if (!isset($balances[$account])) {
                $balances[$account] = 0;
            }
            $balances[$account] += $entry->debit - $entry->credit;
        }

        $assetsAccounts = Account::where('type', 'asset')->pluck('name')->all() ?: ['Bank', 'Accounts Receivable', 'Input VAT'];
        $liabilityAccounts = Account::where('type', 'liability')->pluck('name')->all() ?: ['Accounts Payable', 'VAT Payable', 'Agent Advances', 'Commission Payable'];

        $assets = [];
        $liabilities = [];

        foreach ($balances as $account => $amount) {
            if (in_array($account, $assetsAccounts, true)) {
                $assets[$account] = $amount;
            } elseif (in_array($account, $liabilityAccounts, true)) {
                $liabilities[$account] = $amount * -1;
            }
        }

        $totalAssets = array_sum($assets);
        $totalLiabilities = array_sum($liabilities);
        $equity = $totalAssets - $totalLiabilities;

        return view('admin.finance.bs', compact('asOf', 'assets', 'liabilities', 'totalAssets', 'totalLiabilities', 'equity'));
    }

    public function cashflow(Request $request)
    {
        [$from, $to] = $this->resolveDateRange(
            $request,
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $bankAccounts = Account::where('type', 'asset')
            ->where(function ($query) {
                $query->where('name', 'like', '%Bank%')
                    ->orWhere('name', 'like', '%Cash%')
                    ->orWhere('code', 'like', '10%');
            })
            ->pluck('name')
            ->all();

        $entries = LedgerEntry::whereIn('account', $bankAccounts ?: ['Bank'])
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $cashIn = $entries->sum('debit');
        $cashOut = $entries->sum('credit');
        $net = $cashIn - $cashOut;

        return view('admin.finance.cashflow', compact('from', 'to', 'cashIn', 'cashOut', 'net'));
    }

    public function agentPerformance(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : Carbon::now()->endOfDay();

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
        })->sortByDesc('net_sales');

        return view('admin.finance.agent_performance', [
            'from' => $from,
            'to' => $to,
            'rows' => $rows,
        ]);
    }

    public function productionSummary(Request $request)
    {
        [$from, $to] = $this->resolveDateRange(
            $request,
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

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
            'byProduct' => $byProduct,
            'salesTotal' => $salesTotal,
            'expensesTotal' => $expensesTotal,
            'materialCostTotal' => $materialCostTotal,
            'approxProfit' => $salesTotal - $materialCostTotal - $expensesTotal,
        ]);
    }

    public function payrollSummary(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

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

        return view('admin.finance.payroll_summary', compact('from', 'to', 'rows', 'totals'));
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

    public function trialBalance(Request $request)
    {
        [$from, $to] = $this->resolveDateRange(
            $request,
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $accounts = Account::orderBy('code')->get();
        $rows = [];

        foreach ($accounts as $account) {
            $openingDebit = $this->accountDebitTotal($account->id, null, $from->copy()->subDay()->endOfDay());
            $openingCredit = $this->accountCreditTotal($account->id, null, $from->copy()->subDay()->endOfDay());
            $periodDebit = $this->accountDebitTotal($account->id, $from, $to);
            $periodCredit = $this->accountCreditTotal($account->id, $from, $to);
            $closingDebit = $openingDebit + $periodDebit;
            $closingCredit = $openingCredit + $periodCredit;
            $balance = round($closingDebit - $closingCredit, 2);

            if (
                abs($openingDebit) < 0.01 && abs($openingCredit) < 0.01
                && abs($periodDebit) < 0.01 && abs($periodCredit) < 0.01
            ) {
                continue;
            }

            $rows[] = [
                'account' => $account,
                'opening_debit' => round($openingDebit - $openingCredit, 2) > 0 ? round($openingDebit - $openingCredit, 2) : 0,
                'opening_credit' => round($openingCredit - $openingDebit, 2) > 0 ? round($openingCredit - $openingDebit, 2) : 0,
                'period_debit' => $periodDebit,
                'period_credit' => $periodCredit,
                'closing_debit' => $balance > 0 ? $balance : 0,
                'closing_credit' => $balance < 0 ? abs($balance) : 0,
            ];
        }

        $totals = [
            'opening_debit' => collect($rows)->sum('opening_debit'),
            'opening_credit' => collect($rows)->sum('opening_credit'),
            'period_debit' => collect($rows)->sum('period_debit'),
            'period_credit' => collect($rows)->sum('period_credit'),
            'closing_debit' => collect($rows)->sum('closing_debit'),
            'closing_credit' => collect($rows)->sum('closing_credit'),
        ];

        return view('admin.finance.trial_balance', compact('from', 'to', 'rows', 'totals'));
    }

    public function generalLedger(Request $request)
    {
        [$from, $to] = $this->resolveDateRange(
            $request,
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $accounts = Account::orderBy('code')->get();
        $selectedAccount = $request->query('account_id')
            ? Account::find($request->query('account_id'))
            : null;

        $lines = collect();
        $runningBalance = 0.0;

        if ($selectedAccount) {
            $openingDebit = $this->accountDebitTotal($selectedAccount->id, null, $from->copy()->subDay()->endOfDay());
            $openingCredit = $this->accountCreditTotal($selectedAccount->id, null, $from->copy()->subDay()->endOfDay());
            $runningBalance = round($openingDebit - $openingCredit, 2);

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
        }

        return view('admin.finance.general_ledger', compact(
            'from',
            'to',
            'accounts',
            'selectedAccount',
            'lines',
            'runningBalance'
        ));
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
        $costing = app(InventoryCostingService::class);
        $rows = $costing->valuationReport();
        $operationalValue = $costing->operationalStockValue();
        $ledgerValue = $costing->ledgerInventoryBalance();
        $variance = round($operationalValue - $ledgerValue, 2);

        return view('admin.finance.inventory_valuation', compact(
            'rows',
            'operationalValue',
            'ledgerValue',
            'variance'
        ));
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
        [$from, $to] = $this->resolveDateRange(
            $request,
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $rows = app(ProductionVarianceService::class)->report($from, $to);
        $totals = [
            'variance' => round((float) $rows->sum('variance_total'), 2),
            'actual' => round((float) $rows->sum(fn (array $row) => $row['actual_unit_cost'] * $row['quantity']), 2),
            'standard' => round((float) $rows->sum(fn (array $row) => $row['standard_unit_cost'] * $row['quantity']), 2),
        ];

        return view('admin.finance.production_variance', compact('from', 'to', 'rows', 'totals'));
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
