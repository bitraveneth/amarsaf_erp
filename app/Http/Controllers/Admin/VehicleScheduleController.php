<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRoute;
use App\Models\Delivery;
use App\Models\Vehicle;
use App\Models\VehicleSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VehicleScheduleController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $schedules = VehicleSchedule::with('vehicle', 'route')
            ->whereDate('scheduled_date', $date)
            ->orderBy('vehicle_id')
            ->get();

        $deliveries = Delivery::with(['vehicle', 'route', 'order.agent'])
            ->whereDate('created_at', $date)
            ->orderBy('vehicle_id')
            ->orderBy('sequence')
            ->orderBy('id')
            ->get();

        $vehicles = Vehicle::orderBy('name')->get();
        $routes = DeliveryRoute::orderBy('name')->get();

        return view('admin.deliveries.schedule', compact('schedules', 'vehicles', 'routes', 'date', 'deliveries'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'route_id' => 'nullable|exists:delivery_routes,id',
            'scheduled_date' => 'required|date',
            'driver' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        VehicleSchedule::create($data);

        return redirect()->route('admin.vehicle-schedule.index', ['date' => $data['scheduled_date']])
            ->with('status', 'Vehicle schedule saved.');
    }
}
