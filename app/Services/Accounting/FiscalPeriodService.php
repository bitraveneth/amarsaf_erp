<?php

namespace App\Services\Accounting;

use App\Models\AccountingPeriod;
use App\Models\FiscalYear;
use Illuminate\Support\Carbon;

class FiscalPeriodService
{
    public function ensureCurrentYear(): FiscalYear
    {
        $year = (int) now()->format('Y');

        $fiscalYear = FiscalYear::query()
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->first();

        if ($fiscalYear) {
            return $fiscalYear;
        }

        return $this->createYear($year);
    }

    public function createYear(int $year): FiscalYear
    {
        $start = Carbon::create($year, 1, 1);
        $end = Carbon::create($year, 12, 31);

        $fiscalYear = FiscalYear::create([
            'name' => (string) $year,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_closed' => false,
        ]);

        for ($month = 1; $month <= 12; $month++) {
            $periodStart = Carbon::create($year, $month, 1);
            $periodEnd = $periodStart->copy()->endOfMonth();

            AccountingPeriod::create([
                'fiscal_year_id' => $fiscalYear->id,
                'name' => $periodStart->format('F Y'),
                'period_number' => $month,
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
                'is_closed' => false,
            ]);
        }

        return $fiscalYear->fresh('periods');
    }

    public function closePeriod(AccountingPeriod $period): void
    {
        $period->update(['is_closed' => true]);
    }

    public function openPeriod(AccountingPeriod $period): void
    {
        $period->update(['is_closed' => false]);
    }
}
