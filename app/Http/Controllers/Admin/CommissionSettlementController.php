<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentCommissionSettlement;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CommissionSettlementController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $settlements = AgentCommissionSettlement::with('agent')
            ->whereBetween('period_start', [$from, $to])
            ->orderBy('agent_id')
            ->get();

        return view('admin.agents.settlements', compact('settlements', 'month'));
    }

    public function generate(Request $request)
    {
        $month = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $items = OrderItem::with(['order.agent', 'order.delivery.items'])
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('status', 'delivered')
                    ->whereHas('invoice', function ($invoiceQuery) use ($from, $to) {
                        $invoiceQuery->whereBetween('issued_at', [$from, $to]);
                    });
            })
            ->get();

        $byAgent = [];

        foreach ($items as $item) {
            if (!$item->order || !$item->order->agent) {
                continue;
            }
            $agentId = $item->order->agent->id;
            if (!isset($byAgent[$agentId])) {
                $byAgent[$agentId] = [
                    'sales' => 0,
                    'commission' => 0,
                ];
            }

            $byAgent[$agentId]['sales'] += $item->realizedSalesTotal();
            $byAgent[$agentId]['commission'] += $item->realizedCommissionTotal();
        }

        foreach ($byAgent as $agentId => $totals) {
            AgentCommissionSettlement::updateOrCreate(
                [
                    'agent_id' => $agentId,
                    'period_start' => $from,
                    'period_end' => $to,
                ],
                [
                    'sales_total' => $totals['sales'],
                    'commission_total' => $totals['commission'],
                ]
            );
        }

        return redirect()->route('admin.settlements.index')->with('status', 'Monthly settlements generated.');
    }

    public function updateStatus(Request $request, AgentCommissionSettlement $settlement)
    {
        $data = $request->validate([
            'status' => 'required|in:open,approved,paid',
        ]);

        $settlement->update(['status' => $data['status']]);

        return redirect()->route('admin.settlements.index')->with('status', 'Settlement updated.');
    }
}
