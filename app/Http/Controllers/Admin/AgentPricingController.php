<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentCommissionRule;
use App\Models\AgentPriceList;
use App\Models\Product;
use Illuminate\Http\Request;

class AgentPricingController extends Controller
{
    public function edit(Agent $agent)
    {
        $products = Product::orderBy('name')->get();
        $priceLists = $agent->priceLists()->get()->keyBy('product_id');
        $commissions = $agent->commissions()->orderBy('sku')->get();

        return view('admin.agents.pricing', compact('agent', 'products', 'priceLists', 'commissions'));
    }

    public function update(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'prices' => 'array',
            'prices.*' => 'nullable|numeric|min:0',
            'commissions' => 'array',
            'commissions.*.sku' => 'nullable|string',
            'commissions.*.type' => 'nullable|in:percentage,fixed',
            'commissions.*.value' => 'nullable|numeric|min:0',
            'commissions.*.order_type' => 'nullable|in:regular,bulk,sample,return',
            'commissions.*.frequency' => 'nullable|in:per_order,monthly',
        ]);

        $prices = $data['prices'] ?? [];
        $commissions = $data['commissions'] ?? [];

        // Refresh price list
        AgentPriceList::where('agent_id', $agent->id)->delete();
        foreach ($prices as $productId => $price) {
            if ($price === null || $price === '' || $price == 0) {
                continue;
            }

            AgentPriceList::create([
                'agent_id' => $agent->id,
                'product_id' => $productId,
                'price' => $price,
                'notes' => null,
            ]);
        }

        // Refresh commission rules
        AgentCommissionRule::where('agent_id', $agent->id)->delete();
        foreach ($commissions as $row) {
            $sku = $row['sku'] ?? null;
            $value = $row['value'] ?? null;
            $type = $row['type'] ?? null;

            if (!$type || $value === null || $value === '' || $value == 0) {
                continue;
            }

            AgentCommissionRule::create([
                'agent_id' => $agent->id,
                'sku' => $sku ?: null,
                'type' => $type,
                'value' => $value,
                'order_type' => $row['order_type'] ?? null,
                'frequency' => $row['frequency'] ?? 'per_order',
            ]);
        }

        return redirect()
            ->route('admin.agents.pricing.edit', $agent)
            ->with('status', 'Pricing and commission rules updated.');
    }
}

