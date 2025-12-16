<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRoute;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DeliveryRouteController extends Controller
{
    public function index()
    {
        $routes = DeliveryRoute::with('vehicle')->orderBy('name')->get();
        $vehicles = Vehicle::orderBy('name')->get();

        return view('admin.deliveries.routes', compact('routes', 'vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'zone' => 'nullable|string',
            'day' => 'nullable|string',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver' => 'nullable|string',
        ]);

        DeliveryRoute::create($data);

        return redirect()->route('admin.delivery-routes.index')->with('status', 'Route saved.');
    }
}

