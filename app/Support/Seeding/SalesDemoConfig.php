<?php

namespace App\Support\Seeding;

class SalesDemoConfig
{
    public static function enabled(): bool
    {
        if (app()->environment('testing')) {
            return filter_var((string) env('SEED_SALES_DEMO', true), FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var((string) env('SEED_SALES_DEMO', true), FILTER_VALIDATE_BOOLEAN);
    }

    public static function years(): int
    {
        return max(1, (int) env('SEED_DEMO_YEARS', 1));
    }

    public static function scale(): string
    {
        $scale = strtolower((string) env('SEED_DEMO_SCALE', 'large'));

        return in_array($scale, ['medium', 'large'], true) ? $scale : 'large';
    }

    public static function isLarge(): bool
    {
        return self::scale() === 'large';
    }

    /** @return array{ordersMin: int, ordersMax: int, dailyChance: int, productionPerWeek: int} */
    public static function volumeProfile(): array
    {
        if (self::isLarge()) {
            return [
                'ordersMin' => 3,
                'ordersMax' => 6,
                'dailyChance' => 68,
                'productionPerWeek' => 4,
            ];
        }

        return [
            'ordersMin' => 1,
            'ordersMax' => 3,
            'dailyChance' => 42,
            'productionPerWeek' => 2,
        ];
    }

    public static function totalDays(): int
    {
        return self::years() * 365;
    }
}
