<?php

namespace App\Support;

use App\Helpers\SystemSettings;
use App\Models\Agent;
use App\Models\AgentCommissionRule;
use Illuminate\Support\Facades\DB;

class AgentCommissionSync
{
    public static function defaultRateForForm(): ?string
    {
        $rate = SystemSettings::get('agent_default_commission_rate');

        if ($rate === null || $rate === '') {
            return null;
        }

        return rtrim(rtrim(number_format((float) $rate, 2), '0'), '.');
    }

    public static function saveDefaultRate(mixed $rate): void
    {
        $numericRate = ($rate === null || $rate === '') ? null : (float) $rate;

        if ($numericRate === null || $numericRate <= 0) {
            SystemSettings::forget(['agent_default_commission_rate']);

            return;
        }

        SystemSettings::putMany([
            'agent_default_commission_rate' => (string) $numericRate,
        ]);
    }

    public static function applyDefaultToAllAgents(): int
    {
        $rate = self::defaultRateForForm();

        if ($rate === null) {
            return 0;
        }

        $count = 0;

        Agent::query()->orderBy('id')->each(function (Agent $agent) use ($rate, &$count) {
            self::sync($agent, $rate);
            $count++;
        });

        return $count;
    }

    public static function rateForForm(Agent $agent): ?string
    {
        $rule = $agent->relationLoaded('commissions')
            ? $agent->commissions->first(
                fn (AgentCommissionRule $rule) => ($rule->frequency ?? 'monthly') === 'monthly'
                    && ! filled($rule->sku)
                    && ! self::isAdvanced($rule)
            )
            : $agent->commissions()
                ->where('frequency', 'monthly')
                ->whereNull('sku')
                ->orderBy('id')
                ->get()
                ->first(fn (AgentCommissionRule $rule) => ! self::isAdvanced($rule));

        if (! $rule || $rule->type !== 'percentage') {
            return null;
        }

        return rtrim(rtrim(number_format((float) $rule->value, 2), '0'), '.');
    }

    public static function sync(Agent $agent, mixed $rate, string $frequency = 'monthly'): void
    {
        $numericRate = ($rate === null || $rate === '') ? null : (float) $rate;

        DB::transaction(function () use ($agent, $numericRate, $frequency) {
            AgentCommissionRule::where('agent_id', $agent->id)->delete();

            if ($numericRate === null || $numericRate <= 0) {
                return;
            }

            AgentCommissionRule::create([
                'agent_id' => $agent->id,
                'sku' => null,
                'type' => 'percentage',
                'value' => $numericRate,
                'order_type' => null,
                'frequency' => $frequency,
                'threshold_min' => null,
                'threshold_max' => null,
            ]);
        });
    }

    /**
     * @return array{
     *     commission_label: string,
     *     payment_label: string,
     *     prices_label: string,
     *     commission_rate: string|null,
     *     has_custom_prices: bool
     * }
     */
    public static function summary(Agent $agent): array
    {
        $agent->loadMissing(['commissions', 'priceLists']);

        $rule = $agent->commissions->first();
        $commissionLabel = 'Not set';
        $paymentLabel = 'Every month';
        $commissionRate = null;

        if ($rule) {
            if ($rule->type === 'percentage' && ! self::isAdvanced($rule)) {
                $commissionRate = rtrim(rtrim(number_format((float) $rule->value, 2), '0'), '.');
                $commissionLabel = $commissionRate . '% on sales';
            } elseif ($rule->type === 'percentage') {
                $commissionLabel = rtrim(rtrim(number_format((float) $rule->value, 2), '0'), '.') . '% (custom rules)';
            } else {
                $commissionLabel = 'BDT ' . number_format((float) $rule->value, 0) . ' (custom rules)';
            }

            $paymentLabel = ($rule->frequency ?? 'monthly') === 'per_order'
                ? 'On each order'
                : 'Every month';
        }

        $hasCustomPrices = $agent->priceLists->isNotEmpty();

        return [
            'commission_label' => $commissionLabel,
            'payment_label' => $paymentLabel,
            'prices_label' => $hasCustomPrices ? 'Custom prices for this agent' : 'Standard catalog prices',
            'commission_rate' => $commissionRate,
            'has_custom_prices' => $hasCustomPrices,
        ];
    }

    public static function isAdvanced(AgentCommissionRule $rule): bool
    {
        if ($rule->type === 'fixed') {
            return true;
        }

        return filled($rule->sku)
            || filled($rule->order_type)
            || filled($rule->threshold_min)
            || filled($rule->threshold_max);
    }
}
