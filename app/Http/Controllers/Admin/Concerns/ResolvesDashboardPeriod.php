<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

trait ResolvesDashboardPeriod
{
    protected function dashboardRangeOptions(): array
    {
        return [
            '7d' => 'Last 7 days',
            'month' => 'This month',
            'quarter' => 'This quarter',
            'year' => 'This year',
            'custom' => 'Custom range',
        ];
    }

    protected function resolveDashboardPeriod(Request $request, string $defaultRange = 'month'): array
    {
        $range = $request->query('range');
        $hasCustomDates = $request->filled('from') || $request->filled('to');
        $today = Carbon::today();

        if ($range === 'custom' || (! $range && $hasCustomDates)) {
            $from = $request->filled('from')
                ? Carbon::parse($request->query('from'))->startOfDay()
                : $today->copy()->startOfMonth();
            $to = $request->filled('to')
                ? Carbon::parse($request->query('to'))->endOfDay()
                : $today->copy()->endOfMonth();

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$from, $to, 'custom'];
        }

        switch ($range) {
            case '7d':
                $from = $today->copy()->subDays(6)->startOfDay();
                $to = $today->copy()->endOfDay();
                break;
            case 'quarter':
                $from = $today->copy()->startOfQuarter();
                $to = $today->copy()->endOfDay();
                break;
            case 'year':
                $from = $today->copy()->startOfYear();
                $to = $today->copy()->endOfDay();
                break;
            case 'month':
                $from = $today->copy()->startOfMonth();
                $to = $today->copy()->endOfDay();
                $range = 'month';
                break;
            default:
                $range = in_array($defaultRange, ['7d', 'month', 'quarter', 'year'], true)
                    ? $defaultRange
                    : 'month';

                return $this->resolveDashboardPeriod(
                    $request->duplicate(['range' => $range]),
                    $defaultRange
                );
        }

        return [$from, $to, $range];
    }

    protected function dashboardPeriodLabel(Carbon $from, Carbon $to): string
    {
        return $from->format('d M Y') . ' – ' . $to->format('d M Y');
    }
}
