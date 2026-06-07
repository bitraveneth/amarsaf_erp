<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesDashboardPeriod;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Campaign;
use App\Models\CustomerGift;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\ProductionRun;
use App\Models\SalaryDistribution;
use App\Support\DashboardChartBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsDashboardController extends Controller
{
    use ResolvesDashboardPeriod;

    public function __invoke(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request, 'year');
        $currencyCode = config('app.currency', 'BDT');

        $invoices = Invoice::with(['receipts', 'creditNotes', 'advanceApplications', 'order.agent'])
            ->whereBetween('issued_at', [$from, $to])
            ->get();

        $grossRevenue = round((float) $invoices->sum(function (Invoice $invoice) use ($from, $to) {
            return $invoice->netSalesAfterCreditsInRange($from, $to);
        }), 2);

        $withholdingTotal = $invoices->sum('withholding');

        $totalCollections = round((float) $invoices->sum(function (Invoice $invoice) use ($from, $to) {
            return $invoice->receiptsTotalInRange($from, $to);
        }), 2);

        $outstanding = round((float) $invoices->sum(function (Invoice $invoice) use ($to) {
            return $invoice->outstandingAsOf($to);
        }), 2);

        $expenses = (float) Expense::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('status', [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED, 'paid', 'overdue'])
            ->sum('amount');
        $giftExpenses = (float) CustomerGift::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('status', [CustomerGift::STATUS_GIVEN, 'delivered'])
            ->sum('amount');
        $campaignExpenses = Campaign::where(function ($query) use ($from, $to) {
            $query->whereBetween('created_at', [$from, $to])
                ->orWhereBetween('start_date', [$from->toDateString(), $to->toDateString()]);
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

        $commissionsTotal = (float) LedgerEntry::where('account', 'Commission Expense')
            ->whereBetween('created_at', [$from, $to])
            ->sum('debit');

        $cogsEstimate = $this->estimateCogs($from, $to);
        $productionQty = ProductionRun::whereBetween('created_at', [$from, $to])
            ->where('qc_status', 'approved')
            ->sum('quantity');
        $activeAgents = Agent::where('is_active', true)->count();
        $netProfitEstimate = $grossRevenue - ($cogsEstimate + $commissionsTotal + $totalExpenses + $totalPayroll);
        $collectionRate = $grossRevenue > 0 ? ($totalCollections / $grossRevenue) * 100 : 0;

        $chart = DashboardChartBuilder::revenueAndCollectionsSeries($from, $to);

        $topAgents = DashboardChartBuilder::normalizeRankList(
            $invoices
                ->filter(fn (Invoice $invoice) => $invoice->order?->agent)
                ->groupBy(fn (Invoice $invoice) => (string) $invoice->order->agent_id)
                ->map(function ($group) use ($from, $to) {
                    $agent = $group->first()->order->agent;

                    return [
                        'label' => $agent->name ?? 'Unknown agent',
                        'value' => $group->sum(fn (Invoice $invoice) => $invoice->netSalesAfterCreditsInRange($from, $to)),
                        'meta' => $agent->zone ?? null,
                    ];
                })
                ->values()
        );

        $costChart = DashboardChartBuilder::costBreakdownSeries([
            ['label' => 'COGS', 'value' => $cogsEstimate],
            ['label' => 'Commissions', 'value' => $commissionsTotal],
            ['label' => 'Operating', 'value' => $totalExpenses],
            ['label' => 'Payroll', 'value' => $totalPayroll],
        ]);

        return view('admin.reports.dashboard', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
            'grossRevenue' => $grossRevenue,
            'withholdingTotal' => $withholdingTotal,
            'totalCollections' => $totalCollections,
            'outstanding' => $outstanding,
            'cogsEstimate' => $cogsEstimate,
            'commissionsTotal' => $commissionsTotal,
            'totalExpenses' => $totalExpenses,
            'totalPayroll' => $totalPayroll,
            'netProfitEstimate' => $netProfitEstimate,
            'productionQty' => $productionQty,
            'activeAgents' => $activeAgents,
            'collectionRate' => $collectionRate,
            'chartLabels' => $chart['labels'],
            'chartRevenue' => $chart['values'],
            'chartCollections' => $chart['secondary'],
            'topAgents' => $topAgents,
            'costChartLabels' => $costChart['labels'],
            'costChartValues' => $costChart['values'],
        ]);
    }

    protected function estimateCogs(Carbon $from, Carbon $to): float
    {
        $invoiceItems = InvoiceItem::whereHas('invoice', function ($query) use ($from, $to) {
            $query
                ->whereDate('issued_at', '>=', $from->toDateString())
                ->whereDate('issued_at', '<=', $to->toDateString());
        })->with('product')->get();

        $cogs = 0.0;

        foreach ($invoiceItems as $item) {
            if (! $item->product_id) {
                continue;
            }

            $unitCost = (float) ($item->product?->standard_cost ?? 0);

            if ($unitCost <= 0) {
                $unitCost = (float) (ProductionRun::where('product_id', $item->product_id)
                    ->whereNotNull('material_unit_cost')
                    ->avg('material_unit_cost') ?? 0);
            }

            if ($unitCost <= 0) {
                continue;
            }

            $cogs += $unitCost * (float) $item->quantity;
        }

        return round($cogs, 2);
    }
}
