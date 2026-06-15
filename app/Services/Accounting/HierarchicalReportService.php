<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LogisticsBill;
use App\Models\SalaryDistribution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HierarchicalReportService
{
    public function trialBalance(Carbon $from, Carbon $to, bool $expandAll = true): Collection
    {
        $roots = Account::query()->roots()->orderBy('code')->get();
        $rows = collect();

        foreach ($roots as $root) {
            $this->appendTrialBalanceNode($rows, $root, $from, $to, $expandAll);
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function trialBalanceTree(Carbon $from, Carbon $to): array
    {
        return Account::query()
            ->roots()
            ->orderBy('code')
            ->get()
            ->map(fn (Account $root) => $this->buildTrialBalanceTreeNode($root, $from, $to))
            ->filter()
            ->values()
            ->all();
    }

    protected function buildTrialBalanceTreeNode(Account $node, Carbon $from, Carbon $to): ?array
    {
        $balances = $this->nodeBalances($node, $from, $to);
        $children = [];

        if ($node->is_group) {
            foreach ($node->children()->orderBy('code')->get() as $child) {
                $built = $this->buildTrialBalanceTreeNode($child, $from, $to);
                if ($built !== null) {
                    $children[] = $built;
                }
            }
        }

        if (! $node->is_group) {
            if (abs($balances['closing_debit']) < 0.01 && abs($balances['closing_credit']) < 0.01) {
                return null;
            }
        } elseif ($children === [] && abs($balances['closing_debit']) < 0.01 && abs($balances['closing_credit']) < 0.01) {
            return null;
        }

        return array_merge($this->rowMeta($node), $balances, [
            'children' => $children,
        ]);
    }

    protected function appendTrialBalanceNode(Collection $rows, Account $node, Carbon $from, Carbon $to, bool $expandAll): void
    {
        $balances = $this->nodeBalances($node, $from, $to);

        if ($node->is_group) {
            if (abs($balances['closing_debit']) < 0.01 && abs($balances['closing_credit']) < 0.01 && ! $expandAll) {
                return;
            }

            $rows->push(array_merge($this->rowMeta($node), $balances, ['is_subtotal' => true]));
        } else {
            if (abs($balances['closing_debit']) < 0.01 && abs($balances['closing_credit']) < 0.01) {
                return;
            }

            $rows->push(array_merge($this->rowMeta($node), $balances, ['is_subtotal' => false]));
        }

        if ($node->is_group && ($expandAll || $node->level < 2)) {
            foreach ($node->children as $child) {
                $this->appendTrialBalanceNode($rows, $child, $from, $to, $expandAll);
            }
        }
    }

    public function balanceSheet(Carbon $asOf): array
    {
        $sections = [
            'assets' => $this->sectionTree('assets', $asOf),
            'liabilities' => $this->sectionTree('liabilities', $asOf),
            'equity' => $this->sectionTree('equity', $asOf),
        ];

        $totalAssets = $sections['assets']['total'];
        $totalLiabilities = $sections['liabilities']['total'];
        $totalEquity = $sections['equity']['total'];

        return [
            'sections' => $sections,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_liabilities_equity' => round($totalLiabilities + $totalEquity, 2),
        ];
    }

    public function profitAndLoss(Carbon $from, Carbon $to): array
    {
        $revenue = $this->sectionTree('revenue', $to, $from);
        $manufacturing = $this->sectionTree('manufacturing', $to, $from);
        $operating = $this->sectionTree('operatingexpense', $to, $from);

        $revenueRows = $revenue['rows'];
        $operatingRows = $operating['rows'];

        $grossSales = round(
            $this->amountBySlug($revenueRows, 'product_sales')
            + $this->amountBySlug($revenueRows, 'service_revenue')
            + $this->amountBySlug($revenueRows, 'export_sales'),
            2
        );
        $salesReturns = round(abs($this->amountBySlug($revenueRows, 'sales_returns')), 2);
        $otherIncome = round($this->subtotalByCode($revenueRows, '4200'), 2);
        $netRevenue = $revenue['total'];

        $manufacturingCost = $manufacturing['total'];
        $grossProfit = round($netRevenue - $manufacturingCost, 2);

        $administrativeExpenses = round($this->subtotalByCode($operatingRows, '6100'), 2);
        $sellingExpenses = round($this->subtotalByCode($operatingRows, '6200'), 2);
        $financialExpenses = round($this->subtotalByCode($operatingRows, '6300'), 2);
        $operatingExpenses = $operating['total'];
        $operatingProfit = round($grossProfit - $administrativeExpenses - $sellingExpenses, 2);
        $netProfit = round($grossProfit - $operatingExpenses, 2);

        $commissionExpense = round($this->amountBySlug($operatingRows, 'commission_expense'), 2);
        $deliveryExpense = round(
            $this->amountBySlug($operatingRows, 'delivery_expense')
            + $this->amountBySlug($operatingRows, 'vehicle_fuel')
            + $this->amountBySlug($operatingRows, 'courier_expense'),
            2
        );
        $payrollExpense = round($this->amountBySlug($operatingRows, 'salaries_wages'), 2);

        return [
            'revenue' => $revenue,
            'manufacturing' => $manufacturing,
            'operating' => $operating,
            'grossSales' => $grossSales,
            'salesReturns' => $salesReturns,
            'otherIncome' => $otherIncome,
            'netRevenue' => $netRevenue,
            'manufacturingCost' => $manufacturingCost,
            'grossProfit' => $grossProfit,
            'administrativeExpenses' => $administrativeExpenses,
            'sellingExpenses' => $sellingExpenses,
            'financialExpenses' => $financialExpenses,
            'commissionExpense' => $commissionExpense,
            'deliveryExpense' => $deliveryExpense,
            'payrollExpense' => $payrollExpense,
            'operatingExpenses' => $operatingExpenses,
            'operatingProfit' => $operatingProfit,
            'netProfit' => $netProfit,
            'unposted' => $this->unpostedSummary($from, $to),
        ];
    }

    /**
     * @return array<int, array{label: string, count: int, href: string}>
     */
    protected function unpostedSummary(Carbon $from, Carbon $to): array
    {
        $items = [];

        $draftLogistics = LogisticsBill::query()
            ->where('status', 'draft')
            ->whereDate('bill_date', '>=', $from->toDateString())
            ->whereDate('bill_date', '<=', $to->toDateString())
            ->count();

        if ($draftLogistics > 0) {
            $items[] = [
                'label' => 'Draft logistics bills',
                'count' => $draftLogistics,
                'href' => route('admin.logistics-bills.index'),
            ];
        }

        $unpostedPayroll = SalaryDistribution::query()
            ->whereNull('journal_entry_id')
            ->whereDate('period_end', '>=', $from->toDateString())
            ->whereDate('period_end', '<=', $to->toDateString())
            ->count();

        if ($unpostedPayroll > 0) {
            $items[] = [
                'label' => 'Payroll not in ledger',
                'count' => $unpostedPayroll,
                'href' => route('admin.salary-distributions.index'),
            ];
        }

        $periodExpenses = Expense::query()
            ->where('amount', '>', 0)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->pluck('id');

        if ($periodExpenses->isNotEmpty()) {
            $postedExpenseIds = JournalEntry::query()
                ->where('source_type', Expense::class)
                ->where('status', 'posted')
                ->whereIn('source_id', $periodExpenses)
                ->pluck('source_id');

            $unpostedExpenses = $periodExpenses->diff($postedExpenseIds)->count();

            if ($unpostedExpenses > 0) {
                $items[] = [
                    'key' => 'expenses',
                    'label' => 'Expenses not in P&L ledger',
                    'count' => $unpostedExpenses,
                    'href' => route('admin.expenses.index', [
                        'from' => $from->toDateString(),
                        'to' => $to->toDateString(),
                    ]),
                    'hint' => 'Usually older records saved before auto-posting, or a failed post. New expenses post automatically when you save them.',
                    'can_sync' => true,
                ];
            }
        }

        return $items;
    }

    protected function amountBySlug(Collection $rows, string $slug): float
    {
        $row = $rows->first(fn (array $item) => ($item['slug'] ?? '') === $slug && empty($item['is_subtotal']));

        return (float) ($row['amount'] ?? 0);
    }

    protected function subtotalByCode(Collection $rows, string $code): float
    {
        $row = $rows->first(fn (array $item) => ($item['code'] ?? '') === $code && ! empty($item['is_subtotal']));

        return (float) ($row['amount'] ?? 0);
    }

    public function manufacturingSchedule(Carbon $from, Carbon $to): array
    {
        return $this->sectionTree('manufacturing', $to, $from);
    }

    protected function sectionTree(string $reportRoot, Carbon $to, ?Carbon $from = null): array
    {
        $root = Account::query()
            ->where('report_root', $reportRoot)
            ->whereNull('parent_id')
            ->first();

        if (! $root) {
            return ['name' => ucfirst($reportRoot), 'rows' => collect(), 'total' => 0.0];
        }

        $rows = collect();
        $this->appendStatementNode($rows, $root, $from, $to, $reportRoot);

        return [
            'name' => $root->name,
            'rows' => $rows,
            'total' => round($rows->where('is_subtotal', true)->where('level', 0)->sum('amount'), 2)
                ?: round($rows->where('is_subtotal', false)->sum('amount'), 2),
        ];
    }

    protected function appendStatementNode(Collection $rows, Account $node, ?Carbon $from, Carbon $to, string $reportRoot): float
    {
        $amount = $this->signedAmount($node, $from, $to, $reportRoot);

        if ($node->is_group) {
            $childTotal = 0.0;
            foreach ($node->children as $child) {
                $childTotal += $this->appendStatementNode($rows, $child, $from, $to, $reportRoot);
            }

            if (abs($childTotal) >= 0.01 || $node->level <= 1) {
                $rows->push(array_merge($this->rowMeta($node), [
                    'amount' => round($childTotal, 2),
                    'is_subtotal' => true,
                ]));
            }

            return $childTotal;
        }

        if (abs($amount) < 0.01) {
            return 0.0;
        }

        $rows->push(array_merge($this->rowMeta($node), [
            'amount' => round($amount, 2),
            'is_subtotal' => false,
        ]));

        return $amount;
    }

    protected function signedAmount(Account $node, ?Carbon $from, Carbon $to, string $reportRoot): float
    {
        $balances = $this->ledgerBalances($node->ledgerDescendantIds(), $from, $to);
        $net = $balances['debit'] - $balances['credit'];

        return match ($reportRoot) {
            'revenue' => round($balances['credit'] - $balances['debit'], 2),
            'liabilities', 'equity' => round($balances['credit'] - $balances['debit'], 2),
            default => round($net, 2),
        };
    }

    protected function nodeBalances(Account $node, Carbon $from, Carbon $to): array
    {
        $ids = $node->ledgerDescendantIds();
        $opening = $this->ledgerBalances($ids, null, $from->copy()->subDay());
        $period = $this->ledgerBalances($ids, $from, $to);
        $closing = $this->ledgerBalances($ids, null, $to);

        return [
            'opening_debit' => $opening['debit'],
            'opening_credit' => $opening['credit'],
            'period_debit' => $period['debit'],
            'period_credit' => $period['credit'],
            'closing_debit' => max(0, $closing['debit'] - $closing['credit']),
            'closing_credit' => max(0, $closing['credit'] - $closing['debit']),
        ];
    }

    /**
     * @param  array<int>  $accountIds
     * @return array{debit: float, credit: float}
     */
    protected function ledgerBalances(array $accountIds, ?Carbon $from, ?Carbon $to): array
    {
        if ($accountIds === []) {
            return ['debit' => 0.0, 'credit' => 0.0];
        }

        $query = JournalEntryLine::query()
            ->whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function (Builder $q) use ($from, $to) {
                $q->where('status', 'posted');
                if ($from) {
                    $q->whereDate('entry_date', '>=', $from->toDateString());
                }
                if ($to) {
                    $q->whereDate('entry_date', '<=', $to->toDateString());
                }
            });

        return [
            'debit' => round((float) $query->sum('debit'), 2),
            'credit' => round((float) (clone $query)->sum('credit'), 2),
        ];
    }

    protected function rowMeta(Account $node): array
    {
        return [
            'id' => $node->id,
            'code' => $node->code,
            'name' => $node->name,
            'slug' => $node->slug,
            'level' => $node->level,
            'is_group' => $node->is_group,
            'type' => $node->type,
            'report_root' => $node->report_root,
            'breadcrumb' => $node->breadcrumb(),
        ];
    }
}
