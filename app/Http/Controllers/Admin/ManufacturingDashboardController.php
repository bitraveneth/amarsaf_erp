<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesDashboardPeriod;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ProductionRun;
use App\Services\Manufacturing\ManufacturingFlow;
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
        $pendingQcInPeriod = $runs->where('qc_status', '!=', 'approved')->count();

        $openQcCount = ProductionRun::query()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->whereNull('qc_status')
                    ->orWhere('qc_status', 'pending');
            })
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->count();

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
                $qty = (float) $group->sum('quantity');

                return [
                    'product_name' => $product?->name ?? 'Unknown product',
                    'qty' => $qty,
                    'label' => $product?->name ?? 'Unknown product',
                    'value' => $qty,
                    'meta' => $group->count() . ' runs',
                ];
            })
            ->sortByDesc('qty')
            ->take(5)
            ->values();

        $recentRuns = $runs
            ->sortByDesc(fn ($run) => $run->created_at?->timestamp ?? 0)
            ->take(5)
            ->values();

        $qcApprovalRate = $totalRuns > 0
            ? min(100, round(($approvedRuns->count() / $totalRuns) * 100, 1))
            : 0;

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

        $productsWithoutActiveBom = ManufacturingFlow::countFinishedProductsWithoutActiveBom();
        $productsNeedingRecipe = ManufacturingFlow::finishedProductsWithoutActiveBom();
        $awaitingStockCount = ProductionRun::query()
            ->whereIn('qc_status', ['approved', 'partial'])
            ->whereNull('stock_confirmed_at')
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->count();

        $flowStep = ManufacturingFlow::dashboardStep(
            $productsWithoutActiveBom,
            $openQcCount,
            $awaitingStockCount
        );
        $flowInProgress = ManufacturingFlow::dashboardInProgress(
            $productsWithoutActiveBom,
            $openQcCount,
            $awaitingStockCount
        );

        return view('admin.manufacturing.dashboard', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'reportPeriodParams' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'totalRuns' => $totalRuns,
            'totalQuantity' => $totalQuantity,
            'approvedQuantity' => $approvedQuantity,
            'approvedRunsCount' => $approvedRuns->count(),
            'openQcCount' => $openQcCount,
            'pendingQcInPeriod' => $pendingQcInPeriod,
            'batchCount' => $batchCount,
            'expiringSoon' => $expiringSoon,
            'expiringSoonCount' => $expiringSoon->count(),
            'topProducts' => $topProducts,
            'recentRuns' => $recentRuns,
            'qcApprovalRate' => $qcApprovalRate,
            'byLine' => $byLine,
            'byShift' => $byShift,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'productsWithoutActiveBom' => $productsWithoutActiveBom,
            'productsNeedingRecipe' => $productsNeedingRecipe,
            'awaitingStockCount' => $awaitingStockCount,
            'flowStep' => $flowStep,
            'flowInProgress' => $flowInProgress,
        ]);
    }
}
