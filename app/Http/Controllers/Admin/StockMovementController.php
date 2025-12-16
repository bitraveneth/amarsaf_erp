<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse', 'order'])->latest()->paginate(12);
        return view('admin.stock.movements', compact('movements'));
    }

    public function create()
    {
        $entries = StockEntry::with('product')->where('status', 'available')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('admin.stock.transfer', compact('entries', 'warehouses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entry_id' => 'required|exists:stock_entries,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $entry = StockEntry::findOrFail($data['entry_id']);
        if ($entry->quantity < $data['quantity']) {
            return back()->withErrors(['quantity' => 'Cannot transfer more than available quantity.']);
        }

        $entry->quantity -= $data['quantity'];
        $entry->save();

        $newEntry = StockEntry::create([
            'warehouse_id' => $data['destination_warehouse_id'],
            'product_id' => $entry->product_id,
            'batch_id' => $entry->batch_id,
            'quantity' => $data['quantity'],
            'status' => 'available',
        ]);

        StockMovement::create([
            'stock_entry_id' => $entry->id,
            'type' => 'transfer-out',
            'quantity' => $data['quantity'] * -1,
            'notes' => 'Transferred to warehouse ' . $data['destination_warehouse_id'],
        ]);

        StockMovement::create([
            'stock_entry_id' => $newEntry->id,
            'type' => 'transfer-in',
            'quantity' => $data['quantity'],
            'notes' => $data['notes'],
        ]);

        return redirect()->route('admin.stock.movements')->with('status', 'Stock transferred.');
    }
}
