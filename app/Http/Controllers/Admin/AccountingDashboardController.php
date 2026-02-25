<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\SalaryDistribution;
use Carbon\Carbon;

class AccountingDashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $invoices = Invoice::with(['receipts', 'creditNotes'])
            ->whereBetween('issued_at', [$startOfMonth, $endOfMonth])
            ->get();

        $totalInvoices = $invoices->count();
        $netSales = $invoices->sum('net_total');
        $vatTotal = $invoices->sum('vat_amount');
        $withholdingTotal = $invoices->sum('withholding');

        $collected = Receipt::whereBetween('received_at', [$startOfMonth, $endOfMonth])->sum('amount');

        $outstanding = $invoices->sum(function (Invoice $invoice) {
            $grossTotal = $invoice->net_total + $invoice->vat_amount;
            $cashTotal = $grossTotal - $invoice->withholding;
            $credited = $invoice->creditNotes->sum('amount');
            $paid = $invoice->receipts->sum('amount');

            return max($cashTotal - $credited - $paid, 0);
        });

        $expenses = Expense::whereBetween('date', [$startOfMonth, $endOfMonth])->get();
        $totalExpenses = $expenses->sum('amount');

        $salaryDistributions = SalaryDistribution::whereBetween('period_start', [$startOfMonth, $endOfMonth])->get();
        $totalPayroll = $salaryDistributions->sum(function ($d) {
            return $d->base_salary + $d->bonus + $d->ta_allowances + $d->da_allowances + $d->commission;
        });

        $netProfitEstimate = ($netSales + $vatTotal - $withholdingTotal) - ($totalExpenses + $totalPayroll);

        return view('admin.accounting.dashboard', [
            'periodLabel' => $startOfMonth->format('d M Y') . ' – ' . $endOfMonth->format('d M Y'),
            'totalInvoices' => $totalInvoices,
            'netSales' => $netSales,
            'vatTotal' => $vatTotal,
            'withholdingTotal' => $withholdingTotal,
            'collected' => $collected,
            'outstanding' => $outstanding,
            'totalExpenses' => $totalExpenses,
            'totalPayroll' => $totalPayroll,
            'netProfitEstimate' => $netProfitEstimate,
        ]);
    }
}

