<?php

namespace App\Services\Accounting;

use App\Models\LogisticsBill;
use Illuminate\Support\Carbon;

class LogisticsBillPostingService
{
    public function __construct(protected AccountingService $accounting)
    {
    }

    public function sync(LogisticsBill $bill): void
    {
        $this->accounting->deleteByJournalTypeAndSource('logistics_bill', LogisticsBill::class, $bill->id);

        $net = (float) $bill->net_total;
        $vat = (float) $bill->vat_amount;

        if ($net <= 0 && $vat <= 0) {
            return;
        }

        if ($bill->status === 'draft') {
            return;
        }

        $bill->loadMissing('transportCarrier');
        $description = 'Logistics bill ' . $bill->document_number;
        $analytic = 'carrier:' . $bill->transport_carrier_id;

        $lines = [
            [
                'account_key' => $this->expenseAccountKey($bill),
                'debit' => $net,
                'credit' => 0,
                'description' => $description,
                'analytic_label' => $analytic,
            ],
        ];

        if ($vat > 0) {
            $lines[] = [
                'account_key' => 'input_vat',
                'debit' => $vat,
                'credit' => 0,
                'description' => $description,
                'analytic_label' => $analytic,
            ];
        }

        $lines[] = [
            'account_key' => 'trade_creditors',
            'debit' => 0,
            'credit' => $net + $vat,
            'description' => $description,
            'analytic_label' => $analytic,
        ];

        $this->accounting->post(
            'logistics_bill',
            Carbon::parse($bill->bill_date),
            $lines,
            [
                'description' => $description,
                'source_type' => LogisticsBill::class,
                'source_id' => $bill->id,
            ]
        );
    }

    protected function expenseAccountKey(LogisticsBill $bill): string
    {
        return match ($bill->service_type) {
            LogisticsBill::SERVICE_COURIER => 'courier_expense',
            default => 'delivery_expense',
        };
    }
}
