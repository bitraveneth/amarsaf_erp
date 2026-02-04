<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\StockEntry;
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

    public function destroy(WarehouseLocation $location)
    {
        if (StockEntry::where('warehouse_location_id', $location->id)->exists()) {
            return redirect()->route('admin.warehouse-locations.index')
                ->with('status', 'Location is used in stock entries and cannot be deleted. Move or clear stock first.');
        }

        $location->delete();

        return redirect()->route('admin.warehouse-locations.index')
            ->with('status', 'Location deleted.');
    }
}
