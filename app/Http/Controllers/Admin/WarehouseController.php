<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockEntry;
use App\Models\ProductionRun;
use App\Models\WarehouseLocation;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::withCount('entries')->paginate(8);
        $entries = StockEntry::with(['product', 'batch', 'warehouse'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        return view('admin.warehouses.index', compact('warehouses', 'entries'));
    }

    public function create()
    {
        return view('admin.warehouses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        Warehouse::create($data);
        return back()->with('status', 'Warehouse added.');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('admin.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        $warehouse->update($data);

        return redirect()->route('admin.warehouses.index')->with('status', 'Warehouse updated.');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->entries()->exists()) {
            return redirect()->route('admin.warehouses.index')
                ->with('status', 'Warehouse has stock entries and cannot be deleted.');
        }

        if (ProductionRun::where('warehouse_id', $warehouse->id)->exists()) {
            return redirect()->route('admin.warehouses.index')
                ->with('status', 'Warehouse is linked to production runs and cannot be deleted.');
        }

         if (WarehouseLocation::where('warehouse_id', $warehouse->id)->exists()) {
            return redirect()->route('admin.warehouses.index')
                ->with('status', 'Warehouse has locations configured and cannot be deleted. Delete those locations first.');
        }

        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')->with('status', 'Warehouse deleted.');
    }

    public function clearStock(Warehouse $warehouse)
    {
        foreach ($warehouse->entries as $entry) {
            $entry->movements()->delete();
            $entry->delete();
        }

        return redirect()->route('admin.warehouses.index')->with('status', 'All stock cleared from warehouse.');
    }
}
