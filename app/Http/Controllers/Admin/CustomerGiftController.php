<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\CustomerGift;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CustomerGiftController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $query = CustomerGift::with(['agent', 'employee'])
            ->whereBetween('date', [$from, $to]);

        if ($agentId = $request->query('agent_id')) {
            $query->where('agent_id', $agentId);
        }

        $gifts = $query->orderByDesc('date')->paginate(20)->withQueryString();

        $total = (clone $query)->sum('amount');

        $agents = Agent::orderBy('name')->get();

        return view('admin.crm.gifts.index', compact('gifts', 'from', 'to', 'total', 'agents'));
    }

    public function create()
    {
        $agents = Agent::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();

        return view('admin.crm.gifts.create', [
            'gift' => new CustomerGift([
                'date' => Carbon::today(),
                'status' => 'given',
            ]),
            'agents' => $agents,
            'employees' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        CustomerGift::create($data);

        return redirect()->route('admin.gifts.index')->with('status', 'Customer gift recorded.');
    }

    public function edit(CustomerGift $gift)
    {
        $agents = Agent::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();

        return view('admin.crm.gifts.edit', compact('gift', 'agents', 'employees'));
    }

    public function update(Request $request, CustomerGift $gift)
    {
        $data = $this->validated($request);

        $gift->update($data);

        return redirect()->route('admin.gifts.index')->with('status', 'Customer gift updated.');
    }

    public function destroy(CustomerGift $gift)
    {
        $gift->delete();

        return redirect()->route('admin.gifts.index')->with('status', 'Customer gift deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'employee_id' => 'nullable|exists:employees,id',
            'date' => 'required|date',
            'occasion' => 'nullable|string|max:100',
            'gift_type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0',
            'campaign_code' => 'nullable|string|max:100',
            'status' => 'required|string|max:50',
        ]);
    }
}
