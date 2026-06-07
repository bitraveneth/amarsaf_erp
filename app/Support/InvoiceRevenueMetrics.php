<?php

namespace App\Support;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Receipt;
use Carbon\Carbon;

class InvoiceRevenueMetrics
{
    public static function creditNetAmountSql(string $invoiceTable, string $amountColumn): string
    {
        return "CASE
            WHEN {$amountColumn} <= 0 OR {$invoiceTable}.net_total <= 0 OR {$invoiceTable}.vat_amount <= 0
            THEN {$amountColumn}
            ELSE ROUND({$amountColumn} / (1 + ({$invoiceTable}.vat_amount / {$invoiceTable}.net_total)), 2)
        END";
    }

    public static function sumNetSalesAfterCreditsInRange(
        Carbon|string $issuedFrom,
        Carbon|string $issuedTo,
        Carbon|string $creditFrom,
        Carbon|string $creditTo,
        ?callable $constraint = null
    ): float {
        $issuedFromDate = self::normalizeDate($issuedFrom);
        $issuedToDate = self::normalizeDate($issuedTo);
        $creditFromDate = self::normalizeDate($creditFrom);
        $creditToDate = self::normalizeDate($creditTo);

        $creditNetSql = self::creditNetAmountSql('credit_invoices', 'credit_notes.amount');

        $creditSums = CreditNote::query()
            ->join('invoices as credit_invoices', 'credit_invoices.id', '=', 'credit_notes.invoice_id')
            ->select('credit_notes.invoice_id')
            ->selectRaw("SUM({$creditNetSql}) as credit_net")
            ->whereDate('credit_notes.issued_at', '>=', $creditFromDate)
            ->whereDate('credit_notes.issued_at', '<=', $creditToDate)
            ->groupBy('credit_notes.invoice_id');

        $query = Invoice::query()
            ->leftJoinSub($creditSums, 'period_credits', function ($join) {
                $join->on('period_credits.invoice_id', '=', 'invoices.id');
            })
            ->whereDate('invoices.issued_at', '>=', $issuedFromDate)
            ->whereDate('invoices.issued_at', '<=', $issuedToDate);

        if ($constraint) {
            $constraint($query);
        }

        return (float) ($query->selectRaw(
            'COALESCE(SUM(CASE
                WHEN invoices.net_total - COALESCE(period_credits.credit_net, 0) < 0 THEN 0
                ELSE invoices.net_total - COALESCE(period_credits.credit_net, 0)
            END), 0) as total'
        )->value('total') ?? 0);
    }

    public static function sumReceiptsInRange(Carbon|string $from, Carbon|string $to): float
    {
        return (float) Receipt::query()
            ->whereDate('received_at', '>=', self::normalizeDate($from))
            ->whereDate('received_at', '<=', self::normalizeDate($to))
            ->sum('amount');
    }

    public static function sumOutstandingAsOf(Carbon|string $asOf, ?callable $constraint = null): float
    {
        $asOfDate = self::normalizeDate($asOf);
        $total = 0.0;

        $query = Invoice::query()
            ->select(['id', 'net_total', 'vat_amount', 'withholding'])
            ->whereDate('issued_at', '<=', $asOfDate)
            ->whereIn('status', ['issued', 'adjusted'])
            ->with([
                'creditNotes:id,invoice_id,issued_at,amount',
                'receipts:id,invoice_id,received_at,amount',
                'advanceApplications:id,invoice_id,applied_at,amount',
            ]);

        if ($constraint) {
            $constraint($query);
        }

        $query->chunkById(100, function ($invoices) use (&$total, $asOfDate) {
            $total += $invoices->sum(function (Invoice $invoice) use ($asOfDate) {
                return $invoice->outstandingAsOf($asOfDate);
            });
        });

        return round($total, 2);
    }

    public static function countOverdueInvoices(Carbon|string $asOf): int
    {
        $asOfDate = self::normalizeDate($asOf);
        $count = 0;

        Invoice::query()
            ->select(['id', 'net_total', 'vat_amount', 'withholding', 'due_at'])
            ->whereIn('status', ['issued', 'adjusted'])
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', $asOfDate)
            ->with([
                'creditNotes:id,invoice_id,issued_at,amount',
                'receipts:id,invoice_id,received_at,amount',
                'advanceApplications:id,invoice_id,applied_at,amount',
            ])
            ->chunkById(100, function ($invoices) use (&$count, $asOfDate) {
                $count += $invoices->filter(function (Invoice $invoice) use ($asOfDate) {
                    return $invoice->outstandingAsOf($asOfDate) > 0.01;
                })->count();
            });

        return $count;
    }

    protected static function normalizeDate(Carbon|string $value): string
    {
        return ($value instanceof Carbon ? $value->copy() : Carbon::parse($value))->toDateString();
    }
}
