<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CommissionReportController extends Controller
{
    public function rules()
    {
        $agents = Agent::with('commissions')->orderBy('name')->get();

        return view('admin.agents.commission_rules', compact('agents'));
    }

    public function index(Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $items = OrderItem::with('order.agent')
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
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
                    'agent' => $item->order->agent,
                    'sales' => 0,
                    'commission' => 0,
                ];
            }

            $lineTotal = $item->quantity * $item->unit_price;
            $byAgent[$agentId]['sales'] += $lineTotal;
            $byAgent[$agentId]['commission'] += $item->commission_amount ?? 0;
        }

        return view('admin.agents.commissions', [
            'rows' => $byAgent,
            'month' => $month,
        ]);
    }
}
