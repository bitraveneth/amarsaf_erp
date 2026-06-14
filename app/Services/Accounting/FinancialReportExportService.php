<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Campaign;
use App\Models\CustomerGift;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntryLine;
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
    public function profitAndLoss(Request $request): Collection
    {
        [$from, $to] = $this->resolvePeriod($request);
        $currencyCode = config('app.currency', 'BDT');

        $entries = LedgerEntry::whereBetween('created_at', [$from, $to])->get();

        $sales = (float) $entries->where('account', 'Sales Revenue')->sum('credit');
        $returns = (float) $entries->where('account', 'Sales Returns')->sum('debit');
        $commissions = (float) $entries->where('account', 'Commission Expense')->sum('debit');
        $otherExpenses = $this->operatingExpensesTotal($from, $to);
        $payroll = $this->payrollTotal($from, $to);

        $netSales = $sales - $returns;

        $invoiceItems = InvoiceItem::with(['invoice', 'product'])
            ->whereHas('invoice', function ($q) use ($from, $to) {
                $q->whereDate('issued_at', '>=', $from->toDateString())
                    ->whereDate('issued_at', '<=', $to->toDateString());
            })
            ->get();

        $costByProduct = ProductionRun::whereNotNull('material_unit_cost')
            ->get()
            ->groupBy('product_id')
            ->map(fn ($group) => (float) $group->avg('material_unit_cost'));

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
        $grossProfit = $netSales - $cogs;
        $profit = $grossProfit - $commissions - $otherExpenses - $payroll;

        $rows = collect([
            ['Sales revenue', $this->money($sales)],
            ['Sales returns', $this->money($returns)],
            ['Net sales', $this->money($netSales)],
            ['Cost of goods sold', $this->money($cogs)],
            ['Gross profit', $this->money($grossProfit)],
            ['Commission expense', $this->money($commissions)],
            ['Operating expenses', $this->money($otherExpenses)],
            ['Payroll', $this->money($payroll)],
            ['Net profit', $this->money($profit)],
            ['', ''],
            ['Period', $from->format('d M Y') . ' – ' . $to->format('d M Y')],
            ['Currency', $currencyCode],
            ['', ''],
            ['Account breakdown', 'Net (' . $currencyCode . ')'],
        ]);

        $accountRows = $entries->groupBy('account')->map(function (Collection $group, string $account) {
            $debit = (float) $group->sum('debit');
            $credit = (float) $group->sum('credit');

            return [
                'account' => $account,
                'net' => $credit - $debit,
            ];
        })->sortBy('account');

        foreach ($accountRows as $row) {
            $rows->push([$row['account'], $this->money($row['net'])]);
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
