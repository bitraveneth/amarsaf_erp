<?php

namespace App\Support;

use App\Models\Agent;
use App\Models\AgentCommissionRule;
use App\Models\CompanyCommissionRule;
use App\Models\OrderItem;
use Illuminate\Support\Collection;

class CommissionPolicyResolver
{
    /** @var Collection<int, CompanyCommissionRule>|null */
    protected ?Collection $companyRules = null;

    /**
     * @return array{type: string, value: float, frequency: string, source: string}|null
     */
    public function resolveMonthlyForItem(Agent $agent, OrderItem $item, Collection $agentRules): ?array
    {
        $salesTotal = $item->realizedSalesTotal();
        if ($salesTotal <= 0) {
            return null;
        }

        $sku = $item->product?->sku;
        $monthlyRules = $agentRules->where('frequency', 'monthly');

        $skuRule = $this->bestMatchingAgentRule(
            $monthlyRules->filter(fn (AgentCommissionRule $rule) => filled($rule->sku) && $rule->sku === $sku),
            $salesTotal
        );
        if ($skuRule) {
            return $this->packRule($skuRule, 'agent_product');
        }

        $blanketRule = $monthlyRules->first(fn (AgentCommissionRule $rule) => ! filled($rule->sku) && ! AgentCommissionSync::isAdvanced($rule));
        if ($blanketRule) {
            return $this->packRule($blanketRule, 'agent_default');
        }

        $companyRule = $this->companyRules()->get($item->product_id);
        if ($companyRule && (float) $companyRule->value > 0) {
            return $this->packCompanyRule($companyRule, 'company_product');
        }

        $defaultRate = AgentCommissionSync::defaultRateForForm();
        if ($defaultRate !== null && (float) $defaultRate > 0) {
            return [
                'type' => 'percentage',
                'value' => (float) $defaultRate,
                'frequency' => 'monthly',
                'source' => 'company_default',
            ];
        }

        return null;
    }

    public function calculateMonthlyAmount(Agent $agent, OrderItem $item, Collection $agentRules): float
    {
        $resolved = $this->resolveMonthlyForItem($agent, $item, $agentRules);
        if (! $resolved) {
            return 0.0;
        }

        return $this->commissionAmount($resolved, $item->realizedSalesTotal());
    }

    public function calculatePerOrderAmount(
        Collection $agentRules,
        ?string $sku,
        string $orderType,
        float $lineTotal
    ): float {
        if ($lineTotal <= 0) {
            return 0.0;
        }

        $perOrderRules = $agentRules->where('frequency', 'per_order');
        $rule = $this->bestMatchingAgentRule(
            $perOrderRules->filter(fn (AgentCommissionRule $rule) => $this->agentRuleMatchesLine($rule, $sku, $orderType, $lineTotal)),
            $lineTotal
        );

        if ($rule) {
            return $this->commissionAmount($this->packRule($rule, 'agent'), $lineTotal);
        }

        return 0.0;
    }

    /**
     * @return array{type: string, value: float, frequency: string, source: string}|null
     */
    public function previewMonthlyForProduct(Agent $agent, int $productId, ?string $sku, Collection $agentRules): ?array
    {
        $monthlyRules = $agentRules->where('frequency', 'monthly');

        if ($sku) {
            $skuRule = $monthlyRules->first(fn (AgentCommissionRule $rule) => filled($rule->sku) && $rule->sku === $sku && ! AgentCommissionSync::isAdvanced($rule));
            if ($skuRule) {
                return $this->packRule($skuRule, 'agent_product');
            }
        }

        $blanketRule = $monthlyRules->first(fn (AgentCommissionRule $rule) => ! filled($rule->sku) && ! AgentCommissionSync::isAdvanced($rule));
        if ($blanketRule) {
            return $this->packRule($blanketRule, 'agent_default');
        }

        $companyRule = $this->companyRules()->get($productId);
        if ($companyRule && (float) $companyRule->value > 0) {
            return $this->packCompanyRule($companyRule, 'company_product');
        }

        $defaultRate = AgentCommissionSync::defaultRateForForm();
        if ($defaultRate !== null) {
            return [
                'type' => 'percentage',
                'value' => (float) $defaultRate,
                'frequency' => 'monthly',
                'source' => 'company_default',
            ];
        }

        return null;
    }

    public function sourceLabel(string $source): string
    {
        return match ($source) {
            'agent_product' => 'Agent product rate',
            'agent_default' => 'Agent default',
            'company_product' => 'Product rate',
            'company_default' => 'Company default',
            default => 'Custom',
        };
    }

    protected function companyRules(): Collection
    {
        return $this->companyRules ??= CompanyCommissionSync::rulesByProductId();
    }

    protected function bestMatchingAgentRule(Collection $rules, float $basisAmount): ?AgentCommissionRule
    {
        if ($rules->isEmpty()) {
            return null;
        }

        return $rules
            ->filter(fn (AgentCommissionRule $rule) => $this->thresholdMatches($rule, $basisAmount))
            ->sortByDesc(fn (AgentCommissionRule $rule) => (float) ($rule->threshold_min ?? 0))
            ->first();
    }

    protected function agentRuleMatchesLine(AgentCommissionRule $rule, ?string $sku, string $orderType, float $amount): bool
    {
        if ($rule->sku && $rule->sku !== $sku) {
            return false;
        }

        if ($rule->order_type && $rule->order_type !== $orderType) {
            return false;
        }

        return $this->thresholdMatches($rule, $amount);
    }

    protected function thresholdMatches(AgentCommissionRule $rule, float $basisAmount): bool
    {
        if ($rule->threshold_min !== null && $basisAmount < (float) $rule->threshold_min) {
            return false;
        }

        if ($rule->threshold_max !== null && $basisAmount > (float) $rule->threshold_max) {
            return false;
        }

        return true;
    }

    /**
     * @return array{type: string, value: float, frequency: string, source: string}
     */
    protected function packRule(AgentCommissionRule $rule, string $source): array
    {
        return [
            'type' => $rule->type,
            'value' => (float) $rule->value,
            'frequency' => $rule->frequency ?? 'monthly',
            'source' => $source,
        ];
    }

    /**
     * @return array{type: string, value: float, frequency: string, source: string}
     */
    protected function packCompanyRule(CompanyCommissionRule $rule, string $source): array
    {
        return [
            'type' => $rule->type,
            'value' => (float) $rule->value,
            'frequency' => $rule->frequency ?? 'monthly',
            'source' => $source,
        ];
    }

    /**
     * @param  array{type: string, value: float}  $resolved
     */
    protected function commissionAmount(array $resolved, float $basisAmount): float
    {
        if ($resolved['type'] === 'fixed') {
            return round($resolved['value'], 2);
        }

        return round($basisAmount * ($resolved['value'] / 100), 2);
    }
}
