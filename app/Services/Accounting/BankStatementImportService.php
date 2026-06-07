<?php

namespace App\Services\Accounting;

use App\Models\BillPayment;
use App\Models\Receipt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BankStatementImportService
{
    public function parseCsv(string $contents): array
    {
        $lines = preg_split('/\R/', trim($contents));
        $lines = array_values(array_filter($lines, fn ($line) => trim($line) !== ''));

        if ($lines === []) {
            return [];
        }

        $delimiter = str_contains($lines[0], ';') && ! str_contains($lines[0], ',') ? ';' : ',';
        $headers = array_map(fn ($value) => $this->normalizeHeader($value), str_getcsv(array_shift($lines), $delimiter));
        $rows = [];

        foreach ($lines as $line) {
            $cells = str_getcsv($line, $delimiter);
            if (count($cells) < 2) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $header) {
                $assoc[$header] = trim((string) ($cells[$index] ?? ''));
            }

            $amount = $this->parseAmount($assoc);
            $date = $this->parseDate($assoc);

            if ($amount === null || ! $date) {
                continue;
            }

            $rows[] = [
                'date' => $date,
                'amount' => abs($amount),
                'direction' => $amount >= 0 ? 'inflow' : 'outflow',
                'reference' => $this->parseReference($assoc),
            ];
        }

        return $rows;
    }

    public function matchReceipts(array $statementRows, Carbon $from, Carbon $to): array
    {
        $receipts = Receipt::with('invoice.order.agent')
            ->where('reconciled', false)
            ->whereBetween('received_at', [$from, $to])
            ->get();

        return collect($statementRows)
            ->filter(fn (array $row) => $row['direction'] === 'inflow')
            ->map(function (array $row) use ($receipts) {
                $match = $this->findReceiptMatch($receipts, $row);

                return array_merge($row, [
                    'match_type' => 'receipt',
                    'match' => $match,
                    'match_id' => $match?->id,
                ]);
            })
            ->values()
            ->all();
    }

    public function matchBillPayments(array $statementRows, Carbon $from, Carbon $to): array
    {
        $payments = BillPayment::with('bill.supplier')
            ->where('reconciled', false)
            ->whereBetween('paid_at', [$from, $to])
            ->get();

        return collect($statementRows)
            ->filter(fn (array $row) => $row['direction'] === 'outflow')
            ->map(function (array $row) use ($payments) {
                $match = $this->findBillPaymentMatch($payments, $row);

                return array_merge($row, [
                    'match_type' => 'bill_payment',
                    'match' => $match,
                    'match_id' => $match?->id,
                ]);
            })
            ->values()
            ->all();
    }

    protected function findReceiptMatch(Collection $receipts, array $row): ?Receipt
    {
        $targetAmount = round((float) $row['amount'], 2);
        $targetDate = Carbon::parse($row['date'])->startOfDay();

        return $receipts
            ->filter(function (Receipt $receipt) use ($targetAmount, $targetDate, $row) {
                if (round((float) $receipt->amount, 2) !== $targetAmount) {
                    return false;
                }

                $receiptDate = $receipt->received_at?->copy()->startOfDay();
                if (! $receiptDate || abs($receiptDate->diffInDays($targetDate)) > 3) {
                    return false;
                }

                if ($row['reference'] && $receipt->invoice?->number) {
                    return str_contains(strtolower($row['reference']), strtolower($receipt->invoice->number));
                }

                return true;
            })
            ->sortBy(fn (Receipt $receipt) => abs($receipt->received_at->diffInDays($targetDate)))
            ->first();
    }

    protected function findBillPaymentMatch(Collection $payments, array $row): ?BillPayment
    {
        $targetAmount = round((float) $row['amount'], 2);
        $targetDate = Carbon::parse($row['date'])->startOfDay();

        return $payments
            ->filter(function (BillPayment $payment) use ($targetAmount, $targetDate, $row) {
                if (round((float) $payment->amount, 2) !== $targetAmount) {
                    return false;
                }

                $paidDate = $payment->paid_at?->copy()->startOfDay();
                if (! $paidDate || abs($paidDate->diffInDays($targetDate)) > 3) {
                    return false;
                }

                if ($row['reference'] && $payment->bill?->number) {
                    return str_contains(strtolower($row['reference']), strtolower($payment->bill->number));
                }

                return true;
            })
            ->sortBy(fn (BillPayment $payment) => abs($payment->paid_at->diffInDays($targetDate)))
            ->first();
    }

    protected function normalizeHeader(string $value): string
    {
        return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '_', $value), '_'));
    }

    protected function parseAmount(array $row): ?float
    {
        foreach (['amount', 'credit', 'debit', 'value', 'transaction_amount'] as $key) {
            if (! isset($row[$key]) || $row[$key] === '') {
                continue;
            }

            $raw = str_replace([',', ' '], '', $row[$key]);
            if ($raw === '') {
                continue;
            }

            return (float) $raw;
        }

        $credit = isset($row['credit']) ? (float) str_replace(',', '', $row['credit']) : 0.0;
        $debit = isset($row['debit']) ? (float) str_replace(',', '', $row['debit']) : 0.0;

        if ($credit > 0) {
            return $credit;
        }

        if ($debit > 0) {
            return -$debit;
        }

        return null;
    }

    protected function parseDate(array $row): ?Carbon
    {
        foreach (['date', 'transaction_date', 'posted_date', 'value_date'] as $key) {
            if (empty($row[$key])) {
                continue;
            }

            try {
                return Carbon::parse($row[$key])->startOfDay();
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    protected function parseReference(array $row): ?string
    {
        foreach (['reference', 'description', 'narration', 'details', 'memo'] as $key) {
            if (! empty($row[$key])) {
                return trim($row[$key]);
            }
        }

        return null;
    }
}
