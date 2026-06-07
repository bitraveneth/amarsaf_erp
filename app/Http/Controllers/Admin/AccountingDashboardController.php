<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesDashboardPeriod;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CustomerGift;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\SalaryDistribution;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountingDashboardController extends Controller
{
    use ResolvesDashboardPeriod;

    public function __invoke(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $currencyCode = config('app.currency', 'BDT');

        $invoices = Invoice::query()
            ->select(['id', 'issued_at', 'net_total', 'vat_amount', 'withholding'])
            ->with('creditNotes')
            ->whereBetween('issued_at', [$from, $to])
            ->get();

        $totalInvoices = $invoices->count();
        $netSales = round((float) $invoices->sum(function (Invoice $invoice) use ($from, $to) {
            return $invoice->netSalesAfterCreditsInRange($from, $to);
        }), 2);
        $vatTotal = round((float) $invoices->sum(function (Invoice $invoice) use ($from, $to) {
            return max(0.0, (float) $invoice->vat_amount - $invoice->creditNotesVatTotalInRange($from, $to));
        }), 2);
        $withholdingTotal = $invoices->sum('withholding');
        $collected = Receipt::whereBetween('received_at', [$from, $to])->sum('amount');
        $outstanding = Invoice::query()
            ->with(['receipts', 'creditNotes', 'advanceApplications'])
            ->whereDate('issued_at', '<=', $to->toDateString())
            ->whereIn('status', ['issued', 'adjusted'])
            ->get()
            ->sum(fn (Invoice $invoice) => (float) $invoice->outstandingAsOf($to));

        $expenses = (float) Expense::whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->whereIn('status', [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED, 'paid', 'overdue'])
            ->sum('amount');
        $giftExpenses = (float) CustomerGift::whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->whereIn('status', [CustomerGift::STATUS_GIVEN, 'delivered'])
            ->sum('amount');
        $campaignExpenses = Campaign::where(function ($query) use ($from, $to) {
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
        $totalExpenses = $expenses + $giftExpenses + $campaignExpenses;

        $salaryDistributions = SalaryDistribution::whereBetween('period_start', [$from, $to])->get();
        $totalPayroll = $salaryDistributions->sum(function ($d) {
            return $d->base_salary + $d->bonus + $d->ta_allowances + $d->da_allowances + $d->commission;
        });

        $netProfitEstimate = $netSales - ($totalExpenses + $totalPayroll);
        $collectionRate = $netSales > 0 ? ($collected / $netSales) * 100 : 0;
        $cashGap = $collected - ($totalExpenses + $totalPayroll);

        $chart = \App\Support\DashboardChartBuilder::revenueAndCollectionsSeries($from, $to);

        return view('admin.accounting.dashboard', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
            'totalInvoices' => $totalInvoices,
            'netSales' => $netSales,
            'vatTotal' => $vatTotal,
            'withholdingTotal' => $withholdingTotal,
            'collected' => $collected,
            'outstanding' => $outstanding,
            'totalExpenses' => $totalExpenses,
            'totalPayroll' => $totalPayroll,
            'netProfitEstimate' => $netProfitEstimate,
            'collectionRate' => $collectionRate,
            'cashGap' => $cashGap,
            'chartLabels' => $chart['labels'],
            'chartRevenue' => $chart['values'],
            'chartCollections' => $chart['secondary'],
        ]);
    }
}
