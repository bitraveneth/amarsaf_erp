<?php

namespace App\Services\Accounting;

use App\Exceptions\AccountingPeriodClosedException;
use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LedgerEntry;
use App\Services\WebhookDispatcher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AccountingService
{
    public function __construct(
        protected AccountResolver $accounts
    ) {
    }

    public function post(
        string $journalType,
        Carbon $entryDate,
        array $lines,
        array $options = []
    ): JournalEntry {
        $normalizedLines = $this->normalizeLines($lines);
        $this->assertBalanced($normalizedLines);
        $this->assertPeriodOpen($entryDate);

        $status = $options['status'] ?? 'posted';
        $description = $options['description'] ?? null;
        $timestamps = isset($options['created_at'])
            ? Carbon::parse($options['created_at'])
            : $entryDate->copy()->startOfDay();

        $journal = DB::transaction(function () use (
            $journalType,
            $entryDate,
            $normalizedLines,
            $options,
            $status,
            $description,
            $timestamps
        ) {
            $period = $this->resolvePeriod($entryDate);

            $journal = JournalEntry::create([
                'number' => 'JE-TMP-' . uniqid(),
                'entry_date' => $entryDate->toDateString(),
                'journal_type' => $journalType,
                'source_type' => $options['source_type'] ?? null,
                'source_id' => $options['source_id'] ?? null,
                'description' => $description,
                'status' => $status,
                'posted_at' => $status === 'posted' ? now() : null,
                'posted_by' => $status === 'posted' ? ($options['posted_by'] ?? Auth::id()) : null,
                'accounting_period_id' => $period?->id,
                'reversal_of_id' => $options['reversal_of_id'] ?? null,
                'order_id' => $options['order_id'] ?? null,
                'invoice_id' => $options['invoice_id'] ?? null,
            ]);

            $journal->update([
                'number' => $this->formatJournalNumber($journal->id),
            ]);

            foreach ($normalizedLines as $index => $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $line['account_id'],
                    'line_number' => $index + 1,
                    'description' => $line['description'] ?? $description,
                    'analytic_label' => $line['analytic_label'] ?? null,
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);

                if ($status === 'posted') {
                    $legacy = new LedgerEntry([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $line['account_id'],
                        'account' => $line['account_name'],
                        'description' => $line['description'] ?? $description,
                        'debit' => $line['debit'],
                        'credit' => $line['credit'],
                        'order_id' => $options['order_id'] ?? null,
                        'invoice_id' => $options['invoice_id'] ?? null,
                    ]);
                    $legacy->created_at = $timestamps;
                    $legacy->updated_at = $timestamps;
                    $legacy->save();
                }
            }

            return $journal->fresh('lines.account');
        });

        if ($status === 'posted') {
            app(WebhookDispatcher::class)->dispatch('journal.posted', [
                'journal_id' => $journal->id,
                'journal_type' => $journalType,
                'entry_date' => $entryDate->toDateString(),
                'description' => $description,
            ]);
        }

        return $journal;
    }

    public function createDraft(Carbon $entryDate, array $lines, array $options = []): JournalEntry
    {
        return $this->post('manual', $entryDate, $lines, array_merge($options, [
            'status' => 'draft',
        ]));
    }

    public function saveDraft(JournalEntry $journal, Carbon $entryDate, array $lines, array $options = []): JournalEntry
    {
        if (! $journal->exists) {
            return $this->createDraft($entryDate, $lines, $options);
        }

        if (! $journal->isEditable()) {
            throw new InvalidArgumentException('Only draft journal entries can be edited.');
        }

        $normalizedLines = $this->normalizeLines($lines);
        $this->assertBalanced($normalizedLines);
        $this->assertPeriodOpen($entryDate);

        return DB::transaction(function () use ($journal, $entryDate, $normalizedLines, $options) {
            $period = $this->resolvePeriod($entryDate);

            $journal->update([
                'entry_date' => $entryDate->toDateString(),
                'description' => $options['description'] ?? $journal->description,
                'accounting_period_id' => $period?->id,
            ]);

            $journal->lines()->delete();

            foreach ($normalizedLines as $index => $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $line['account_id'],
                    'line_number' => $index + 1,
                    'description' => $line['description'] ?? $journal->description,
                    'analytic_label' => $line['analytic_label'] ?? null,
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);
            }

            return $journal->fresh('lines.account');
        });
    }

    public function postDraft(JournalEntry $journal): JournalEntry
    {
        if ($journal->status !== 'draft') {
            throw new InvalidArgumentException('Only draft entries can be posted.');
        }

        $journal->load('lines.account');
        if (! $journal->isBalanced()) {
            throw new InvalidArgumentException('Journal entry is not balanced.');
        }

        $this->assertPeriodOpen(Carbon::parse($journal->entry_date));

        return DB::transaction(function () use ($journal) {
            $timestamps = Carbon::parse($journal->entry_date)->startOfDay();

            foreach ($journal->lines as $line) {
                $legacy = new LedgerEntry([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $line->account_id,
                    'account' => $line->account->name,
                    'description' => $line->description ?? $journal->description,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'order_id' => $journal->order_id,
                    'invoice_id' => $journal->invoice_id,
                ]);
                $legacy->created_at = $timestamps;
                $legacy->updated_at = $timestamps;
                $legacy->save();
            }

            $journal->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => Auth::id(),
            ]);

            return $journal->fresh('lines.account');
        });
    }

    public function reverse(JournalEntry $journal, ?string $reason = null): JournalEntry
    {
        if ($journal->status !== 'posted') {
            throw new InvalidArgumentException('Only posted entries can be reversed.');
        }

        $journal->load('lines.account');
        $entryDate = Carbon::today();
        $description = $reason ?: ('Reversal of ' . $journal->number);

        $lines = $journal->lines->map(function (JournalEntryLine $line) use ($description) {
            return [
                'account_id' => $line->account_id,
                'debit' => $line->credit,
                'credit' => $line->debit,
                'description' => $description,
            ];
        })->all();

        return DB::transaction(function () use ($journal, $entryDate, $lines, $description) {
            $reversal = $this->post('reversal', $entryDate, $lines, [
                'description' => $description,
                'source_type' => JournalEntry::class,
                'source_id' => $journal->id,
                'order_id' => $journal->order_id,
                'invoice_id' => $journal->invoice_id,
                'reversal_of_id' => $journal->id,
            ]);

            $journal->update([
                'status' => 'reversed',
                'reversed_entry_id' => $reversal->id,
            ]);

            $reversal->update(['reversal_of_id' => $journal->id]);

            return $reversal;
        });
    }

    public function deleteByDescription(string $description): void
    {
        $journals = JournalEntry::where('description', $description)->get();

        DB::transaction(function () use ($journals, $description) {
            foreach ($journals as $journal) {
                LedgerEntry::where('journal_entry_id', $journal->id)->delete();
                $journal->lines()->delete();
                $journal->delete();
            }

            LedgerEntry::where('description', $description)
                ->whereNull('journal_entry_id')
                ->delete();
        });
    }

    public function deleteByJournalTypeAndSource(string $journalType, string $sourceType, int $sourceId): void
    {
        $journals = JournalEntry::query()
            ->where('journal_type', $journalType)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->get();

        DB::transaction(function () use ($journals) {
            foreach ($journals as $journal) {
                if ($journal->status === 'posted') {
                    LedgerEntry::where('journal_entry_id', $journal->id)->delete();
                }

                $journal->lines()->delete();
                $journal->delete();
            }
        });
    }

    public function resolveAccount(string $identifier): Account
    {
        $slug = config("accounting.accounts.{$identifier}") ?? $identifier;

        $account = Account::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            $account = Account::where('name', $identifier)->where('is_active', true)->first();
        }

        if (! $account) {
            throw new InvalidArgumentException("Account not found in chart of accounts: {$identifier}");
        }

        if (! $account->isPostable()) {
            throw new InvalidArgumentException("Account [{$account->name}] is a group and cannot be posted to.");
        }

        return $account;
    }

    public function resolveBySlug(string $slug): Account
    {
        return $this->accounts->resolve($slug);
    }

    public function resolveAccountId(string $name): int
    {
        return $this->resolveAccount($name)->id;
    }

    protected function normalizeLines(array $lines): array
    {
        $normalized = [];

        foreach ($lines as $line) {
            $debit = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);

            if ($debit <= 0 && $credit <= 0) {
                continue;
            }

            if ($debit > 0 && $credit > 0) {
                throw new InvalidArgumentException('A journal line cannot have both debit and credit amounts.');
            }

            if (isset($line['account_id'])) {
                $account = Account::findOrFail($line['account_id']);
            } elseif (isset($line['account_key'])) {
                $account = $this->accounts->resolve((string) $line['account_key']);
            } elseif (isset($line['slug'])) {
                $account = $this->resolveBySlug((string) $line['slug']);
            } else {
                $account = $this->resolveAccount((string) $line['account']);
            }

            if (! $account->isPostable()) {
                throw new InvalidArgumentException("Account [{$account->name}] is a group and cannot be posted to.");
            }

            $normalized[] = [
                'account_id' => $account->id,
                'account_name' => $account->name,
                'debit' => $debit,
                'credit' => $credit,
                'description' => $line['description'] ?? null,
                'analytic_label' => $line['analytic_label'] ?? null,
            ];
        }

        if (count($normalized) < 2) {
            throw new InvalidArgumentException('A journal entry requires at least two lines.');
        }

        return $normalized;
    }

    protected function assertBalanced(array $lines): void
    {
        $debit = round(collect($lines)->sum('debit'), 2);
        $credit = round(collect($lines)->sum('credit'), 2);

        if (abs($debit - $credit) > 0.01) {
            throw new InvalidArgumentException("Journal entry is not balanced. Debit={$debit}, Credit={$credit}");
        }
    }

    protected function assertPeriodOpen(Carbon $entryDate): void
    {
        $period = $this->resolvePeriod($entryDate);

        if ($period?->is_closed) {
            throw new AccountingPeriodClosedException(
                'Accounting period ' . $period->name . ' is closed. Choose an open period or reopen it first.'
            );
        }
    }

    protected function resolvePeriod(Carbon $entryDate): ?AccountingPeriod
    {
        return AccountingPeriod::query()
            ->whereDate('start_date', '<=', $entryDate->toDateString())
            ->whereDate('end_date', '>=', $entryDate->toDateString())
            ->first();
    }

    protected function formatJournalNumber(int $journalId): string
    {
        return 'JE-' . str_pad((string) $journalId, 6, '0', STR_PAD_LEFT);
    }
}
