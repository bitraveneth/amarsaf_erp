<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CustomerGift;
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

        $outstanding = $invoices->sum(fn (Invoice $invoice) => (float) $invoice->outstanding);

        // Cost side
        $expenses = (float) Expense::whereBetween('date', [$startOfYear, $endOfYear])
            ->whereIn('status', [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED, 'paid', 'overdue'])
            ->sum('amount');
        $giftExpenses = (float) CustomerGift::whereBetween('date', [$startOfYear, $endOfYear])
            ->whereIn('status', [CustomerGift::STATUS_GIVEN, 'delivered'])
            ->sum('amount');
        $campaignExpenses = Campaign::where(function ($query) use ($startOfYear, $endOfYear) {
            $query->whereBetween('created_at', [$startOfYear, $endOfYear])
                ->orWhereBetween('start_date', [$startOfYear->toDateString(), $endOfYear->toDateString()]);
        })->whereIn('status', [
            Campaign::STATUS_RUNNING,
            Campaign::STATUS_COMPLETED,
            'active',
            'paused',
        ])->sum('cost');
        $totalExpenses = $expenses + $giftExpenses + $campaignExpenses;

        $salaryDistributions = SalaryDistribution::whereBetween('period_start', [$startOfYear, $endOfYear])->get();
        $totalPayroll = $salaryDistributions->sum(function ($d) {
            return $d->base_salary + $d->bonus + $d->ta_allowances + $d->da_allowances + $d->commission;
        });

        $productionQty = ProductionRun::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('qc_status', 'approved')
            ->sum('quantity');

        $activeAgents = Agent::where('is_active', true)->count();

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
