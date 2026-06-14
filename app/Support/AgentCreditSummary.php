<?php

namespace App\Support;

use App\Models\Agent;
use App\Models\AgentAdvance;
use App\Models\Invoice;
use Illuminate\Support\Collection;

class AgentCreditSummary
{
    /**
     * @return array{
     *     credit_limit: float,
     *     outstanding: float,
     *     available_credit: float,
     *     open_advance: float,
     *     used_pct: float,
     *     status: string,
     *     status_label: string,
     *     recent_advances: Collection<int, AgentAdvance>
     * }
     */
    public static function forAgent(Agent $agent): array
    {
        $creditLimit = (float) ($agent->credit_limit ?? 0);

        $outstanding = (float) Invoice::query()
            ->whereHas('order', fn ($query) => $query->where('agent_id', $agent->id))
            ->get()
            ->sum(fn (Invoice $invoice) => (float) $invoice->outstanding);

        $openAdvance = (float) AgentAdvance::query()
            ->where('agent_id', $agent->id)
            ->whereIn('status', ['open', 'partial'])
            ->selectRaw('COALESCE(SUM(amount - applied_amount), 0) as balance')
            ->value('balance');

        $availableCredit = max(0, $creditLimit - $outstanding);
        $usedPct = $creditLimit > 0 ? min(100, round(($outstanding / $creditLimit) * 100, 1)) : 0;

        $status = 'ok';
        $statusLabel = 'Within limit';

        if ($creditLimit <= 0) {
            $status = 'none';
            $statusLabel = 'No credit limit set';
        } elseif ($outstanding >= $creditLimit) {
            $status = 'exhausted';
            $statusLabel = 'Limit reached';
        } elseif ($usedPct >= 80) {
            $status = 'warning';
            $statusLabel = 'Near limit';
        }

        $recentAdvances = AgentAdvance::query()
            ->where('agent_id', $agent->id)
            ->orderByDesc('advanced_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return [
            'credit_limit' => $creditLimit,
            'outstanding' => $outstanding,
            'available_credit' => $availableCredit,
            'open_advance' => $openAdvance,
            'used_pct' => $usedPct,
            'status' => $status,
            'status_label' => $statusLabel,
            'recent_advances' => $recentAdvances,
        ];
    }
}
