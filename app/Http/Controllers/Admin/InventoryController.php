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
        $today = Carbon::today();
        $expiringSoon = StockEntry::with('product', 'batch')
            ->whereNotNull('batch_id')
            ->whereHas('batch', function ($query) use ($today) {
                $query->where('expiry_date', '<=', $today->copy()->addDays(30));
            })
            ->orderBy('batch_id')
            ->get();

        $summary = StockEntry::selectRaw('warehouse_id, status, SUM(quantity) as total')
            ->groupBy('warehouse_id', 'status')
            ->with('warehouse')
            ->get();

        $recentMovements = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse', 'order.agent'])
            ->latest()
            ->limit(10)
            ->get();

        $recentRuns = ProductionRun::with(['product', 'batch', 'warehouse'])
            ->whereNotNull('stock_confirmed_at')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.inventory.index', compact('summary', 'expiringSoon', 'recentMovements', 'recentRuns'));
    }

    public function materials()
    {
        // Raw-material stock summary by product and warehouse
        $entries = StockEntry::with(['product', 'warehouse'])
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
        ]);
    }
}
