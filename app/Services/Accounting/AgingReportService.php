<?php

namespace App\Services\Accounting;

use App\Models\Invoice;
use App\Models\JournalEntryLine;
use App\Models\PurchaseBill;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AgingReportService
{
    public function __construct(
        protected AccountResolver $accounts,
    ) {}

    public const BUCKET_LABELS = [
        'current' => 'Current',
        '1_30' => '1–30 days',
        '31_60' => '31–60 days',
        '61_90' => '61–90 days',
        '90_plus' => '90+ days',
    ];

    public function receivableAging(?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? Carbon::today())->copy()->endOfDay();

        $invoices = Invoice::with(['order.agent', 'receipts', 'creditNotes', 'advanceApplications'])
            ->whereIn('status', ['issued', 'adjusted'])
            ->get()
            ->filter(fn (Invoice $invoice) => $invoice->outstanding > 0.00001)
            ->values();

        $rows = $invoices->map(function (Invoice $invoice) use ($asOf) {
            $dueDate = $invoice->due_at ?? $invoice->issued_at;
            $bucket = $this->agingBucket($dueDate, $asOf);

            return [
                'invoice' => $invoice,
                'agent' => $invoice->order?->agent,
                'due_at' => $dueDate,
                'days_past_due' => $this->daysPastDue($dueDate, $asOf),
                'bucket' => $bucket,
                'outstanding' => (float) $invoice->outstanding,
            ];
        });

        return [
            'asOf' => $asOf,
            'rows' => $rows,
            'buckets' => $this->summarizeBuckets($rows),
            'byAgent' => $this->groupReceivableByAgent($rows),
            'subledgerTotal' => round((float) $rows->sum('outstanding'), 2),
            'glBalance' => $this->ledgerBalance('trade_debtors', 'asset'),
            'variance' => round((float) $rows->sum('outstanding') - $this->ledgerBalance('trade_debtors', 'asset'), 2),
        ];
    }

    public function payableAging(?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? Carbon::today())->copy()->endOfDay();

        $bills = PurchaseBill::with(['supplier', 'payments'])
            ->whereIn('status', ['open', 'part_paid'])
            ->get()
            ->filter(fn (PurchaseBill $bill) => $bill->outstanding > 0.00001)
            ->values();

        $rows = $bills->map(function (PurchaseBill $bill) use ($asOf) {
            $dueDate = $bill->due_date ?? $bill->bill_date;
            $bucket = $this->agingBucket($dueDate, $asOf);

            return [
                'bill' => $bill,
                'supplier' => $bill->supplier,
                'due_at' => $dueDate,
                'days_past_due' => $this->daysPastDue($dueDate, $asOf),
                'bucket' => $bucket,
                'outstanding' => (float) $bill->outstanding,
            ];
        });

        return [
            'asOf' => $asOf,
            'rows' => $rows,
            'buckets' => $this->summarizeBuckets($rows),
            'bySupplier' => $this->groupPayableBySupplier($rows),
            'subledgerTotal' => round((float) $rows->sum('outstanding'), 2),
            'glBalance' => $this->ledgerBalance('trade_creditors', 'liability'),
            'variance' => round((float) $rows->sum('outstanding') - $this->ledgerBalance('trade_creditors', 'liability'), 2),
        ];
    }

    public function agingBucket(?Carbon $dueDate, Carbon $asOf): string
    {
        if (! $dueDate) {
            return 'current';
        }

        $daysPastDue = $this->daysPastDue($dueDate, $asOf);

        if ($daysPastDue <= 0) {
            return 'current';
        }

        if ($daysPastDue <= 30) {
            return '1_30';
        }

        if ($daysPastDue <= 60) {
            return '31_60';
        }

        if ($daysPastDue <= 90) {
            return '61_90';
        }

        return '90_plus';
    }

    public function daysPastDue(?Carbon $dueDate, Carbon $asOf): int
    {
        if (! $dueDate) {
            return 0;
        }

        return (int) $dueDate->copy()->startOfDay()->diffInDays($asOf->copy()->startOfDay(), false);
    }

    protected function summarizeBuckets(Collection $rows): array
    {
        $totals = array_fill_keys(array_keys(self::BUCKET_LABELS), 0.0);

        foreach ($rows as $row) {
            $totals[$row['bucket']] = ($totals[$row['bucket']] ?? 0) + (float) $row['outstanding'];
        }

        foreach ($totals as $key => $value) {
            $totals[$key] = round($value, 2);
        }

        return $totals;
    }

    protected function groupReceivableByAgent(Collection $rows): Collection
    {
        return $rows
            ->groupBy(fn (array $row) => $row['agent']?->id ?? 0)
            ->map(function (Collection $group) {
                $agent = $group->first()['agent'];
                $buckets = $this->summarizeBuckets($group);

                return [
                    'agent' => $agent,
                    'name' => $agent?->name ?? 'Unknown agent',
                    'buckets' => $buckets,
                    'total' => round((float) $group->sum('outstanding'), 2),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    protected function groupPayableBySupplier(Collection $rows): Collection
    {
        return $rows
            ->groupBy(fn (array $row) => $row['supplier']?->id ?? 0)
            ->map(function (Collection $group) {
                $supplier = $group->first()['supplier'];
                $buckets = $this->summarizeBuckets($group);

                return [
                    'supplier' => $supplier,
                    'name' => $supplier?->name ?? 'Unknown supplier',
                    'buckets' => $buckets,
                    'total' => round((float) $group->sum('outstanding'), 2),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    protected function ledgerBalance(string $accountKey, string $normalBalance): float
    {
        try {
            $accountId = $this->accounts->id($accountKey);
        } catch (\InvalidArgumentException) {
            return 0.0;
        }

        $debits = (float) JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->sum('debit');

        $credits = (float) JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->sum('credit');

        $balance = $normalBalance === 'asset'
            ? $debits - $credits
            : $credits - $debits;

        return round($balance, 2);
    }
}
