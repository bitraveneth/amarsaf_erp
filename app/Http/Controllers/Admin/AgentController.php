<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentCommissionRule;
use App\Models\AgentCommissionSettlement;
use App\Models\AgentPriceList;
use App\Models\Order;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with('parent')->paginate(12);
        return view('admin.agents.index', compact('agents'));
    }

    public function create()
    {
        $parents = Agent::orderBy('name')->get();
        return view('admin.agents.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'area' => 'nullable|string',
            'zone' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'kyc_documents' => 'nullable|array',
            'parent_id' => 'nullable|exists:agents,id',
            'bank_details' => 'nullable|string',
        ]);

        if (!empty($data['kyc_documents'])) {
            $data['kyc_documents'] = array_values($data['kyc_documents']);
        }

        Agent::create($data);
        return redirect()->route('admin.agents.index')->with('status', 'Agent saved.');
    }

    public function edit(Agent $agent)
    {
        $parents = Agent::where('id', '!=', $agent->id)->orderBy('name')->get();
        return view('admin.agents.edit', compact('agent', 'parents'));
    }

    public function update(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'area' => 'nullable|string',
            'zone' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'kyc_documents' => 'nullable|array',
            'parent_id' => 'nullable|exists:agents,id',
            'bank_details' => 'nullable|string',
        ]);

        if (!empty($data['kyc_documents'])) {
            $data['kyc_documents'] = array_values($data['kyc_documents']);
        }

        $agent->update($data);

        return redirect()->route('admin.agents.index')->with('status', 'Agent updated.');
    }

    public function destroy(Agent $agent)
    {
        if (Order::where('agent_id', $agent->id)->exists()) {
            return redirect()->route('admin.agents.index')
                ->with('status', 'Agent has orders and cannot be deleted. Consider disabling or reassigning instead.');
        }

        AgentPriceList::where('agent_id', $agent->id)->delete();
        AgentCommissionRule::where('agent_id', $agent->id)->delete();
        AgentCommissionSettlement::where('agent_id', $agent->id)->delete();

        $agent->delete();

        return redirect()->route('admin.agents.index')->with('status', 'Agent deleted.');
    }
}
