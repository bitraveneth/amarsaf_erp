<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BillOfMaterial;
use App\Models\ProductionRun;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function index()
    {
        $runs = ProductionRun::with('product', 'batch')->latest()->paginate(10);

        $today = now()->toDateString();
        $todayRuns = ProductionRun::whereDate('created_at', $today)->get();

        $byLineShift = $todayRuns
            ->groupBy(fn ($run) => ($run->line ?: 'Unassigned') . '|' . ($run->shift ?: 'All'))
            ->map(function ($group) {
                return $group->sum('quantity');
            });

        $lineCapacities = [
            'Line 1|Morning' => 50000,
            'Line 1|Evening' => 50000,
            'Line 2|Morning' => 40000,
            'Line 2|Evening' => 40000,
        ];

        return view('admin.production.index', compact('runs', 'byLineShift', 'lineCapacities', 'today'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $batches = Batch::orderBy('production_date', 'desc')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('admin.production.create', compact('products', 'batches', 'warehouses'));
    }

    public function edit(ProductionRun $production)
    {
        $production->load('product', 'batch', 'warehouse');
        return view('admin.production.edit', ['run' => $production]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'required|exists:batches,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'line' => 'nullable|string',
            'shift' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'qc_status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $run = ProductionRun::create($data);

        if ($run->qc_status === 'approved' && $run->quantity > 0 && $run->warehouse_id) {
            // Finished goods stock
            StockEntry::create([
                'warehouse_id' => $run->warehouse_id,
                'product_id' => $run->product_id,
                'batch_id' => $run->batch_id,
                'quantity' => $run->quantity,
                'status' => 'available',
            ]);

            // Consume raw materials based on active BOM, if any
            $bom = BillOfMaterial::where('product_id', $run->product_id)
                ->where('is_active', true)
                ->orderByDesc('id')
                ->with('items')
                ->first();

            if ($bom && $bom->items->isNotEmpty()) {
                foreach ($bom->items as $item) {
                    $totalRequired = $item->quantity * $run->quantity;
                    if ($totalRequired <= 0) {
                        continue;
                    }

                    // Simple FEFO/FIFO: use oldest stock entries first
                    $entries = StockEntry::where('warehouse_id', $run->warehouse_id)
                        ->where('product_id', $item->component_product_id)
                        ->where('status', 'available')
                        ->orderBy('created_at')
                        ->get();

                    $remaining = $totalRequired;

                    foreach ($entries as $entry) {
                        if ($remaining <= 0) {
                            break;
                        }

                        $consume = min($remaining, $entry->quantity);
                        if ($consume <= 0) {
                            continue;
                        }

                        $entry->quantity -= $consume;
                        if ($entry->quantity <= 0) {
                            $entry->status = 'sold'; // treated as consumed in production
                        }
                        $entry->save();

                        $remaining -= $consume;
                    }

                    // If remaining > 0, it means negative stock; we currently keep it simple and do not create it.
                }
            }
        }

        return redirect()->route('admin.production.index')->with('status', 'Production run recorded.');
    }

    public function update(Request $request, ProductionRun $production)
    {
        $data = $request->validate([
            'line' => 'nullable|string',
            'shift' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $production->update($data);

        return redirect()->route('admin.production.index')->with('status', 'Production run updated.');
    }

    public function destroy(ProductionRun $production)
    {
        $production->delete();

        return redirect()->route('admin.production.index')->with('status', 'Production run deleted.');
    }
}
