<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesDashboardPeriod;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ProductionRun;
use Illuminate\Http\Request;

class ManufacturingDashboardController extends Controller
{
    use ResolvesDashboardPeriod;

    public function __invoke(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $runQuery = ProductionRun::with(['product', 'warehouse'])
            ->whereBetween('created_at', [$from, $to])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            });

        $runs = $runQuery->get();
        $totalRuns = $runs->count();
        $totalQuantity = $runs->sum('quantity');
        $approvedRuns = $runs->where('qc_status', 'approved');
        $approvedQuantity = $approvedRuns->sum('quantity');
        $pendingQcCount = $runs->where('qc_status', '!=', 'approved')->count();

        $batches = Batch::with('product')
            ->whereBetween('production_date', [$from->toDateString(), $to->toDateString()])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereHas('productionRuns', function ($runQuery) use ($warehouseIds) {
                    $runQuery->whereIn('warehouse_id', $warehouseIds);
                });
            })
            ->get();

        $batchCount = $batches->count();
        $today = now()->startOfDay();

        $expiringSoon = Batch::with('product')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$today, $today->copy()->addDays(60)])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereHas('stockEntries', function ($stockQuery) use ($warehouseIds) {
                    $stockQuery->whereIn('warehouse_id', $warehouseIds);
                });
            })
            ->get();

        $topProducts = $runs
            ->groupBy(fn ($run) => (string) ($run->product_id ?? 'unknown'))
            ->map(function ($group) {
                $product = $group->first()->product;

                return [
                    'label' => $product?->name ?? 'Unknown product',
                    'value' => $group->sum('quantity'),
                    'meta' => $group->count() . ' runs',
                ];
            })
            ->sortByDesc('value')
            ->take(5)
            ->values();

        $byLine = $runs
            ->groupBy('line')
            ->map(fn ($group) => $group->sum('quantity'))
            ->sortDesc();

        $byShift = $runs
            ->groupBy('shift')
            ->map(fn ($group) => $group->sum('quantity'))
            ->sortDesc();

        $chartLabels = $byLine->keys()->map(fn ($line) => 'Line ' . ($line ?: '—'))->values()->all();
        $chartValues = $byLine->values()->map(fn ($qty) => (float) $qty)->all();

        return view('admin.manufacturing.dashboard', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'totalRuns' => $totalRuns,
            'totalQuantity' => $totalQuantity,
            'approvedQuantity' => $approvedQuantity,
            'approvedRunsCount' => $approvedRuns->count(),
            'pendingQcCount' => $pendingQcCount,
            'batchCount' => $batchCount,
            'expiringSoon' => $expiringSoon,
            'topProducts' => $topProducts,
            'byLine' => $byLine,
            'byShift' => $byShift,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
        ]);
    }
}
