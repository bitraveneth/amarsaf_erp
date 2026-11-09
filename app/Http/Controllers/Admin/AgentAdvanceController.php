<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentAdvance;
use App\Models\JournalEntry;
use App\Services\Accounting\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AgentAdvanceController extends Controller
{
    public function __construct(protected AccountingService $accounting)
    {
    }

    public function index(Request $request)
    {
        $advances = AgentAdvance::with('agent')
            ->when($request->query('agent_id'), function ($query, $agentId) {
                $query->where('agent_id', $agentId);
            })
            ->orderByDesc('advanced_at')
            ->paginate(15)
            ->withQueryString();

        $agents = Agent::orderBy('name')->get(['id', 'name', 'phone', 'area', 'zone']);

        $summaryQuery = AgentAdvance::query()
            ->when($request->query('agent_id'), function ($query, $agentId) {
                $query->where('agent_id', $agentId);
            });

        $summary = [
            'count' => (clone $summaryQuery)->count(),
            'amount' => (float) (clone $summaryQuery)->sum('amount'),
            'applied' => (float) (clone $summaryQuery)->sum('applied_amount'),
        ];
        $summary['available'] = max(0, $summary['amount'] - $summary['applied']);

        return view('admin.agent-advances.index', compact('advances', 'agents', 'summary'));
    }

    public function show(AgentAdvance $advance)
    {
        $advance->load(['agent', 'applications.invoice']);

        $journal = JournalEntry::with('lines.account')
            ->where('source_type', AgentAdvance::class)
            ->where('source_id', $advance->id)
            ->latest('id')
            ->first();

        $agentAdvances = AgentAdvance::with(['applications.invoice'])
            ->when($advance->agent_id, fn ($query) => $query->where('agent_id', $advance->agent_id), fn ($query) => $query->whereKey($advance->id))
            ->orderBy('advanced_at')
            ->orderBy('id')
            ->get();

        $history = $agentAdvances->flatMap(function (AgentAdvance $item) use ($advance) {
            $rows = [[
                'at' => $item->advanced_at,
                'advance' => $item,
                'current' => $item->id === $advance->id,
                'event' => 'Advance received',
                'detail' => collect([
                    $item->payment_method ? ucfirst(str_replace('_', ' ', $item->payment_method)) : null,
                    $item->reference,
                ])->filter()->implode(' · '),
                'invoice' => null,
                'amount' => (float) $item->amount,
                'direction' => 'in',
            ]];

            foreach ($item->applications as $application) {
                $rows[] = [
                    'at' => $application->applied_at,
                    'advance' => $item,
                    'current' => $item->id === $advance->id,
                    'event' => 'Applied to invoice',
                    'detail' => $application->invoice?->number,
                    'invoice' => $application->invoice,
                    'amount' => (float) $application->amount,
                    'direction' => 'out',
                ];
            }

            return $rows;
        })->sortBy(function (array $row) {
            $date = $row['at'] ? $row['at']->format('Y-m-d') : '0000-00-00';

            return $date.'-'.str_pad((string) $row['advance']->id, 6, '0', STR_PAD_LEFT).'-'.($row['direction'] === 'in' ? '0' : '1');
        })->values();

        return view('admin.agent-advances.show', compact('advance', 'journal', 'agentAdvances', 'history'));
    }

    public function create(Request $request)
    {
        $agents = Agent::orderBy('name')->get();
        $selectedAgent = $request->query('agent_id');

        return view('admin.agent-advances.create', compact('agents', 'selectedAgent'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'amount' => 'required|numeric|min:0.01',
            'advanced_at' => 'required|date',
            'payment_method' => 'nullable|in:cash,bkash,bank_transfer,cheque',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'redirect' => 'nullable|string|in:commission,commercial',
        ]);

        DB::transaction(function () use ($data) {
            $advance = AgentAdvance::create([
                'agent_id' => $data['agent_id'],
                'amount' => $data['amount'],
                'applied_amount' => 0,
                'advanced_at' => $data['advanced_at'],
                'payment_method' => $data['payment_method'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'open',
            ]);

            $agent = Agent::findOrFail($data['agent_id']);
            $description = $this->advanceLedgerDescription($advance, $agent);
            $entryDate = Carbon::parse($advance->advanced_at);

            $this->accounting->post(
                'agent_advance',
                $entryDate,
                [
                    ['account_key' => 'bank_default', 'debit' => $advance->amount, 'credit' => 0],
                    ['account' => 'Agent Advances', 'debit' => 0, 'credit' => $advance->amount],
                ],
                [
                    'description' => $description,
                    'source_type' => AgentAdvance::class,
                    'source_id' => $advance->id,
                    'created_at' => $entryDate->copy()->startOfDay(),
                ]
            );
        });

        if (in_array($request->input('redirect'), ['commission', 'commercial'], true)) {
            return redirect()
                ->route('admin.agents.show', ['agent' => $data['agent_id'], 'tab' => 'commission'])
                ->with('status', 'Advance recorded.');
        }

        return redirect()->route('admin.agent-advances.index')->with('status', 'Agent advance recorded.');
    }

    protected function advanceLedgerDescription(AgentAdvance $advance, Agent $agent): string
    {
        $date = Carbon::parse($advance->advanced_at)->toDateString();

        return 'Agent advance #' . $advance->id . ' from ' . $agent->name . ' on ' . $date;
    }
}
