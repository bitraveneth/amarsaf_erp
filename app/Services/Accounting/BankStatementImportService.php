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

    /**
     * Build a CSV that matches unreconciled ERP receipts (inflows) and supplier
     * payments (outflows), plus two unmatched bank-only lines for review.
     *
     * @return array{csv: string, line_count: int, used_fallback: bool}
     */
    public function buildDemoStatementCsv(Carbon $from, Carbon $to, int $receiptLimit = 8, int $paymentLimit = 4): array
    {
        $receipts = Receipt::with('invoice')
            ->where('reconciled', false)
            ->whereBetween('received_at', [$from, $to])
            ->orderByDesc('received_at')
            ->limit($receiptLimit)
            ->get();

        $payments = BillPayment::with('bill')
            ->where('reconciled', false)
            ->whereBetween('paid_at', [$from, $to])
            ->orderByDesc('paid_at')
            ->limit($paymentLimit)
            ->get();

        $usedFallback = false;

        if ($receipts->isEmpty() && $payments->isEmpty()) {
            $usedFallback = true;
            $receipts = Receipt::with('invoice')
                ->where('reconciled', false)
                ->orderByDesc('received_at')
                ->limit($receiptLimit)
                ->get();
            $payments = BillPayment::with('bill')
                ->where('reconciled', false)
                ->orderByDesc('paid_at')
                ->limit($paymentLimit)
                ->get();
        }

        $lines = [];

        foreach ($receipts as $receipt) {
            $date = $receipt->received_at?->toDateString() ?? $from->toDateString();
            $lines[] = [
                $date,
                number_format((float) $receipt->amount, 2, '.', ''),
                $receipt->invoice?->number ?? '',
                'Agent collection',
            ];
        }

        foreach ($payments as $payment) {
            $date = $payment->paid_at?->toDateString() ?? $from->toDateString();
            $lines[] = [
                $date,
                number_format(-1 * abs((float) $payment->amount), 2, '.', ''),
                $payment->bill?->number ?? '',
                'Supplier payment',
            ];
        }

        $anchor = optional($receipts->first())->received_at
            ?? optional($payments->first())->paid_at
            ?? $from->copy();

        $lines[] = [
            $anchor->copy()->addDay()->toDateString(),
            '-12.75',
            'CHQ-BANK',
            'Bank service charge (no ERP match)',
        ];
        $lines[] = [
            $anchor->copy()->addDays(2)->toDateString(),
            '3.25',
            'INT-DEMO',
            'Account interest (no ERP match)',
        ];

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Date', 'Amount', 'Reference', 'Description']);
        foreach ($lines as $line) {
            fputcsv($handle, $line);
        }
        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return [
            'csv' => $csv,
            'line_count' => count($lines),
            'used_fallback' => $usedFallback,
        ];
    }

    /**
     * Session-safe preview of import matches (no Eloquent models).
     */
    public function summarizeMatches(array $matches): array
    {
        return collect($matches)
            ->map(function (array $row) {
                $match = $row['match'] ?? null;
                $label = null;

                if ($match instanceof Receipt) {
                    $label = $match->invoice?->number ?? ('Receipt #'.$match->id);
                } elseif ($match instanceof BillPayment) {
                    $label = $match->bill?->number ?? ('Payment #'.$match->id);
                }

                $date = $row['date'] ?? null;
                if ($date instanceof Carbon) {
                    $date = $date->toDateString();
                }

                return [
                    'date' => (string) $date,
                    'amount' => (float) ($row['amount'] ?? 0),
                    'direction' => $row['direction'] ?? 'inflow',
                    'reference' => $row['reference'] ?? null,
                    'match_type' => $row['match_type'] ?? null,
                    'matched' => (bool) ($row['match_id'] ?? null),
                    'match_label' => $label,
                ];
            })
            ->values()
            ->all();
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
