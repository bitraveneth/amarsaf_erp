<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ChartOfAccountsSummaryService
{
    /** @var array<int, float> */
    protected array $rollupCache = [];

    /**
     * @return array{
     *     period: array<int, array{debit: float, credit: float}>,
     *     closing: array<int, array{debit: float, credit: float}>,
     *     type_cards: array<string, array<string, mixed>>,
     *     net_assets: float
     * }
     */
    public function summarize(Carbon $from, Carbon $to, Collection $allAccounts): array
    {
        $this->rollupCache = [];

        $period = $this->aggregateBalances($from, $to);
        $closing = $this->aggregateBalances(null, $to);

        $typeCards = $this->typeCards($allAccounts, $period, $closing);

        $assets = $typeCards['asset']['balance'] ?? 0.0;
        $liabilities = $typeCards['liability']['balance'] ?? 0.0;

        return [
            'period' => $period,
            'closing' => $closing,
            'type_cards' => $typeCards,
            'net_assets' => round($assets - $liabilities, 2),
        ];
    }

    public function rollupBalance(Account $node, array $period, array $closing): float
    {
        if (isset($this->rollupCache[$node->id])) {
            return $this->rollupCache[$node->id];
        }

        $ids = $node->ledgerDescendantIds();
        if ($ids === []) {
            return $this->rollupCache[$node->id] = 0.0;
        }

        $periodDebit = 0.0;
        $periodCredit = 0.0;
        $closingDebit = 0.0;
        $closingCredit = 0.0;

        foreach ($ids as $id) {
            $periodDebit += $period[$id]['debit'] ?? 0.0;
            $periodCredit += $period[$id]['credit'] ?? 0.0;
            $closingDebit += $closing[$id]['debit'] ?? 0.0;
            $closingCredit += $closing[$id]['credit'] ?? 0.0;
        }

        $amount = match ($node->type) {
            'income' => round($periodCredit - $periodDebit, 2),
            'expense' => round($periodDebit - $periodCredit, 2),
            'liability', 'equity' => round($closingCredit - $closingDebit, 2),
            default => round($closingDebit - $closingCredit, 2),
        };

        return $this->rollupCache[$node->id] = $amount;
    }

    /**
     * @return array<int, array{debit: float, credit: float}>
     */
    protected function aggregateBalances(?Carbon $from, Carbon $to): array
    {
        $query = JournalEntryLine::query()
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(credit), 0) as credit')
            ->whereHas('journalEntry', function (Builder $q) use ($from, $to) {
                $q->where('status', 'posted');

                if ($from) {
                    $q->whereDate('entry_date', '>=', $from->toDateString());
                }

                $q->whereDate('entry_date', '<=', $to->toDateString());
            })
            ->groupBy('account_id');

        $rows = [];

        foreach ($query->get() as $row) {
            $rows[(int) $row->account_id] = [
                'debit' => round((float) $row->debit, 2),
                'credit' => round((float) $row->credit, 2),
            ];
        }

        return $rows;
    }

    /**
     * @param  array<int, array{debit: float, credit: float}>  $period
     * @param  array<int, array{debit: float, credit: float}>  $closing
     * @return array<string, array<string, mixed>>
     */
    protected function typeCards(Collection $allAccounts, array $period, array $closing): array
    {
        $cards = [];

        foreach (['asset', 'liability', 'equity', 'income', 'expense'] as $type) {
            $subset = $allAccounts->where('type', $type);
            $ledgerIds = $subset->where('is_group', false)->pluck('id')->all();

            $periodDebit = 0.0;
            $periodCredit = 0.0;
            $closingDebit = 0.0;
            $closingCredit = 0.0;
            $activeLedgers = 0;

            foreach ($ledgerIds as $id) {
                $periodDebit += $period[$id]['debit'] ?? 0.0;
                $periodCredit += $period[$id]['credit'] ?? 0.0;
                $closingDebit += $closing[$id]['debit'] ?? 0.0;
                $closingCredit += $closing[$id]['credit'] ?? 0.0;

                if (($period[$id]['debit'] ?? 0) > 0 || ($period[$id]['credit'] ?? 0) > 0) {
                    $activeLedgers++;
                }
            }

            $balance = match ($type) {
                'income' => round($periodCredit - $periodDebit, 2),
                'expense' => round($periodDebit - $periodCredit, 2),
                'liability', 'equity' => round($closingCredit - $closingDebit, 2),
                default => round($closingDebit - $closingCredit, 2),
            };

            $cards[$type] = [
                'count' => $subset->count(),
                'ledgers' => count($ledgerIds),
                'active_in_period' => $activeLedgers,
                'balance' => $balance,
                'balance_mode' => in_array($type, ['income', 'expense'], true) ? 'period' : 'closing',
            ];
        }

        return $cards;
    }
}
