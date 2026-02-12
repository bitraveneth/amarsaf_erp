<?php

namespace Database\Seeders\Accounting;

use App\Models\LedgerEntry;
use App\Models\SalaryDistribution;
use Illuminate\Database\Seeder;

/**
 * Seed data for Accounting → Payroll.
 */
class PayrollModuleSeeder extends Seeder
{
    public function run(): void
    {
        $salary = SalaryDistribution::with('employee')->latest('period_start')->first();

        if (! $salary) {
            return;
        }

        $gross = $salary->base_salary
            + $salary->bonus
            + $salary->ta_allowances
            + $salary->da_allowances
            + $salary->commission;

        // DR Payroll Expense
        LedgerEntry::create([
            'account'     => 'Payroll Expense',
            'description' => 'Payroll for ' . $salary->employee->name,
            'debit'       => $gross,
            'credit'      => 0,
        ]);

        // CR Bank
        LedgerEntry::create([
            'account'     => 'Bank',
            'description' => 'Payroll payment for ' . $salary->employee->name,
            'debit'       => 0,
            'credit'      => $gross,
        ]);
    }
}
