<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntryLine;
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

        $netRevenue = $revenue['total'];
        $manufacturingCost = $manufacturing['total'];
        $operatingExpenses = $operating['total'];
        $grossProfit = round($netRevenue - $manufacturingCost, 2);
        $netProfit = round($grossProfit - $operatingExpenses, 2);

        return compact('revenue', 'manufacturing', 'operating', 'netRevenue', 'manufacturingCost', 'operatingExpenses', 'grossProfit', 'netProfit');
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
