<?php

namespace App\Services\Accounting;

use App\Models\AccountingPeriod;
use App\Models\Expense;
use App\Models\JournalEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Throwable;

class UnpostedExpenseNotice
{
    /** @var array<string, array<string, mixed>|null> */
    protected array $cache = [];

    public function __construct(
        protected ExpensePostingService $posting,
        protected AccountResolver $accounts
    ) {
    }

    /**
     * Expenses saved in the list that have no posted journal, so profit and loss ignores them.
     *
     * @return array<string, mixed>|null
     */
    public function summary(?Carbon $from = null, ?Carbon $to = null): ?array
    {
        $cacheKey = ($from?->toDateString() ?? '*').'|'.($to?->toDateString() ?? '*');

        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        if (! Schema::hasTable('expenses') || ! Schema::hasTable('journal_entries')) {
            return $this->cache[$cacheKey] = null;
        }

        $query = Expense::query()
            ->with('expenseCategory')
            ->where('amount', '>', 0)
            ->orderBy('date')
            ->orderBy('id');

        if ($from) {
            $query->whereDate('date', '>=', $from->toDateString());
        }

        if ($to) {
            $query->whereDate('date', '<=', $to->toDateString());
        }

        $expenses = $query->get();

        if ($expenses->isEmpty()) {
            return $this->cache[$cacheKey] = null;
        }

        $journals = JournalEntry::query()
            ->where('source_type', Expense::class)
            ->whereIn('source_id', $expenses->modelKeys())
            ->get(['id', 'source_id', 'status', 'entry_date'])
            ->groupBy('source_id');

        $closedPeriods = Schema::hasTable('accounting_periods')
            ? AccountingPeriod::query()->where('is_closed', true)->get(['name', 'start_date', 'end_date'])
            : collect();

        $groups = [
            'period_closed' => [],
            'reversed' => [],
            'draft' => [],
            'blocked' => [],
            'never_posted' => [],
        ];

        foreach ($expenses as $expense) {
            $rows = $journals->get($expense->id, collect());

            if ($rows->contains(fn ($journal) => $journal->status === 'posted')) {
                continue;
            }

            $closed = $this->closedPeriodName($expense->date, $closedPeriods);
            if ($closed !== null) {
                $groups['period_closed'][] = $this->example($expense, 'The month '.$closed.' is closed, so a voucher cannot be posted on this date.');
                continue;
            }

            if ($rows->contains(fn ($journal) => $journal->status === 'reversed')) {
                $groups['reversed'][] = $this->example($expense, 'Its voucher was reversed, so the statement dropped the amount.');
                continue;
            }

            if ($rows->contains(fn ($journal) => $journal->status === 'draft')) {
                $groups['draft'][] = $this->example($expense, 'A draft voucher exists and has not been posted.');
                continue;
            }

            $block = $this->postingBlock($expense);
            if ($block !== null) {
                $groups['blocked'][] = $this->example($expense, $block);
                continue;
            }

            $groups['never_posted'][] = $this->example(
                $expense,
                'Saved in the expense list with no journal voucher.'
            );
        }

        $count = array_sum(array_map('count', $groups));

        if ($count === 0) {
            return $this->cache[$cacheKey] = null;
        }

        $openCount = $count - count($groups['period_closed']) - count($groups['blocked']);
        $ids = [];
        foreach ($groups as $items) {
            foreach ($items as $item) {
                $ids[] = $item['id'];
            }
        }
        sort($ids);

        $examples = [];
        foreach (['never_posted', 'reversed', 'draft', 'blocked', 'period_closed'] as $code) {
            foreach ($groups[$code] as $item) {
                $examples[] = $item;
                if (count($examples) >= 3) {
                    break 2;
                }
            }
        }

        return $this->cache[$cacheKey] = [
            'count' => $count,
            'open_count' => $openCount,
            'title' => $count === 1
                ? '1 expense is not in the profit and loss ledger'
                : $count.' expenses are not in the profit and loss ledger',
            'why' => $this->why($groups),
            'examples' => $examples,
            'ids' => $ids,
            'fingerprint' => md5(implode(',', $ids)),
            'can_sync' => $openCount > 0,
            'report_url' => route('admin.reports.pl'),
            'list_url' => route('admin.expenses.index'),
        ];
    }

    public function forget(): void
    {
        $this->cache = [];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, AccountingPeriod>  $closedPeriods
     */
    protected function closedPeriodName(mixed $date, $closedPeriods): ?string
    {
        if ($date === null || $closedPeriods->isEmpty()) {
            return null;
        }

        $day = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();

        $period = $closedPeriods->first(function (AccountingPeriod $period) use ($day) {
            return $period->start_date && $period->end_date
                && $day->between($period->start_date->copy()->startOfDay(), $period->end_date->copy()->endOfDay());
        });

        return $period?->name ?: ($period ? 'for this date' : null);
    }

    protected function postingBlock(Expense $expense): ?string
    {
        try {
            $this->accounts->resolve($this->posting->expenseAccountKey($expense));
            $this->accounts->resolve($this->posting->creditAccountKey($expense));
        } catch (InvalidArgumentException $exception) {
            return $exception->getMessage();
        } catch (Throwable) {
            return 'The expense account or the payment account could not be found.';
        }

        return null;
    }

    /**
     * @return array{id: int, label: string, detail: string}
     */
    protected function example(Expense $expense, string $detail): array
    {
        $name = trim((string) ($expense->description ?: $expense->expenseCategory?->name ?: $expense->category ?: 'Expense'));
        $when = $expense->date ? Carbon::parse($expense->date)->format('d M Y') : 'no date';

        return [
            'id' => (int) $expense->id,
            'label' => $name.' · '.number_format((float) $expense->amount, 2).' · '.$when,
            'detail' => $detail,
        ];
    }

    /**
     * @param  array<string, array<int, array{id: int, label: string, detail: string}>>  $groups
     */
    protected function why(array $groups): string
    {
        $parts = [];

        if ($groups['never_posted'] !== []) {
            $parts[] = (count($groups['never_posted']) === 1 ? 'This expense is' : 'These expenses are')
                .' saved without a journal voucher. Sync posts '
                .(count($groups['never_posted']) === 1 ? 'it' : 'them')
                .' to the ledger.';
        }

        if ($groups['reversed'] !== []) {
            $parts[] = 'A voucher was reversed, so the statement no longer includes that amount. Sync posts a fresh voucher while the month is open.';
        }

        if ($groups['draft'] !== []) {
            $parts[] = 'A draft voucher was started and never posted.';
        }

        if ($groups['blocked'] !== []) {
            $parts[] = 'Sync cannot post '
                .(count($groups['blocked']) === 1 ? 'one expense' : count($groups['blocked']).' expenses')
                .' until the expense account and the payment account both exist. '.$groups['blocked'][0]['detail'];
        }

        if ($groups['period_closed'] !== []) {
            $parts[] = (count($groups['period_closed']) === 1 ? 'One expense is' : count($groups['period_closed']).' expenses are')
                .' dated in a closed month. Reopen that month before syncing it.';
        }

        return implode(' ', $parts);
    }
}
