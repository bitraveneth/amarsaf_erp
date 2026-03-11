<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CustomerGift;
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

        $invoices = Invoice::query()
            ->select(['id', 'issued_at', 'net_total', 'vat_amount', 'withholding'])
            ->withSum('receipts', 'amount')
            ->withSum('creditNotes', 'amount')
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
            $credited = (float) ($invoice->credit_notes_sum_amount ?? 0);
            $paid = (float) ($invoice->receipts_sum_amount ?? 0);

            return max($cashTotal - $credited - $paid, 0);
        });

        $expenses = (float) Expense::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED, 'paid', 'overdue'])
            ->sum('amount');
        $giftExpenses = (float) CustomerGift::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', [CustomerGift::STATUS_GIVEN, 'delivered'])
            ->sum('amount');
        $campaignExpenses = Campaign::where(function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->orWhereBetween('start_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
        })->whereIn('status', [
            Campaign::STATUS_RUNNING,
            Campaign::STATUS_COMPLETED,
            'active',
            'paused',
        ])->sum('cost');
        $totalExpenses = $expenses + $giftExpenses + $campaignExpenses;

        $salaryDistributions = SalaryDistribution::whereBetween('period_start', [$startOfMonth, $endOfMonth])->get();
        $totalPayroll = $salaryDistributions->sum(function ($d) {
            return $d->base_salary + $d->bonus + $d->ta_allowances + $d->da_allowances + $d->commission;
        });

        $netProfitEstimate = $netSales - ($totalExpenses + $totalPayroll);

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
