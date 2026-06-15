<?php

namespace App\Services\Accounting;

use Carbon\Carbon;

class PlDriverLinkService
{
    /**
     * @return array<string, mixed>
     */
    public function driverCard(
        string $key,
        string $label,
        float $amount,
        float $netRevenue,
        Carbon $from,
        Carbon $to,
        ?float $priorAmount = null
    ): array {
        $pct = abs($netRevenue) >= 0.01
            ? round(abs($amount) / abs($netRevenue) * 100, 1)
            : null;

        $delta = null;
        if ($priorAmount !== null && abs($priorAmount) >= 0.01) {
            $delta = round((($amount - $priorAmount) / abs($priorAmount)) * 100, 1);
        }

        return [
            'key' => $key,
            'label' => $label,
            'amount' => $amount,
            'amount_display' => number_format(abs($amount), 0),
            'pct_display' => $pct !== null ? $pct . '%' : '—',
            'href' => $this->href($key, $from, $to),
            'module' => $key,
            'delta_pct' => $delta,
            'delta_tone' => $delta === null ? 'neutral' : ($delta <= 0 ? 'positive' : 'negative'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function accountLink(?string $slug, Carbon $from, Carbon $to): ?array
    {
        if (! $slug) {
            return null;
        }

        $href = $this->href($slug, $from, $to);

        if (! $href) {
            return null;
        }

        return [
            'href' => $href,
            'title' => $this->accountLinkTitle($slug),
        ];
    }

    protected function href(string $key, Carbon $from, Carbon $to): ?string
    {
        $params = ['from' => $from->toDateString(), 'to' => $to->toDateString()];

        return match ($key) {
            'commission', 'commission_expense' => route('admin.reports.commissions', array_merge($params, ['month' => $from->format('Y-m')])),
            'payroll', 'salaries_wages' => route('admin.reports.payroll', $params),
            'delivery', 'delivery_expense', 'vehicle_fuel', 'courier_expense' => route('admin.reports.logistics-bills', $params),
            'manufacturing', 'cogs' => route('admin.reports.manufacturing-schedule', $params),
            'production_variance' => route('admin.reports.production-variance', $params),
            'expenses' => route('admin.reports.expense-summary', $params),
            'utilities', 'utilities_expense', 'electricity_expense' => route('admin.reports.utilities', $params),
            default => null,
        };
    }

    protected function accountLinkTitle(string $slug): string
    {
        return match ($slug) {
            'commission_expense' => 'Agent performance',
            'salaries_wages' => 'Payroll distributions',
            'delivery_expense', 'vehicle_fuel', 'courier_expense' => 'Logistics bills',
            default => 'Source records',
        };
    }
}
