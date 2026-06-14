<?php

namespace App\Services\Accounting;

use App\Models\SalaryDistribution;
use Illuminate\Support\Carbon;

class PayrollPostingService
{
    public function __construct(
        protected AccountingService $accounting
    ) {
    }

    public function sync(SalaryDistribution $distribution): void
    {
        $this->accounting->deleteByJournalTypeAndSource('payroll', SalaryDistribution::class, $distribution->id);

        $total = round(
            (float) $distribution->base_salary
            + (float) $distribution->bonus
            + (float) $distribution->ta_allowances
            + (float) $distribution->da_allowances
            + (float) $distribution->commission
            + (float) $distribution->overtime_pay,
            2
        );

        if ($total <= 0) {
            return;
        }

        $distribution->loadMissing('employee');
        $analytic = $distribution->employee?->department;
        $creditKey = $this->creditAccountKey($distribution);
        $description = 'Payroll #' . $distribution->id . ' · ' . ($distribution->employee?->name ?? 'Employee');

        $lines = [
            [
                'account_key' => 'salaries_wages',
                'debit' => $total,
                'credit' => 0,
                'description' => $description,
                'analytic_label' => $analytic,
            ],
        ];

        if ((float) $distribution->bonus > 0) {
            // Bonus stays rolled into salaries_wages for simplicity; component split can be added later.
        }

        $lines[] = [
            'account_key' => $creditKey,
            'debit' => 0,
            'credit' => $total,
            'description' => $description,
            'analytic_label' => $analytic,
        ];

        $journal = $this->accounting->post(
            'payroll',
            Carbon::parse($distribution->period_end),
            $lines,
            [
                'description' => $description,
                'source_type' => SalaryDistribution::class,
                'source_id' => $distribution->id,
            ]
        );

        $distribution->forceFill(['journal_entry_id' => $journal->id])->save();
    }

    protected function creditAccountKey(SalaryDistribution $distribution): string
    {
        $paymentType = $distribution->payment_type ?: 'bank';

        if ($paymentType === 'payable') {
            return 'salary_payable';
        }

        if ($paymentType === 'cash') {
            return $distribution->payment_account_key ?: 'cash_in_hand';
        }

        return $distribution->payment_account_key ?: 'bank_default';
    }
}
