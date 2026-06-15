<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CashFlowReportService
{
    public function report(Carbon $from, Carbon $to): array
    {
        $accounts = $this->cashAccounts();
        $accountIds = $accounts->pluck('id');

        $periodLines = JournalEntryLine::query()
            ->whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function ($query) use ($from, $to) {
                $query->where('status', 'posted')
                    ->whereDate('entry_date', '>=', $from->toDateString())
                    ->whereDate('entry_date', '<=', $to->toDateString());
            })
            ->get();

        $cashIn = round((float) $periodLines->sum('debit'), 2);
        $cashOut = round((float) $periodLines->sum('credit'), 2);
        $net = round($cashIn - $cashOut, 2);

        $openingBalance = $this->balanceBefore($accountIds, $from);
        $closingBalance = round($openingBalance + $net, 2);

        $byAccount = $accounts->map(function (Account $account) use ($from, $to) {
            $lines = JournalEntryLine::query()
                ->where('account_id', $account->id)
                ->whereHas('journalEntry', function ($query) use ($from, $to) {
                    $query->where('status', 'posted')
                        ->whereDate('entry_date', '>=', $from->toDateString())
                        ->whereDate('entry_date', '<=', $to->toDateString());
                })
                ->get();

            $in = round((float) $lines->sum('debit'), 2);
            $out = round((float) $lines->sum('credit'), 2);

            return [
                'account' => $account,
                'cash_in' => $in,
                'cash_out' => $out,
                'net' => round($in - $out, 2),
            ];
        })->filter(fn (array $row) => abs($row['cash_in']) > 0.00001 || abs($row['cash_out']) > 0.00001)
            ->sortByDesc(fn (array $row) => abs($row['net']))
            ->values();

        $weekly = $this->weeklyBreakdown($accountIds, $from, $to);

        $days = max(1, $from->diffInDays($to) + 1);

        return [
            'from' => $from,
            'to' => $to,
            'cashIn' => $cashIn,
            'cashOut' => $cashOut,
            'net' => $net,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'byAccount' => $byAccount,
            'weekly' => $weekly,
            'days' => $days,
            'ratio' => $cashOut > 0 ? round($cashIn / $cashOut, 2) : ($cashIn > 0 ? 999 : 0),
        ];
    }

    protected function cashAccounts(): Collection
    {
        return Account::query()
            ->where('type', 'asset')
            ->where(function ($query) {
                $query->where('slug', 'like', 'bank_%')
                    ->orWhereIn('slug', ['cash_in_hand', 'petty_cash'])
                    ->orWhere('name', 'like', '%Bank%')
                    ->orWhere('name', 'like', '%Cash%')
                    ->orWhere('code', 'like', '10%');
            })
            ->orderBy('code')
            ->get();
    }

    protected function balanceBefore(Collection $accountIds, Carbon $before): float
    {
        if ($accountIds->isEmpty()) {
            return 0.0;
        }

        $debits = (float) JournalEntryLine::query()
            ->whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function ($query) use ($before) {
                $query->where('status', 'posted')
                    ->whereDate('entry_date', '<', $before->toDateString());
            })
            ->sum('debit');

        $credits = (float) JournalEntryLine::query()
            ->whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function ($query) use ($before) {
                $query->where('status', 'posted')
                    ->whereDate('entry_date', '<', $before->toDateString());
            })
            ->sum('credit');

        return round($debits - $credits, 2);
    }

    protected function weeklyBreakdown(Collection $accountIds, Carbon $from, Carbon $to): Collection
    {
        if ($accountIds->isEmpty()) {
            return collect();
        }

        $cursor = $from->copy()->startOfWeek();
        $weeks = collect();

        while ($cursor->lte($to)) {
            $weekEnd = $cursor->copy()->endOfWeek();
            if ($weekEnd->gt($to)) {
                $weekEnd = $to->copy();
            }

            $weekStart = $cursor->copy();
            if ($weekStart->lt($from)) {
                $weekStart = $from->copy();
            }

            $lines = JournalEntryLine::query()
                ->whereIn('account_id', $accountIds)
                ->whereHas('journalEntry', function ($query) use ($weekStart, $weekEnd) {
                    $query->where('status', 'posted')
                        ->whereDate('entry_date', '>=', $weekStart->toDateString())
                        ->whereDate('entry_date', '<=', $weekEnd->toDateString());
                })
                ->get();

            $in = round((float) $lines->sum('debit'), 2);
            $out = round((float) $lines->sum('credit'), 2);

            $weeks->push([
                'label' => $weekStart->format('d M') . ' – ' . $weekEnd->format('d M'),
                'cash_in' => $in,
                'cash_out' => $out,
                'net' => round($in - $out, 2),
            ]);

            $cursor->addWeek();
        }

        return $weeks;
    }
}
