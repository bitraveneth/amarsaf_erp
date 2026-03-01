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
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $movements = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse', 'order'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereHas('stockEntry', function ($stockQuery) use ($warehouseIds) {
                    $stockQuery->whereIn('warehouse_id', $warehouseIds);
                });
            })
            ->latest()
            ->paginate(12);
        return view('admin.stock.movements', compact('movements'));
    }

    public function create()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $entries = StockEntry::with(['product', 'warehouse'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->where('status', 'available')
            ->orderByDesc('updated_at')
            ->get();

        // Split into two trays: material stock (raw / service) and finished products.
        $materialEntries = $entries->filter(function (StockEntry $entry) {
            $type = $entry->product->product_type ?? null;
            return in_array($type, ['raw', 'service']);
        });

        $finishedEntries = $entries->reject(function (StockEntry $entry) {
            $type = $entry->product->product_type ?? null;
            return in_array($type, ['raw', 'service']);
        });

        $warehouses = Warehouse::query()
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('id', $warehouseIds);
            })
            ->orderBy('name')
            ->get();
        return view('admin.stock.transfer', compact('materialEntries', 'finishedEntries', 'warehouses'));
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
        $this->ensureStockEntryAccess($entry);
        $this->ensureWarehouseAccess((int) $data['destination_warehouse_id']);

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

    public function writeOffForm(Request $request)
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $selectedEntryId = $request->input('entry_id');
        $filterWarehouse = $request->input('warehouse_id');
        $filterProduct   = $request->input('product_id');
        $filterBatch     = $request->input('batch_id');
        $defaultReason   = $request->input('reason');

        $entries = StockEntry::with(['product', 'warehouse'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->where('status', 'available')
            ->when($filterWarehouse, function ($q) use ($filterWarehouse) {
                $q->where('warehouse_id', $filterWarehouse);
            })
            ->when($filterProduct, function ($q) use ($filterProduct) {
                $q->where('product_id', $filterProduct);
            })
            ->when($filterBatch, function ($q) use ($filterBatch) {
                $q->where('batch_id', $filterBatch);
            })
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.stock.writeoff', compact('entries', 'selectedEntryId', 'defaultReason'));
    }

    public function writeOffStore(Request $request)
    {
        $data = $request->validate([
            'entry_id' => 'required|exists:stock_entries,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|in:expired,wasted,supplier-return,production-loss,other',
            'notes' => 'nullable|string',
        ]);

        $entry = StockEntry::findOrFail($data['entry_id']);
        $this->ensureStockEntryAccess($entry);

        if ($entry->quantity < $data['quantity']) {
            return back()->withErrors(['quantity' => 'Cannot write off more than available quantity.']);
        }

        $entry->quantity -= $data['quantity'];
        $entry->save();

        StockMovement::create([
            'stock_entry_id' => $entry->id,
            'type' => $data['reason'],
            'quantity' => $data['quantity'] * -1,
            'notes' => $data['notes'],
        ]);

        return redirect()->route('admin.stock.movements')->with('status', 'Stock written off.');
    }

    public function writeOffEntry(StockEntry $entry)
    {
        $this->ensureStockEntryAccess($entry);

        if ($entry->quantity <= 0) {
            return back()->with('status', 'Entry already has zero quantity.');
        }

        $quantity = $entry->quantity;

        $entry->quantity = 0;
        $entry->save();

        StockMovement::create([
            'stock_entry_id' => $entry->id,
            'type' => 'expired',
            'quantity' => $quantity * -1,
            'notes' => 'Written off as expired from inventory view.',
        ]);

        return back()->with('status', 'Batch written off as expired.');
    }

    protected function ensureWarehouseAccess(int $warehouseId): void
    {
        if (! auth()->user()?->canAccessWarehouse($warehouseId)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }

    protected function ensureStockEntryAccess(StockEntry $entry): void
    {
        $this->ensureWarehouseAccess((int) $entry->warehouse_id);
    }
}
