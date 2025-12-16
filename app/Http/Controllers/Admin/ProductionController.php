<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
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
            StockEntry::create([
                'warehouse_id' => $run->warehouse_id,
                'product_id' => $run->product_id,
                'batch_id' => $run->batch_id,
                'quantity' => $run->quantity,
                'status' => 'available',
            ]);
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
