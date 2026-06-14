<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FleetExpense;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::orderBy('name')->paginate(15);

        $stats = [
            'total' => Vehicle::count(),
            'capacity' => (int) Vehicle::sum('capacity_crates'),
            'with_driver' => Vehicle::whereNotNull('driver')->where('driver', '!=', '')->count(),
            'with_plate' => Vehicle::whereNotNull('license_plate')->where('license_plate', '!=', '')->count(),
            'active' => Vehicle::where('is_active', true)->count(),
        ];

        return view('admin.vehicles.index', compact('vehicles', 'stats'));
    }

    public function create()
    {
        return view('admin.vehicles.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'type' => $this->normalizedNullableString($request->input('type')),
            'license_plate' => $this->normalizedNullableString($request->input('license_plate'), true),
            'driver' => $this->normalizedNullableString($request->input('driver')),
        ]);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicles', 'name'),
            ],
            'type' => 'nullable|string|max:100',
            'ownership' => 'nullable|in:own,leased,hired_daily',
            'fuel_type' => 'nullable|in:diesel,octane,cng,electric',
            'odometer_km' => 'nullable|integer|min:0',
            'license_plate' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('vehicles', 'license_plate'),
            ],
            'driver' => 'nullable|string|max:255',
            'capacity_crates' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]) + [
            'is_active' => $request->boolean('is_active', true),
            'ownership' => $request->input('ownership', 'own'),
        ];

        Vehicle::create($data);

        return redirect()->route('admin.vehicles.index')->with('status', 'Vehicle added.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->loadCount('fleetExpenses');

        $recentExpenses = FleetExpense::with('deliveryRoute')
            ->where('vehicle_id', $vehicle->id)
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        $monthStart = now()->startOfMonth();
        $monthTotals = FleetExpense::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('expense_date', '>=', $monthStart)
            ->selectRaw('expense_type, SUM(amount) as total')
            ->groupBy('expense_type')
            ->pluck('total', 'expense_type');

        return view('admin.vehicles.show', compact('vehicle', 'recentExpenses', 'monthTotals'));
    }

    protected function normalizedNullableString($value, bool $upper = false): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return $upper ? mb_strtoupper($value) : $value;
    }
}
