<?php

namespace App\Services\Accounting;

use App\Models\JournalEntry;
use Carbon\Carbon;

class PlDashboardPresenter
{
    public function __construct(protected PlDriverLinkService $driverLinks)
    {
    }

    /**
     * @param  array<string, mixed>  $statement
     * @param  array<string, mixed>|null  $priorStatement
     * @return array<string, mixed>
     */
    public function present(
        array $statement,
        string $currencyCode,
        string $periodLabel,
        Carbon $from,
        Carbon $to,
        ?array $priorStatement = null
    ): array {
        $netRevenue = (float) ($statement['netRevenue'] ?? 0);
        $netProfit = (float) ($statement['netProfit'] ?? 0);
        $priorNetProfit = $priorStatement ? (float) ($priorStatement['netProfit'] ?? 0) : null;

        return [
            'hero' => $this->hero($statement, $currencyCode, $periodLabel, $priorNetProfit),
            'waterfall' => $this->waterfall($statement),
            'drivers' => $this->drivers($statement, $netRevenue, $from, $to, $priorStatement),
            'sticky' => [
                'net_revenue' => number_format($netRevenue, 0),
                'gross_profit' => number_format((float) ($statement['grossProfit'] ?? 0), 0),
                'net_profit' => number_format($netProfit, 0),
            ],
            'trust' => $this->trustMeta($from, $to),
        ];
    }

    /**
     * @param  array<string, mixed>  $statement
     * @return array<string, mixed>
     */
    protected function hero(array $statement, string $currencyCode, string $periodLabel, ?float $priorNetProfit): array
    {
        $netRevenue = (float) ($statement['netRevenue'] ?? 0);
        $grossProfit = (float) ($statement['grossProfit'] ?? 0);
        $operatingProfit = (float) ($statement['operatingProfit'] ?? 0);
        $netProfit = (float) ($statement['netProfit'] ?? 0);

        $grossMargin = $netRevenue > 0 ? round($grossProfit / $netRevenue * 100, 1) : 0;
        $operatingMargin = $netRevenue > 0 ? round($operatingProfit / $netRevenue * 100, 1) : 0;
        $netMargin = $netRevenue > 0 ? round($netProfit / $netRevenue * 100, 1) : 0;

        $delta = null;
        if ($priorNetProfit !== null && abs($priorNetProfit) >= 0.01) {
            $deltaPct = round((($netProfit - $priorNetProfit) / abs($priorNetProfit)) * 100, 1);
            $delta = [
                'pct' => $deltaPct,
                'label' => ($deltaPct >= 0 ? '+' : '') . $deltaPct . '% vs prior period',
                'tone' => $deltaPct >= 0 ? 'positive' : 'negative',
            ];
        }

        return [
            'currency' => $currencyCode,
            'period_label' => $periodLabel,
            'net_profit' => $netProfit,
            'net_profit_display' => number_format($netProfit, 0),
            'tone' => $netProfit >= 0 ? 'positive' : 'negative',
            'gross_margin' => $grossMargin,
            'operating_margin' => $operatingMargin,
            'net_margin' => $netMargin,
            'delta' => $delta,
        ];
    }

    /**
     * @param  array<string, mixed>  $statement
     * @return array<int, array<string, mixed>>
     */
    protected function waterfall(array $statement): array
    {
        $netRevenue = (float) ($statement['netRevenue'] ?? 0);
        $cogs = abs((float) ($statement['manufacturingCost'] ?? 0));
        $admin = abs((float) ($statement['administrativeExpenses'] ?? 0));
        $selling = abs((float) ($statement['sellingExpenses'] ?? 0));
        $financial = abs((float) ($statement['financialExpenses'] ?? 0));
        $netProfit = (float) ($statement['netProfit'] ?? 0);

        return [
            ['label' => 'Net revenue', 'value' => $netRevenue, 'kind' => 'positive'],
            ['label' => 'COGS', 'value' => -$cogs, 'kind' => 'negative'],
            ['label' => 'Admin', 'value' => -$admin, 'kind' => 'negative'],
            ['label' => 'Selling', 'value' => -$selling, 'kind' => 'negative'],
            ['label' => 'Financial', 'value' => -$financial, 'kind' => 'negative'],
            ['label' => 'Net profit', 'value' => $netProfit, 'kind' => $netProfit >= 0 ? 'total' : 'negative-total'],
        ];
    }

    /**
     * @param  array<string, mixed>  $statement
     * @param  array<string, mixed>|null  $priorStatement
     * @return array<int, array<string, mixed>>
     */
    protected function drivers(
        array $statement,
        float $netRevenue,
        Carbon $from,
        Carbon $to,
        ?array $priorStatement
    ): array {
        $defs = [
            ['key' => 'manufacturing', 'label' => 'Manufacturing COGS', 'amount' => (float) ($statement['manufacturingCost'] ?? 0)],
            ['key' => 'payroll', 'label' => 'Payroll', 'amount' => (float) ($statement['payrollExpense'] ?? 0)],
            ['key' => 'commission', 'label' => 'Commission', 'amount' => (float) ($statement['commissionExpense'] ?? 0)],
            ['key' => 'delivery', 'label' => 'Delivery & logistics', 'amount' => (float) ($statement['deliveryExpense'] ?? 0)],
        ];

        return collect($defs)
            ->map(function (array $def) use ($netRevenue, $from, $to, $priorStatement) {
                $priorAmount = null;
                if ($priorStatement) {
                    $priorAmount = match ($def['key']) {
                        'manufacturing' => (float) ($priorStatement['manufacturingCost'] ?? 0),
                        'payroll' => (float) ($priorStatement['payrollExpense'] ?? 0),
                        'commission' => (float) ($priorStatement['commissionExpense'] ?? 0),
                        'delivery' => (float) ($priorStatement['deliveryExpense'] ?? 0),
                        default => null,
                    };
                }

                return $this->driverLinks->driverCard(
                    $def['key'],
                    $def['label'],
                    $def['amount'],
                    $netRevenue,
                    $from,
                    $to,
                    $priorAmount
                );
            })
            ->filter(fn (array $card) => abs($card['amount']) >= 0.01)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function trustMeta(Carbon $from, Carbon $to): array
    {
        $lastJournal = JournalEntry::query()
            ->where('status', 'posted')
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString())
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->first();

        return [
            'last_journal_at' => $lastJournal?->entry_date?->format('d M Y'),
            'last_journal_ref' => $lastJournal?->number,
        ];
    }
}
