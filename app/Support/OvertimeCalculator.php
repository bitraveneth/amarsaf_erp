<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\EmployeeContract;

class OvertimeCalculator
{
    public const DEFAULT_MONTHLY_DAYS = 26;

    public const DEFAULT_DAILY_HOURS = 8;

    public function activeContract(Employee $employee): ?EmployeeContract
    {
        return $employee->contracts()
            ->where('status', EmployeeContract::STATUS_ACTIVE)
            ->orderByDesc('start_date')
            ->first();
    }

    public function hourlyRate(Employee $employee): float
    {
        $contract = $this->activeContract($employee);
        $monthlyBase = (float) ($contract?->salary_amount ?? 0);

        if ($monthlyBase <= 0) {
            return 0.0;
        }

        return round($monthlyBase / (self::DEFAULT_MONTHLY_DAYS * self::DEFAULT_DAILY_HOURS), 2);
    }

    public function amount(float $hours, float $hourlyRate, float $multiplier = 1.5): float
    {
        return round(max(0, $hours) * max(0, $hourlyRate) * max(0, $multiplier), 2);
    }

    public function buildRecord(Employee $employee, string $workDate, float $hours, float $multiplier, ?float $hourlyRate = null): array
    {
        $rate = $hourlyRate ?? $this->hourlyRate($employee);

        return [
            'work_date' => $workDate,
            'hours' => round($hours, 2),
            'rate_multiplier' => round($multiplier, 2),
            'hourly_rate' => $rate,
            'amount' => $this->amount($hours, $rate, $multiplier),
        ];
    }
}
