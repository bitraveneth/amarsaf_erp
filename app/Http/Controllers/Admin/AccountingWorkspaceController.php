<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesDashboardPeriod;
use App\Http\Controllers\Controller;
use App\Models\AgentAdvance;
use App\Models\AgentAdvanceApplication;
use App\Models\AgentCommissionSettlement;
use App\Models\BillPayment;
use App\Models\Campaign;
use App\Models\CreditNote;
use App\Models\CustomerGift;
use App\Models\Expense;
use App\Models\FleetExpense;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\LogisticsBill;
use App\Models\LogisticsBillPayment;
use App\Models\ProductionRun;
use App\Models\PurchaseBill;
use App\Models\Receipt;
use App\Models\SalaryDistribution;
use App\Models\StockMovement;
use App\Services\Accounting\LedgerGapReport;
use Illuminate\Http\Request;

class AccountingWorkspaceController extends Controller
{
    use ResolvesDashboardPeriod;

    public function gaps(LedgerGapReport $report)
    {
        $sections = $report->sections();
        $missingCount = array_sum(array_column($sections, 'count'));
        $gapAmount = array_sum(array_map(fn (array $section) => (float) $section['amount'], $sections));

        return view('admin.finance.ledger-gaps', [
            'sections' => $sections,
            'missingCount' => $missingCount,
            'gapAmount' => $gapAmount,
            'currency' => config('app.currency', 'BDT'),
        ]);
    }

    public function activity(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request, 'month');
        if ($range === 'month') {
            $to = $from->copy()->endOfMonth();
        } elseif ($range === 'quarter') {
            $to = $from->copy()->endOfQuarter();
        } elseif ($range === 'year') {
            $to = $from->copy()->endOfYear();
        }
        $periodLabel = $this->dashboardPeriodLabel($from, $to);

        $query = JournalEntry::query()
            ->with(['lines.account', 'postedByUser'])
            ->whereBetween('entry_date', [$from->toDateString(), $to->toDateString()])
            ->when($request->query('type'), fn ($entries, $type) => $entries->where('journal_type', $type));

        $summary = [
            'events' => (clone $query)->count(),
            'posted' => (clone $query)->where('status', 'posted')->count(),
            'drafts' => (clone $query)->where('status', 'draft')->count(),
        ];

        $entries = (clone $query)
            ->latest('posted_at')
            ->latest('entry_date')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $types = JournalEntry::query()
            ->whereBetween('entry_date', [$from->toDateString(), $to->toDateString()])
            ->whereNotNull('journal_type')
            ->distinct()
            ->orderBy('journal_type')
            ->pluck('journal_type');

        return view('admin.finance.accounting-activity', [
            'entries' => $entries,
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $periodLabel,
            'summary' => $summary,
            'types' => $types,
            'currency' => config('app.currency', 'BDT'),
            'sourceLabels' => $this->sourceLabels(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function sourceLabels(): array
    {
        return [
            Expense::class => 'Expense',
            PurchaseBill::class => 'Purchase bill',
            BillPayment::class => 'Bill payment',
            Invoice::class => 'Customer invoice',
            Receipt::class => 'Receipt',
            CreditNote::class => 'Credit note',
            SalaryDistribution::class => 'Payroll',
            FleetExpense::class => 'Fleet expense',
            LogisticsBill::class => 'Logistics bill',
            LogisticsBillPayment::class => 'Logistics payment',
            AgentAdvance::class => 'Agent advance',
            AgentAdvanceApplication::class => 'Advance applied',
            AgentCommissionSettlement::class => 'Commission',
            GoodsReceipt::class => 'Goods receipt',
            ProductionRun::class => 'Production',
            StockMovement::class => 'Stock movement',
            CustomerGift::class => 'Customer gift',
            Campaign::class => 'Campaign',
            JournalEntry::class => 'Manual journal',
        ];
    }
}
