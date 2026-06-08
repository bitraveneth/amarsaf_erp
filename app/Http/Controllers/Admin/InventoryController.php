<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductionRun;
use App\Models\StockEntry;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InventoryController extends Controller
{
    public function index()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $today = Carbon::today();
        $expiringSoon = StockEntry::with('product', 'batch')
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->where('status', 'available')
            ->where('quantity', '>', 0)
            ->whereNotNull('batch_id')
            ->whereHas('batch', function ($query) use ($today) {
                $query->where('expiry_date', '<=', $today->copy()->addDays(30));
            })
            ->orderBy('batch_id')
            ->get();

        $summary = StockEntry::selectRaw('warehouse_id, status, SUM(quantity) as total')
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->groupBy('warehouse_id', 'status')
            ->with('warehouse')
            ->get();

        $recentMovements = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse', 'order.agent'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereHas('stockEntry', function ($stockQuery) use ($warehouseIds) {
                    $stockQuery->whereIn('warehouse_id', $warehouseIds);
                });
            })
            ->latest()
            ->limit(10)
            ->get();

        $recentRuns = ProductionRun::with(['product', 'batch', 'warehouse'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->whereNotNull('stock_confirmed_at')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.inventory.index', compact('summary', 'expiringSoon', 'recentMovements', 'recentRuns'));
    }

    public function materials(Request $request)
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $warehouseFilter = $request->integer('warehouse_id') ?: null;

        if ($warehouseFilter !== null && $warehouseIds !== null && ! in_array($warehouseFilter, $warehouseIds, true)) {
            abort(403);
        }

        $filteredWarehouse = null;
        if ($warehouseFilter !== null) {
            $filteredWarehouse = \App\Models\Warehouse::query()->find($warehouseFilter);
        }

        // Raw-material stock summary by product and warehouse
        $entries = StockEntry::with(['product', 'warehouse'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->when($warehouseFilter !== null, fn ($query) => $query->where('warehouse_id', $warehouseFilter))
            ->where('status', 'available')
            ->whereHas('product', function ($query) {
                $query->where('product_type', 'raw');
            })
            ->get();

        $grouped = $entries->groupBy(function ($entry) {
            return $entry->product_id.'|'.$entry->warehouse_id;
        });

        $rows = $grouped->map(function ($group) {
            $first = $group->first();

            return (object) [
                'product'   => $first->product,
                'warehouse' => $first->warehouse,
                'quantity'  => $group->sum('quantity'),
            ];
        })->sortBy(function ($row) {
            return ($row->product->name ?? '').'|'.($row->warehouse->name ?? '');
        });

        return view('admin.inventory.materials', [
            'rows' => $rows,
            'filteredWarehouse' => $filteredWarehouse,
        ]);
    }

    public function lowStock()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $availableByProduct = StockEntry::query()
            ->selectRaw('product_id, SUM(quantity) as total')
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds))
            ->where('status', 'available')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $rows = \App\Models\Product::stockTracked()
            ->where('is_active', true)
            ->whereNotNull('reorder_level')
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($availableByProduct) {
                $available = (float) ($availableByProduct[$product->id] ?? 0);

                return [
                    'product' => $product,
                    'available' => $available,
                    'reorder_level' => (float) $product->reorder_level,
                    'shortage' => max(0.0, (float) $product->reorder_level - $available),
                ];
            })
            ->filter(fn (array $row) => $row['shortage'] > 0)
            ->sortByDesc('shortage')
            ->values();

        return view('admin.inventory.low_stock', compact('rows'));
    }
}
