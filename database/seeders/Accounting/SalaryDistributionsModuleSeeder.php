<?php

namespace Database\Seeders\Accounting;

use App\Models\Employee;
use App\Models\SalaryDistribution;
use Illuminate\Database\Seeder;

/**
 * Seed data for Accounting → Salary distributions.
 */
class SalaryDistributionsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $employee = Employee::where('name', 'Warehouse Manager')->first();

        if (! $employee) {
            return;
        }

        $from = now()->startOfMonth()->toDateString();
        $to   = now()->endOfMonth()->toDateString();

        SalaryDistribution::firstOrCreate(
            [
                'employee_id'  => $employee->id,
                'period_start' => $from,
                'period_end'   => $to,
            ],
            [
                'base_salary'    => 35000,
                'bonus'          => 5000,
                'ta_allowances'  => 2000,
                'da_allowances'  => 1500,
                'commission'     => 0,
                'payment_method' => 'bank',
                'document_path'  => null,
                'remarks'        => 'Demo monthly salary distribution',
            ]
        );
    }
}
