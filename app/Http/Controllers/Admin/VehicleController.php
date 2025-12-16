<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::orderBy('name')->paginate(15);
        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('admin.vehicles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'type' => 'nullable|string',
            'license_plate' => 'nullable|string',
            'driver' => 'nullable|string',
            'capacity_crates' => 'nullable|integer|min:0',
        ]);

        Vehicle::create($data);

        return redirect()->route('admin.vehicles.index')->with('status', 'Vehicle added.');
    }
}

