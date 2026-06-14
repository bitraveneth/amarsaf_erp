<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRoute;
use App\Models\FleetExpense;
use App\Models\Vehicle;
use App\Services\Accounting\FleetExpensePostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FleetExpenseController extends Controller
{
    public function __construct(protected FleetExpensePostingService $posting)
    {
    }

    public function index(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $query = FleetExpense::with(['vehicle', 'deliveryRoute'])
            ->whereBetween('expense_date', [$from, $to]);

        if ($vehicleId = $request->query('vehicle_id')) {
            $query->where('vehicle_id', $vehicleId);
        }

        if ($type = $request->query('expense_type')) {
            $query->where('expense_type', $type);
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(20)->withQueryString();

        $statsQuery = FleetExpense::query()->whereBetween('expense_date', [$from, $to]);

        if ($vehicleId = $request->query('vehicle_id')) {
            $statsQuery->where('vehicle_id', $vehicleId);
        }

        if ($type = $request->query('expense_type')) {
            $statsQuery->where('expense_type', $type);
        }

        $total = (clone $statsQuery)->sum('amount');
        $entryCount = (clone $statsQuery)->count();
        $vehicleCount = (clone $statsQuery)->distinct('vehicle_id')->count('vehicle_id');
        $totalsByType = (clone $statsQuery)
            ->selectRaw('expense_type, SUM(amount) as total')
            ->groupBy('expense_type')
            ->pluck('total', 'expense_type');

        $vehicles = Vehicle::orderBy('name')->get();
        $selectedVehicle = $vehicleId ? $vehicles->firstWhere('id', (int) $vehicleId) : null;

        return view('admin.logistics.fleet_expenses.index', compact(
            'expenses',
            'from',
            'to',
            'total',
            'entryCount',
            'vehicleCount',
            'totalsByType',
            'vehicles',
            'selectedVehicle',
        ));
    }

    public function create(Request $request)
    {
        return view('admin.logistics.fleet_expenses.create', [
            'expense' => new FleetExpense([
                'expense_date' => $request->query('trip_date', now()->toDateString()),
                'expense_type' => FleetExpense::TYPE_FUEL,
                'payment_type' => 'bank',
                'payment_account_key' => 'bank_default',
                'status' => FleetExpense::STATUS_RECORDED,
                'vehicle_id' => $request->query('vehicle_id'),
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $expense = FleetExpense::create($data);
            $this->posting->sync($expense);
        });

        return redirect()
            ->route('admin.fleet-expenses.index')
            ->with('status', 'Fleet expense recorded.');
    }

    public function edit(FleetExpense $fleetExpense)
    {
        $fleetExpense->load(['vehicle', 'deliveryRoute']);

        return view('admin.logistics.fleet_expenses.edit', [
            'expense' => $fleetExpense,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, FleetExpense $fleetExpense)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($fleetExpense, $data) {
            $fleetExpense->update($data);
            $this->posting->sync($fleetExpense->fresh());
        });

        return redirect()
            ->route('admin.fleet-expenses.index')
            ->with('status', 'Fleet expense updated.');
    }

    public function destroy(FleetExpense $fleetExpense)
    {
        DB::transaction(function () use ($fleetExpense) {
            app(\App\Services\Accounting\AccountingService::class)
                ->deleteByJournalTypeAndSource('fleet_expense', FleetExpense::class, $fleetExpense->id);
            $fleetExpense->delete();
        });

        return redirect()
            ->route('admin.fleet-expenses.index')
            ->with('status', 'Fleet expense deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'expense_type' => 'required|in:' . implode(',', array_keys(FleetExpense::types())),
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'status' => 'required|in:recorded,reviewed',
            'payment_type' => 'required|in:bank,cash,payable',
            'payment_account_key' => 'required|string|max:100',
            'fuel_litres' => 'nullable|numeric|min:0',
            'fuel_rate' => 'nullable|numeric|min:0',
            'odometer_km' => 'nullable|integer|min:0',
            'delivery_route_id' => 'nullable|exists:delivery_routes,id',
            'trip_date' => 'nullable|date',
        ]);

        if ($data['expense_type'] === FleetExpense::TYPE_FUEL) {
            $litres = $data['fuel_litres'] ?? null;
            $rate = $data['fuel_rate'] ?? null;
            if ($litres && $rate && (float) $data['amount'] <= 0) {
                $data['amount'] = round((float) $litres * (float) $rate, 2);
            }
        }

        $data['analytic_label'] = 'vehicle:' . $data['vehicle_id'];

        return $data;
    }

    protected function formOptions(): array
    {
        return [
            'vehicles' => Vehicle::orderBy('name')->get(),
            'routes' => DeliveryRoute::orderBy('name')->get(),
            'types' => FleetExpense::types(),
            'paymentAccountKeys' => [
                'bank_default' => 'Default bank',
                'bank_brac' => 'BRAC Bank',
                'bank_scb' => 'SCB Bank',
                'cash_in_hand' => 'Cash in hand',
                'petty_cash' => 'Petty cash',
            ],
        ];
    }
}
