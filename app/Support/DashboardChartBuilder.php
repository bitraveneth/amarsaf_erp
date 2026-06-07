<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardChartBuilder
{
    /**
     * @return array{labels: array<int, string>, values: array<int, float>, secondary: array<int, float>}
     */
    public static function revenueAndCollectionsSeries(Carbon $from, Carbon $to): array
    {
        $labels = [];
        $revenue = [];
        $collections = [];

        if ($from->diffInDays($to) <= 31) {
            $cursor = $from->copy()->startOfDay();

            while ($cursor->lte($to)) {
                $dayEnd = $cursor->copy()->endOfDay();
                $labels[] = $cursor->format('d M');
                $revenue[] = self::invoiceNetForRange($cursor, $dayEnd);
                $collections[] = self::receiptsForRange($cursor, $dayEnd);
                $cursor->addDay();
            }

            return [
                'labels' => $labels,
                'values' => $revenue,
                'secondary' => $collections,
            ];
        }

        $cursor = $from->copy()->startOfMonth();
        $endMonth = $to->copy()->startOfMonth();

        while ($cursor->lte($endMonth)) {
            $periodStart = $cursor->copy()->startOfMonth();
            $periodEnd = $cursor->isSameMonth($to) && $cursor->isSameYear($to)
                ? $to->copy()->endOfDay()
                : $cursor->copy()->endOfMonth();

            if ($periodEnd->lt($from)) {
                $cursor->addMonth();
                continue;
            }

            if ($periodStart->lt($from)) {
                $periodStart = $from->copy()->startOfDay();
            }

            $labels[] = $cursor->format('M Y');
            $revenue[] = self::invoiceNetForRange($periodStart, $periodEnd);
            $collections[] = self::receiptsForRange($periodStart, $periodEnd);
            $cursor->addMonth();
        }

        return [
            'labels' => $labels,
            'values' => $revenue,
            'secondary' => $collections,
        ];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    public static function costBreakdownSeries(array $rows): array
    {
        $filtered = collect($rows)
            ->filter(fn (array $row) => ($row['value'] ?? 0) > 0)
            ->sortByDesc('value')
            ->values();

        return [
            'labels' => $filtered->pluck('label')->all(),
            'values' => $filtered->pluck('value')->map(fn ($v) => round((float) $v, 2))->all(),
        ];
    }

    protected static function invoiceNetForRange(Carbon $from, Carbon $to): float
    {
        return round((float) Invoice::query()
            ->whereBetween('issued_at', [$from, $to])
            ->get()
            ->sum(fn (Invoice $invoice) => $invoice->netSalesAfterCreditsInRange($from, $to)), 2);
    }

    protected static function receiptsForRange(Carbon $from, Carbon $to): float
    {
        return round((float) Receipt::whereBetween('received_at', [$from, $to])->sum('amount'), 2);
    }

    /**
     * @param  Collection<int, array{label: string, value: float|int, meta?: string}>  $items
     */
    public static function normalizeRankList(Collection $items, int $limit = 5): Collection
    {
        return $items
            ->sortByDesc('value')
            ->take($limit)
            ->values();
    }
}
