<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Campaign;
use App\Models\CustomerGift;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LogisticsBill;
use App\Services\ProductionVarianceService;
use App\Models\LedgerEntry;
use App\Models\ProductionRun;
use App\Models\PurchaseBill;
use App\Models\SalaryDistribution;
use App\Support\ExportDateRange;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinancialReportExportService
{
    public function __construct(
        protected HierarchicalReportService $reports,
        protected IncomeStatementPresenter $presenter
    ) {
    }

    public function profitAndLoss(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $currencyCode = config('app.currency', 'BDT');
        $statement = $this->reports->profitAndLoss($from, $to);
        $periodLabel = $from->format('d M Y') . ' – ' . $to->format('d M Y');

        $presentation = $this->presenter->present(
            $statement,
            $periodLabel,
            $currencyCode,
            $from,
            $to
        );

        $rows = collect([
            ['Income statement', 'Amount (' . $currencyCode . ')', '% of income'],
            ['Period', $periodLabel, ''],
            ['', '', ''],
        ]);

        foreach ($presentation['rows'] as $row) {
            $indent = str_repeat('  ', (int) ($row['indent'] ?? 0));
            $label = $indent . ($row['code'] ? $row['code'] . ' ' : '') . ($row['label'] ?? '');

            $rows->push([
                $label,
                $row['amount_display'] ?? '',
                $row['pct_display'] ?? '',
            ]);
        }

        return $rows->values();
    }

    public function trialBalance(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);

        $accounts = Account::orderBy('code')->get();
        $rows = collect();

        foreach ($accounts as $account) {
            $openingDebit = $this->accountDebitTotal($account->id, null, $from->copy()->subDay()->endOfDay());
            $openingCredit = $this->accountCreditTotal($account->id, null, $from->copy()->subDay()->endOfDay());
            $periodDebit = $this->accountDebitTotal($account->id, $from, $to);
            $periodCredit = $this->accountCreditTotal($account->id, $from, $to);
            $balance = round($openingDebit + $periodDebit - ($openingCredit + $periodCredit), 2);

            if (
                abs($openingDebit) < 0.01 && abs($openingCredit) < 0.01
                && abs($periodDebit) < 0.01 && abs($periodCredit) < 0.01
            ) {
                continue;
            }

            $openingBalance = round($openingDebit - $openingCredit, 2);
            $rows->push([
                $account->code,
                $account->name,
                $this->money($openingBalance > 0 ? $openingBalance : 0),
                $this->money($openingBalance < 0 ? abs($openingBalance) : 0),
                $this->money($periodDebit),
                $this->money($periodCredit),
                $this->money($balance > 0 ? $balance : 0),
                $this->money($balance < 0 ? abs($balance) : 0),
            ]);
        }

        return $rows->values();
    }

    public function generalLedger(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);

        $lines = JournalEntryLine::query()
            ->with(['journalEntry', 'account'])
            ->whereHas('journalEntry', function ($query) use ($from, $to) {
                $query->where('status', 'posted')
                    ->whereDate('entry_date', '>=', $from->toDateString())
                    ->whereDate('entry_date', '<=', $to->toDateString());
            })
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->orderBy('accounts.code')
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->orderBy('journal_entry_lines.line_number')
            ->select('journal_entry_lines.*')
            ->get();

        return $lines->map(function (JournalEntryLine $line) {
            return [
                $line->account?->code ?? '—',
                $line->account?->name ?? '—',
                optional($line->journalEntry?->entry_date)->format('Y-m-d') ?? '—',
                $line->journalEntry?->number ?? $line->journalEntry?->id,
                $line->description ?: ($line->journalEntry?->description ?? ''),
                $this->money((float) $line->debit),
                $this->money((float) $line->credit),
            ];
        })->values();
    }

    public function receivableAging(Request $request): Collection
    {
        $asOf = $this->resolveAsOf($request);
        $report = app(AgingReportService::class)->receivableAging($asOf);
        $bucketLabels = AgingReportService::BUCKET_LABELS;

        $rows = collect($report['rows'] ?? [])->map(function (array $row) use ($bucketLabels) {
            $invoice = $row['invoice'];

            return [
                $invoice->number ?? $invoice->id,
                $row['agent']?->name ?? '—',
                optional($row['due_at'])->format('Y-m-d') ?? '—',
                (string) ($row['days_past_due'] ?? 0),
                $bucketLabels[$row['bucket']] ?? $row['bucket'],
                $this->money($row['outstanding']),
            ];
        });

        $rows->push(['', '', '', '', '', '']);
        $rows->push(['As of', $asOf->format('d M Y'), '', '', 'Total outstanding', $this->money($report['subledgerTotal'] ?? 0)]);

        return $rows->values();
    }

    public function payableAging(Request $request): Collection
    {
        $asOf = $this->resolveAsOf($request);
        $report = app(AgingReportService::class)->payableAging($asOf);
        $bucketLabels = AgingReportService::BUCKET_LABELS;

        $rows = collect($report['rows'] ?? [])->map(function (array $row) use ($bucketLabels) {
            $bill = $row['bill'];

            return [
                $bill->number ?? $bill->id,
                $row['supplier']?->name ?? '—',
                optional($row['due_at'])->format('Y-m-d') ?? '—',
                (string) ($row['days_past_due'] ?? 0),
                $bucketLabels[$row['bucket']] ?? $row['bucket'],
                $this->money($row['outstanding']),
            ];
        });

        $rows->push(['', '', '', '', '', '']);
        $rows->push(['As of', $asOf->format('d M Y'), '', '', 'Total outstanding', $this->money($report['subledgerTotal'] ?? 0)]);

        return $rows->values();
    }

    public function vatReport(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);

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

        $rows = collect();

        foreach ($invoices as $invoice) {
            $taxable = $invoice->netSalesAfterCreditsInRange($from, $to);
            $vat = max(0.0, (float) $invoice->vat_amount - $invoice->creditNotesVatTotalInRange($from, $to));
            $rate = $taxable > 0 ? round(($vat / $taxable) * 100, 2) : 0;

            $rows->push([
                'Output VAT',
                $invoice->issued_at?->format('Y-m-d'),
                $invoice->number ?? $invoice->id,
                $invoice->order?->agent?->name ?? '—',
                $this->money($taxable),
                $this->money($vat),
                $this->money($rate),
            ]);
        }

        foreach ($purchaseBills as $bill) {
            $rate = (float) $bill->net_total > 0
                ? round(((float) $bill->vat_amount / (float) $bill->net_total) * 100, 2)
                : 0;

            $rows->push([
                'Input VAT',
                $bill->bill_date?->format('Y-m-d'),
                $bill->number ?? $bill->id,
                $bill->supplier?->name ?? '—',
                $this->money((float) $bill->net_total),
                $this->money((float) $bill->vat_amount),
                $this->money($rate),
            ]);
        }

        return $rows->values();
    }

    public function balanceSheet(Request $request): Collection
    {
        $asOf = $this->resolveAsOf($request);
        $endOfDay = $asOf->copy()->endOfDay();

        $entries = LedgerEntry::where('created_at', '<=', $endOfDay)->get();

        $balances = [];
        foreach ($entries as $entry) {
            $account = $entry->account;
            if (! isset($balances[$account])) {
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

        $rows = collect([
            ['Section', 'Account', 'Amount'],
            ['Assets', '', ''],
        ]);

        foreach ($assets as $account => $amount) {
            $rows->push(['Assets', $account, $this->money($amount)]);
        }

        $rows->push(['', 'Total assets', $this->money($totalAssets)]);
        $rows->push(['Liabilities', '', '']);

        foreach ($liabilities as $account => $amount) {
            $rows->push(['Liabilities', $account, $this->money($amount)]);
        }

        $rows->push(['', 'Total liabilities', $this->money($totalLiabilities)]);
        $rows->push(['Equity', 'Owner equity', $this->money($equity)]);
        $rows->push(['', '', '']);
        $rows->push(['As of', $asOf->format('d M Y'), '']);

        return $rows->values();
    }

    public function cashFlow(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);

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

        $cashIn = (float) $entries->sum('debit');
        $cashOut = (float) $entries->sum('credit');
        $net = $cashIn - $cashOut;

        $rows = collect([
            ['Line', 'Amount'],
            ['Cash inflows', $this->money($cashIn)],
            ['Cash outflows', $this->money($cashOut)],
            ['Net cash movement', $this->money($net)],
            ['', ''],
            ['Period', $from->format('d M Y') . ' – ' . $to->format('d M Y')],
            ['Accounts', implode(', ', $bankAccounts ?: ['Bank'])],
        ]);

        foreach ($entries->groupBy('account') as $account => $group) {
            $in = (float) $group->sum('debit');
            $out = (float) $group->sum('credit');
            $rows->push([$account . ' (in)', $this->money($in)]);
            $rows->push([$account . ' (out)', $this->money($out)]);
        }

        return $rows->values();
    }

    public function outstandingInvoices(Request $request): Collection
    {
        $asOf = $this->resolveAsOf($request);

        return Invoice::with(['order.agent', 'receipts', 'creditNotes', 'advanceApplications'])
            ->whereIn('status', ['issued', 'adjusted'])
            ->get()
            ->filter(fn (Invoice $invoice) => $invoice->outstanding > 0.00001)
            ->sortByDesc(fn (Invoice $invoice) => $invoice->outstanding)
            ->values()
            ->map(function (Invoice $invoice) use ($asOf) {
                $dueDate = $invoice->due_at ?? $invoice->issued_at;
                $daysPastDue = app(AgingReportService::class)->daysPastDue($dueDate, $asOf);
                $gross = ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding;

                return [
                    $invoice->number ?? $invoice->id,
                    $invoice->order?->agent?->name ?? '—',
                    $invoice->issued_at?->format('Y-m-d') ?? '—',
                    $dueDate?->format('Y-m-d') ?? '—',
                    (string) max(0, $daysPastDue),
                    $this->money($gross),
                    $this->money(max(0.0, $gross - (float) $invoice->outstanding)),
                    $this->money((float) $invoice->outstanding),
                    ucfirst((string) $invoice->status),
                ];
            });
    }

    public function outstandingBills(Request $request): Collection
    {
        $asOf = $this->resolveAsOf($request);

        return PurchaseBill::with(['supplier', 'payments'])
            ->whereIn('status', ['open', 'part_paid'])
            ->get()
            ->filter(fn (PurchaseBill $bill) => $bill->outstanding > 0.00001)
            ->sortByDesc(fn (PurchaseBill $bill) => $bill->outstanding)
            ->values()
            ->map(function (PurchaseBill $bill) use ($asOf) {
                $dueDate = $bill->due_date ?? $bill->bill_date;
                $daysPastDue = app(AgingReportService::class)->daysPastDue($dueDate, $asOf);

                return [
                    $bill->number ?? $bill->id,
                    $bill->supplier?->name ?? '—',
                    $bill->bill_date?->format('Y-m-d') ?? '—',
                    $dueDate?->format('Y-m-d') ?? '—',
                    (string) max(0, $daysPastDue),
                    $this->money((float) $bill->net_total),
                    $this->money((float) $bill->paid_total),
                    $this->money((float) $bill->outstanding),
                    ucfirst(str_replace('_', ' ', (string) $bill->status)),
                ];
            });
    }

    public function salesRegister(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);

        return Invoice::with(['order.agent'])
            ->whereDate('issued_at', '>=', $from->toDateString())
            ->whereDate('issued_at', '<=', $to->toDateString())
            ->orderBy('issued_at')
            ->get()
            ->map(function (Invoice $invoice) {
                $gross = ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding;

                return [
                    $invoice->issued_at?->format('Y-m-d') ?? '—',
                    $invoice->number ?? $invoice->id,
                    $invoice->order?->number ?? $invoice->order_id ?? '—',
                    $invoice->order?->agent?->name ?? '—',
                    $this->money((float) $invoice->net_total),
                    $this->money((float) $invoice->vat_amount),
                    $this->money((float) $invoice->withholding),
                    $this->money($gross),
                    ucfirst((string) $invoice->status),
                ];
            })
            ->values();
    }

    public function expenseSummary(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $data = app(OperationalReportService::class)->expenseSummary($from, $to);

        return $data['rows']->map(fn (array $row) => [
            $row['code'],
            $row['name'],
            (string) $row['line_count'],
            $this->money($row['total']),
        ]);
    }

    public function utilitiesReport(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $data = app(OperationalReportService::class)->utilitiesReport($from, $to);

        return collect($data['lines'] ?? [])->map(fn (Expense $expense) => [
            $expense->date?->format('Y-m-d') ?? '—',
            $expense->description ?? '—',
            $expense->reference ?? '—',
            $this->money((float) $expense->amount),
            ucfirst((string) $expense->status),
        ]);
    }

    public function logisticsBills(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $data = app(OperationalReportService::class)->logisticsBillsSummary($from, $to);

        return collect($data['bills'] ?? [])->map(fn (LogisticsBill $bill) => [
            $bill->bill_date?->format('Y-m-d') ?? '—',
            $bill->number ?? $bill->id,
            $bill->transportCarrier?->name ?? '—',
            $this->money((float) $bill->net_total + (float) $bill->vat_amount),
            $this->money((float) $bill->outstanding),
            ucfirst(str_replace('_', ' ', (string) $bill->status)),
        ]);
    }

    public function routeCosts(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $data = app(OperationalReportService::class)->routeCosts($from, $to);

        return $data['rows']->map(fn (array $row) => [
            $row['route']->name ?? '—',
            $this->money($row['fleet_cost']),
            $this->money($row['carrier_cost']),
            $this->money($row['logistics_cost']),
            $this->money($row['zone_revenue']),
            $this->money($row['margin_after_logistics']),
        ]);
    }

    public function fleetExpenses(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $data = app(OperationalReportService::class)->fleetExpenseSummary($from, $to);

        return $data['rows']->map(fn (array $row) => [
            $row['label'],
            (string) $row['count'],
            $this->money($row['total']),
        ]);
    }

    public function commissionSummary(Request $request): Collection
    {
        $data = app(OperationalReportService::class)->commissionSummary($request);

        return $data['rows']->map(fn (array $row) => [
            $row['agent']->code ?? $row['agent']->id,
            $row['agent']->name ?? '—',
            $this->money($row['sales']),
            $this->money($row['commission']),
            $this->money($row['rate']),
        ]);
    }

    public function salesTargets(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $data = app(OperationalReportService::class)->salesTargets($from, $to);

        return $data['rows']->map(fn (array $row) => [
            $row['name'],
            $this->money($row['target_amount']),
            $this->money($row['achieved']),
            $this->money($row['gap']),
            $this->money($row['progress']),
        ]);
    }

    public function lowStock(Request $request): Collection
    {
        $data = app(OperationalReportService::class)->lowStock();

        return $data['rows']->map(fn (array $row) => [
            $row['product']->sku ?? '—',
            $row['product']->name ?? '—',
            $this->money($row['available']),
            $this->money($row['reorder_level']),
            $this->money($row['gap']),
        ]);
    }

    public function deliveryPerformance(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $data = app(OperationalReportService::class)->deliveryPerformance($from, $to);

        return collect($data['deliveries'] ?? [])->map(fn ($delivery) => [
            $delivery->created_at?->format('Y-m-d') ?? '—',
            $delivery->order?->number ?? $delivery->order_id,
            $delivery->order?->agent?->name ?? '—',
            $delivery->route?->name ?? '—',
            ucfirst((string) $delivery->status),
        ]);
    }

    public function bankReconciliation(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $data = app(OperationalReportService::class)->bankReconciliationSummary($from, $to);
        $rows = collect();

        foreach ($data['receipts'] as $receipt) {
            $rows->push([
                'Receipt',
                $receipt->received_at?->format('Y-m-d') ?? '—',
                $receipt->invoice?->number ?? '—',
                $receipt->invoice?->order?->agent?->name ?? '—',
                $this->money((float) $receipt->amount),
            ]);
        }

        foreach ($data['payments'] as $payment) {
            $rows->push([
                'Payment',
                $payment->paid_at?->format('Y-m-d') ?? '—',
                $payment->bill?->number ?? '—',
                $payment->bill?->supplier?->name ?? '—',
                $this->money((float) $payment->amount),
            ]);
        }

        return $rows->values();
    }

    public function journalRegister(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);

        return JournalEntry::query()
            ->withSum('lines as debit_total', 'debit')
            ->withSum('lines as credit_total', 'credit')
            ->where('status', 'posted')
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString())
            ->orderBy('entry_date')
            ->get()
            ->map(fn (JournalEntry $entry) => [
                $entry->entry_date?->format('Y-m-d') ?? '—',
                $entry->number ?? $entry->id,
                $entry->description ?? '—',
                $this->money((float) ($entry->debit_total ?? 0)),
                $this->money((float) ($entry->credit_total ?? 0)),
            ]);
    }

    public function customerStatement(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $agentId = $request->integer('agent_id') ?: null;
        $data = app(OperationalReportService::class)->customerStatement($from, $to, $agentId);

        return $data['rows']->map(fn (array $row) => [
            $row['date'] instanceof Carbon ? $row['date']->format('Y-m-d') : '—',
            $row['type'],
            $row['reference'],
            $this->money($row['debit']),
            $this->money($row['credit']),
        ]);
    }

    public function supplierStatement(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $supplierId = $request->integer('supplier_id') ?: null;
        $data = app(OperationalReportService::class)->supplierStatement($from, $to, $supplierId);

        return $data['rows']->map(fn (array $row) => [
            $row['date'] instanceof Carbon ? $row['date']->format('Y-m-d') : '—',
            $row['type'],
            $row['reference'],
            $this->money($row['debit']),
            $this->money($row['credit']),
        ]);
    }

    public function executiveSummary(Request $request): Collection
    {
        return $this->profitAndLoss($request);
    }

    public function productionSummary(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $runs = ProductionRun::with('product')
            ->whereBetween('created_at', [$from, $to])
            ->where('qc_status', 'approved')
            ->get()
            ->groupBy('product_id');

        return $runs->map(function (Collection $group) {
            $product = $group->first()->product;

            return [
                $product?->name ?? '—',
                (string) $group->count(),
                $this->money((float) $group->sum('quantity')),
                $this->money((float) $group->avg('material_unit_cost')),
            ];
        })->values();
    }

    public function productionVariance(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $rows = app(ProductionVarianceService::class)->report($from, $to);

        return $rows->map(fn (array $row) => [
            $row['product_name'] ?? '—',
            $row['run_id'] ?? '—',
            $this->money($row['standard_unit_cost'] ?? 0),
            $this->money($row['actual_unit_cost'] ?? 0),
            $this->money($row['variance_total'] ?? 0),
        ]);
    }

    public function payrollSummary(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);

        return SalaryDistribution::with('employee')
            ->whereBetween('period_start', [$from, $to])
            ->get()
            ->map(fn (SalaryDistribution $row) => [
                $row->employee?->name ?? '—',
                $this->money((float) $row->base_salary),
                $this->money((float) $row->commission),
                $this->money((float) $row->bonus),
                $this->money((float) $row->base_salary + (float) $row->bonus + (float) $row->ta_allowances + (float) $row->da_allowances + (float) $row->commission),
            ]);
    }

    public function agentPerformance(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $invoices = Invoice::with(['order.agent', 'receipts', 'creditNotes'])
            ->whereBetween('issued_at', [$from, $to])
            ->get();

        return $invoices
            ->filter(fn (Invoice $invoice) => $invoice->order?->agent)
            ->groupBy(fn (Invoice $invoice) => $invoice->order->agent_id)
            ->map(function (Collection $group) use ($from, $to) {
                $agent = $group->first()->order->agent;
                $sales = $group->sum(fn (Invoice $invoice) => $invoice->netSalesAfterCreditsInRange($from, $to));
                $collections = $group->sum(fn (Invoice $invoice) => $invoice->receiptsTotalInRange($from, $to));

                return [
                    $agent->name ?? '—',
                    $this->money($sales),
                    $this->money($collections),
                    $this->money(max(0, $sales - $collections)),
                ];
            })
            ->values();
    }

    public function inventoryValuation(Request $request): Collection
    {
        $summary = app(InventoryCostingService::class)->valuationSummary();

        return collect($summary['rows'] ?? [])->map(fn (array $line) => [
            $line['product']?->name ?? '—',
            $line['warehouse']?->name ?? '—',
            $this->money($line['quantity'] ?? 0),
            $this->money($line['total_value'] ?? 0),
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolvePeriod(Request $request): array
    {
        $resolved = ExportDateRange::resolve($request);

        if ($resolved && $resolved['from'] && $resolved['to']) {
            return [$resolved['from']->copy()->startOfDay(), $resolved['to']->copy()->endOfDay()];
        }

        $now = now();

        return [$now->copy()->startOfMonth(), $now->copy()->endOfDay()];
    }

    protected function resolveAsOf(Request $request): Carbon
    {
        $resolved = ExportDateRange::resolve($request);

        if ($resolved && $resolved['to']) {
            return $resolved['to']->copy()->endOfDay();
        }

        if ($resolved && $resolved['from']) {
            return $resolved['from']->copy()->endOfDay();
        }

        return now()->endOfDay();
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

    protected function payrollTotal(Carbon $from, Carbon $to): float
    {
        $salariesAccount = Account::query()->where('slug', 'salaries_wages')->value('id');

        if ($salariesAccount) {
            $posted = $this->accountDebitTotal((int) $salariesAccount, $from, $to);

            if ($posted > 0) {
                return $posted;
            }
        }

        return (float) SalaryDistribution::whereBetween('period_start', [$from, $to])
            ->get()
            ->sum(fn (SalaryDistribution $distribution) => (float) $distribution->base_salary
                + (float) $distribution->bonus
                + (float) $distribution->ta_allowances
                + (float) $distribution->da_allowances
                + (float) $distribution->commission);
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

    protected function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
