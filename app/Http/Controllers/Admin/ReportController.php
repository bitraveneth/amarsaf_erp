<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\ProductionRun;
use App\Models\InvoiceItem;
use App\Models\Expense;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\EmployeeAllowance;
use App\Models\BillOfMaterial;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function profitAndLoss(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $entries = LedgerEntry::whereBetween('created_at', [$from, $to])->get();

        $sales = $entries->where('account', 'Sales Revenue')->sum('credit');
        $returns = $entries->where('account', 'Sales Returns')->sum('debit');
        $commissions = $entries->where('account', 'Commission Expense')->sum('debit');

        // Include simple period expenses recorded in the expenses module.
        $otherExpenses = Expense::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $netSales = $sales - $returns;

        // --- Approximate COGS using production material cost snapshot ---
        $invoiceItems = InvoiceItem::with(['invoice', 'product'])
            ->whereHas('invoice', function ($q) use ($from, $to) {
                $q->whereBetween('issued_at', [$from->toDateString(), $to->toDateString()]);
            })
            ->get();

        // Average material_unit_cost per product from all runs where it is set
        $runsWithCost = ProductionRun::whereNotNull('material_unit_cost')->get();
        $costByProduct = $runsWithCost->groupBy('product_id')->map(
            fn ($group) => (float) $group->avg('material_unit_cost')
        );

        $cogs = 0.0;
        foreach ($invoiceItems as $item) {
            if (! $item->product_id) {
                continue;
            }
            $unitCost = $costByProduct->get($item->product_id);
            if ($unitCost === null) {
                continue;
            }
            $cogs += $unitCost * (float) $item->quantity;
        }

        $grossProfit = $netSales - $cogs;
        $profit = $grossProfit - $commissions - $otherExpenses;

        return view('admin.finance.pl', compact(
            'from',
            'to',
            'sales',
            'returns',
            'commissions',
            'otherExpenses',
            'netSales',
            'cogs',
            'grossProfit',
            'profit'
        ));
    }

    public function vat(Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $entries = LedgerEntry::where('account', 'VAT Payable')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $vatCollected = $entries->sum('credit') - $entries->sum('debit');

        return view('admin.finance.vat', compact('month', 'vatCollected'));
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

        $assetsAccounts = Account::where('type', 'asset')->pluck('name')->all() ?: ['Bank', 'Accounts Receivable'];
        $liabilityAccounts = Account::where('type', 'liability')->pluck('name')->all() ?: ['Accounts Payable', 'VAT Payable'];

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
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $bankAccounts = Account::where('type', 'asset')->where('code', 'like', '1%')->pluck('name')->all();

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
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $invoices = Invoice::with(['order.agent', 'receipts', 'creditNotes'])
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
                ];
            }

            $bucket = &$byAgent[$agentId];

            $gross = ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding;
            $bucket['invoiced'] += $gross;
            $bucket['invoice_count']++;

            if ($invoice->order_id) {
                $bucket['order_ids'][$invoice->order_id] = true;
            }

            $bucket['credits'] += $invoice->creditNotes->sum('amount');

            $bucket['receipts'] += $invoice->receipts
                ->whereBetween('received_at', [$from, $to])
                ->sum('amount');
        }

        $rows = collect($byAgent)->map(function (array $bucket) {
            $netSales = $bucket['invoiced'] - $bucket['credits'];
            $outstanding = $netSales - $bucket['receipts'];

            return [
                'agent' => $bucket['agent'],
                'order_count' => count($bucket['order_ids']),
                'invoice_count' => $bucket['invoice_count'],
                'invoiced' => $bucket['invoiced'],
                'credits' => $bucket['credits'],
                'net_sales' => $netSales,
                'receipts' => $bucket['receipts'],
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
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $production = ProductionRun::with('product')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        // Preload active BOMs (with component standard_cost) for all products
        $productIds = $production->pluck('product_id')->unique()->filter()->all();

        $boms = BillOfMaterial::whereIn('product_id', $productIds)
            ->where('is_active', true)
            ->with(['items.component'])
            ->orderByDesc('id')
            ->get()
            ->keyBy('product_id');

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

        $salesInvoices = Invoice::whereBetween('issued_at', [$from, $to])->get();
        $salesTotal = $salesInvoices->sum(function (Invoice $invoice) {
            return ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding;
        });

        $expensesTotal = Expense::whereBetween('date', [$from, $to])->sum('amount');

        return view('admin.finance.production_summary', [
            'from' => $from,
            'to' => $to,
            'byProduct' => $byProduct,
            'salesTotal' => $salesTotal,
            'expensesTotal' => $expensesTotal,
            'approxProfit' => $salesTotal - $expensesTotal,
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

        $employees = Employee::with(['contracts', 'allowances' => function ($query) use ($from, $to) {
            $query->whereBetween('date', [$from, $to]);
        }])->orderBy('name')->get();

        $rows = $employees->map(function (Employee $employee) use ($from, $to) {
            $contracts = $employee->contracts->filter(function (EmployeeContract $contract) use ($from, $to) {
                if ($contract->status === 'ended') {
                    return false;
                }

                $startsBeforeEnd = $contract->start_date ? $contract->start_date <= $to : true;
                $endsAfterStart = $contract->end_date ? $contract->end_date >= $from : true;

                return $startsBeforeEnd && $endsAfterStart;
            });

            $baseSalary = $contracts->sum('salary_amount');

            $baseTa = $contracts->sum('travel_allowance');
            $baseDa = $contracts->sum('dearness_allowance');
            $baseBonus = $contracts->sum('bonus');

            $allowances = $employee->allowances ?? collect();

            $taAllowances = $allowances->where('type', 'TA')->sum('amount');
            $daAllowances = $allowances->where('type', 'DA')->sum('amount');
            $bonusAllowances = $allowances->where('type', 'BONUS')->sum('amount');

            $totalSalary = $baseSalary;
            $totalTa = $baseTa + $taAllowances;
            $totalDa = $baseDa + $daAllowances;
            $totalBonus = $baseBonus + $bonusAllowances;

            $grandTotal = $totalSalary + $totalTa + $totalDa + $totalBonus;

            return [
                'employee' => $employee,
                'contracts' => $contracts,
                'base_salary' => $baseSalary,
                'base_ta' => $baseTa,
                'base_da' => $baseDa,
                'base_bonus' => $baseBonus,
                'ta_allowances' => $taAllowances,
                'da_allowances' => $daAllowances,
                'bonus_allowances' => $bonusAllowances,
                'total_salary' => $totalSalary,
                'total_ta' => $totalTa,
                'total_da' => $totalDa,
                'total_bonus' => $totalBonus,
                'grand_total' => $grandTotal,
            ];
        })->filter(function (array $row) {
            return $row['grand_total'] > 0;
        });

        $totals = [
            'salary' => $rows->sum('total_salary'),
            'ta' => $rows->sum('total_ta'),
            'da' => $rows->sum('total_da'),
            'bonus' => $rows->sum('total_bonus'),
            'grand' => $rows->sum('grand_total'),
        ];

        return view('admin.finance.payroll_summary', compact('from', 'to', 'rows', 'totals'));
    }
}
