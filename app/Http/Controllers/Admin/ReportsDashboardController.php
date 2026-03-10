<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\ProductionRun;
use App\Models\SalaryDistribution;
use App\Models\Agent;
use Carbon\Carbon;

class ReportsDashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();
        $startOfYear = $today->copy()->startOfYear();
        $endOfYear = $today->copy()->endOfYear();

        // Revenue side
        $invoices = Invoice::with(['receipts', 'creditNotes'])
            ->whereBetween('issued_at', [$startOfYear, $endOfYear])
            ->get();

        $grossRevenue = $invoices->sum('net_total');
        $withholdingTotal = $invoices->sum('withholding');

        $totalCollections = $invoices->flatMap->receipts->sum('amount');

        $outstanding = $invoices->sum(function (Invoice $invoice) {
            $gross = $invoice->net_total + $invoice->vat_amount;
            $cashTotal = $gross - $invoice->withholding;
            $credited = $invoice->creditNotes->sum('amount');
            $paid = $invoice->receipts->sum('amount');

            return max($cashTotal - $credited - $paid, 0);
        });

        // Cost side
        $expenses = Expense::whereBetween('date', [$startOfYear, $endOfYear])->get();
        $totalExpenses = $expenses->sum('amount');

        $salaryDistributions = SalaryDistribution::whereBetween('period_start', [$startOfYear, $endOfYear])->get();
        $totalPayroll = $salaryDistributions->sum(function ($d) {
            return $d->base_salary + $d->bonus + $d->ta_allowances + $d->da_allowances + $d->commission;
        });

        $productionQty = ProductionRun::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('qc_status', 'approved')
            ->sum('quantity');

        $activeAgents = Agent::count();

        $netProfitEstimate = $grossRevenue - ($totalExpenses + $totalPayroll);

        return view('admin.reports.dashboard', [
            'yearLabel'          => $startOfYear->format('Y'),
            'grossRevenue'       => $grossRevenue,
            'withholdingTotal'   => $withholdingTotal,
            'totalCollections'   => $totalCollections,
            'outstanding'        => $outstanding,
            'totalExpenses'      => $totalExpenses,
            'totalPayroll'       => $totalPayroll,
            'netProfitEstimate'  => $netProfitEstimate,
            'productionQty'      => $productionQty,
            'activeAgents'       => $activeAgents,
        ]);
    }
}
