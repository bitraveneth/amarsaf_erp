<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentAdvance;
use App\Models\AgentCommissionRule;
use App\Models\AgentCommissionSettlement;
use App\Models\AgentPriceList;
use App\Models\KycDocumentType;
use App\Models\Order;
use App\Models\User;
use App\Support\AgentCommissionSync;
use App\Support\AgentCreditSummary;
use App\Support\AgentKycDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q');

        $agents = Agent::query()
            ->with('commissions')
            ->select('agents.*')
            ->selectSub(
                AgentAdvance::query()
                    ->selectRaw('COALESCE(SUM(amount - applied_amount), 0)')
                    ->whereColumn('agent_id', 'agents.id')
                    ->whereIn('status', ['open', 'partial']),
                'open_advance_balance'
            )
            ->when(filled($search), function ($query) use ($search) {
                $term = '%' . $search . '%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('area', 'like', $term)
                        ->orWhere('zone', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.agents.index', compact('agents', 'search'));
    }

    public function create()
    {
        $parents = Agent::orderBy('name')->get();
        $kycDocumentTypes = KycDocumentType::ordered()->get();

        return view('admin.agents.create', compact('parents', 'kycDocumentTypes'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedAgent($request);

        $kycDocuments = AgentKycDocuments::syncFromRequest($request, []);
        $data['kyc_documents'] = $kycDocuments !== [] ? $kycDocuments : null;

        $agent = Agent::create($data);
        $commissionRate = $request->input('commission_rate') ?? AgentCommissionSync::defaultRateForForm();
        AgentCommissionSync::sync($agent, $commissionRate);

        return redirect()->route('admin.agents.index')->with('status', 'Agent saved.');
    }

    public function edit(Agent $agent)
    {
        $agent->load('commissions');
        $parents = Agent::where('id', '!=', $agent->id)->orderBy('name')->get();
        $kycDocumentTypes = KycDocumentType::ordered()->get();
        $kycDocuments = AgentKycDocuments::normalizeList($agent->kyc_documents);
        $commissionRate = AgentCommissionSync::rateForForm($agent);

        return view('admin.agents.edit', compact('agent', 'parents', 'kycDocumentTypes', 'kycDocuments', 'commissionRate'));
    }

    public function show(Request $request, Agent $agent)
    {
        $agent->load(['parent', 'orders', 'commissions']);
        $kycDocuments = AgentKycDocuments::normalizeList($agent->kyc_documents);
        $tabQuery = $request->query('tab');
        $tab = in_array($tabQuery, ['commission', 'commercial'], true) ? 'commission' : 'profile';

        $commercial = AgentCommissionSync::summary($agent);
        $credit = AgentCreditSummary::forAgent($agent);

        return view('admin.agents.show', compact(
            'agent',
            'kycDocuments',
            'tab',
            'commercial',
            'credit',
        ));
    }

    public function update(Request $request, Agent $agent)
    {
        $data = $this->validatedAgent($request);

        $kycDocuments = AgentKycDocuments::syncFromRequest($request, $agent->kyc_documents);
        $data['kyc_documents'] = $kycDocuments !== [] ? $kycDocuments : null;

        $agent->update($data);
        AgentCommissionSync::sync($agent, $request->input('commission_rate'));

        return redirect()->route('admin.agents.index')->with('status', 'Agent updated.');
    }

    public function updateCredit(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $agent->update([
            'credit_limit' => $data['credit_limit'] ?? 0,
        ]);

        return redirect()
            ->route('admin.agents.show', ['agent' => $agent, 'tab' => 'commission'])
            ->with('status', 'Credit limit updated.');
    }

    public function destroy(Agent $agent)
    {
        $blockingHistory = $this->deletionBlockingHistory($agent->id);

        if ($blockingHistory !== []) {
            return redirect()
                ->route('admin.agents.index')
                ->withErrors([
                    'agent' => 'This agent has historical records (' . implode(', ', $blockingHistory) . ') and cannot be deleted. Disable or reassign it instead.',
                ]);
        }

        if (Order::where('agent_id', $agent->id)->exists()) {
            return redirect()->route('admin.agents.index')
                ->with('error', 'Agent has orders and cannot be deleted. Consider disabling or reassigning instead.');
        }

        User::where('agent_id', $agent->id)->update(['agent_id' => null]);

        AgentPriceList::where('agent_id', $agent->id)->delete();
        AgentCommissionRule::where('agent_id', $agent->id)->delete();
        AgentCommissionSettlement::where('agent_id', $agent->id)->delete();

        $agent->delete();

        return redirect()->route('admin.agents.index')->with('status', 'Agent deleted and any linked user accounts were unassigned.');
    }

    protected function validatedAgent(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'area' => 'nullable|string',
            'zone' => 'nullable|string',
            'location_code' => 'nullable|string|max:100',
            'special_code' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'withholding_rate' => 'nullable|numeric|min:0|max:100',
            'kyc_new' => 'nullable|array',
            'kyc_new.*.type_id' => 'nullable|string|max:100',
            'kyc_new.*.custom_type' => 'nullable|string|max:100',
            'kyc_new.*.file' => 'nullable|file|max:' . AgentKycDocuments::MAX_FILE_KB,
            'kyc_keep' => 'nullable|array',
            'kyc_keep.*' => 'string',
            'parent_id' => 'nullable|exists:agents,id',
            'bank_details' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        unset($data['kyc_new'], $data['kyc_keep'], $data['commission_rate']);

        return $data;
    }

    protected function deletionBlockingHistory(int $agentId): array
    {
        $checks = [
            'customer_gifts' => 'customer gifts',
            'visit_plans' => 'visit plans',
        ];

        $found = [];

        foreach ($checks as $table => $label) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (DB::table($table)->where('agent_id', $agentId)->exists()) {
                $found[] = $label;
            }
        }

        return $found;
    }
}
