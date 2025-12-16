<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;

class WarehouseLocationController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with('locations')->orderBy('name')->get();
        return view('admin.warehouses.locations', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'code' => 'required|string',
            'description' => 'nullable|string',
        ]);

        WarehouseLocation::create($data);

        return redirect()->route('admin.warehouse-locations.index')->with('status', 'Location added.');
    }
}

