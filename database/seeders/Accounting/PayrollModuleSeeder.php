<?php

namespace Database\Seeders\Accounting;

use App\Models\SalaryDistribution;
use App\Services\Accounting\PayrollPostingService;
use Illuminate\Database\Seeder;

/**
 * Seed data for Accounting → Payroll.
 */
class PayrollModuleSeeder extends Seeder
{
    public function run(): void
    {
        $posting = app(PayrollPostingService::class);

        SalaryDistribution::query()
            ->with('employee')
            ->each(function (SalaryDistribution $distribution) use ($posting) {
                if ((float) $distribution->base_salary <= 0 && (float) $distribution->bonus <= 0) {
                    return;
                }

                $posting->sync($distribution);
            });
    }
}
