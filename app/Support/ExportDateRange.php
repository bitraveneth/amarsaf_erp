<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ExportDateRange
{
    public static function presets(): array
    {
        return [
            'all' => [
                'label' => 'All records',
                'description' => 'Complete dataset',
            ],
            'today' => [
                'label' => 'Today',
                'description' => 'Records from today only',
            ],
            'week' => [
                'label' => 'Last 7 days',
                'description' => 'Rolling one week',
            ],
            '6weeks' => [
                'label' => 'Last 6 weeks',
                'description' => 'Rolling six weeks',
            ],
            'month' => [
                'label' => 'This month',
                'description' => 'Current calendar month',
            ],
            '3months' => [
                'label' => 'Last 3 months',
                'description' => 'Rolling quarter',
            ],
            '6months' => [
                'label' => 'Last 6 months',
                'description' => 'Rolling half year',
            ],
            'year' => [
                'label' => 'Last 12 months',
                'description' => 'Rolling year',
            ],
        ];
    }

    public static function selectedKey(Request $request): string
    {
        if ($request->filled('from') || $request->filled('to')) {
            return 'custom';
        }

        $key = (string) $request->query('range', 'all');

        return array_key_exists($key, self::presets()) ? $key : 'all';
    }

    public static function mode(Request $request): string
    {
        if ($request->filled('from') || $request->filled('to')) {
            return 'custom';
        }

        $key = self::selectedKey($request);

        return $key === 'all' ? 'all' : 'preset';
    }

    public static function formDefaults(Request $request): array
    {
        $resolved = self::resolve($request);

        if ($resolved && $resolved['from'] && $resolved['to']) {
            return [
                'from' => $resolved['from']->toDateString(),
                'to' => $resolved['to']->toDateString(),
            ];
        }

        return [
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ];
    }

    public static function quickFillDates(string $preset): array
    {
        $now = now();

        return match ($preset) {
            'today' => [
                'from' => $now->toDateString(),
                'to' => $now->toDateString(),
            ],
            'week' => [
                'from' => $now->copy()->subDays(6)->toDateString(),
                'to' => $now->toDateString(),
            ],
            '6weeks' => [
                'from' => $now->copy()->subWeeks(6)->toDateString(),
                'to' => $now->toDateString(),
            ],
            'month' => [
                'from' => $now->copy()->startOfMonth()->toDateString(),
                'to' => $now->copy()->endOfMonth()->toDateString(),
            ],
            '3months' => [
                'from' => $now->copy()->subMonths(3)->toDateString(),
                'to' => $now->toDateString(),
            ],
            '6months' => [
                'from' => $now->copy()->subMonths(6)->toDateString(),
                'to' => $now->toDateString(),
            ],
            'year' => [
                'from' => $now->copy()->subYear()->toDateString(),
                'to' => $now->toDateString(),
            ],
            default => [
                'from' => $now->copy()->startOfMonth()->toDateString(),
                'to' => $now->toDateString(),
            ],
        };
    }

    public static function resolve(Request $request): ?array
    {
        if ($request->filled('from') || $request->filled('to')) {
            $from = $request->filled('from')
                ? Carbon::parse($request->query('from'))->startOfDay()
                : null;
            $to = $request->filled('to')
                ? Carbon::parse($request->query('to'))->endOfDay()
                : now()->endOfDay();

            return [
                'key' => 'custom',
                'label' => 'Custom range',
                'description' => trim(($from?->format('d M Y') ?? '…') . ' – ' . ($to?->format('d M Y') ?? '…')),
                'from' => $from,
                'to' => $to,
            ];
        }

        $key = self::selectedKey($request);

        if ($key === 'all') {
            return null;
        }

        $preset = self::presets()[$key];
        $now = now();

        [$from, $to] = match ($key) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            '6weeks' => [$now->copy()->subWeeks(6)->startOfDay(), $now->copy()->endOfDay()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            '3months' => [$now->copy()->subMonths(3)->startOfDay(), $now->copy()->endOfDay()],
            '6months' => [$now->copy()->subMonths(6)->startOfDay(), $now->copy()->endOfDay()],
            'year' => [$now->copy()->subYear()->startOfDay(), $now->copy()->endOfDay()],
            default => [null, null],
        };

        return [
            'key' => $key,
            'label' => $preset['label'],
            'description' => $preset['description'],
            'from' => $from,
            'to' => $to,
        ];
    }

    public static function label(Request $request): string
    {
        $resolved = self::resolve($request);

        return $resolved['label'] ?? self::presets()['all']['label'];
    }

    public static function periodSummary(Request $request): string
    {
        $resolved = self::resolve($request);

        if (! $resolved) {
            return 'All records';
        }

        if ($resolved['from'] && $resolved['to']) {
            return $resolved['from']->format('d M Y') . ' – ' . $resolved['to']->format('d M Y');
        }

        return $resolved['label'];
    }

    public static function queryParams(Request $request): array
    {
        $key = self::selectedKey($request);

        if ($key === 'all' && ! $request->filled('from') && ! $request->filled('to')) {
            return ['range' => 'all'];
        }

        if ($request->filled('from') || $request->filled('to')) {
            return array_filter([
                'from' => $request->query('from'),
                'to' => $request->query('to'),
            ]);
        }

        return ['range' => $key];
    }

    public static function apply(Builder $query, Request $request, string $column): Builder
    {
        $resolved = self::resolve($request);

        if (! $resolved || (! $resolved['from'] && ! $resolved['to'])) {
            return $query;
        }

        $from = $resolved['from'];
        $to = $resolved['to'] ?? now()->endOfDay();

        if ($from && $to) {
            $query->whereBetween($column, [$from, $to]);
        } elseif ($from) {
            $query->where($column, '>=', $from);
        } elseif ($to) {
            $query->where($column, '<=', $to);
        }

        return $query;
    }

    public static function filenameSuffix(Request $request): string
    {
        $resolved = self::resolve($request);

        if (! $resolved) {
            return '';
        }

        if ($resolved['key'] === 'custom' && $resolved['from'] && $resolved['to']) {
            return '-' . $resolved['from']->format('Y-m-d') . '_to_' . $resolved['to']->format('Y-m-d');
        }

        return '-' . str_replace(' ', '-', strtolower($resolved['key']));
    }
}
