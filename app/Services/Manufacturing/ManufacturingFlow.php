<?php

namespace App\Services\Manufacturing;

use App\Models\BillOfMaterial;
use App\Models\Product;
use App\Models\ProductionRun;
use Illuminate\Database\Eloquent\Collection;

class ManufacturingFlow
{
    /**
     * Workflow step for a production run (1–5 active, 6 = all complete).
     *
     * 1 Recipe · 2 Batch · 3 Produce · 4 QC · 5 Stock
     */
    public static function stepForRun(ProductionRun $run): int
    {
        if ($run->stock_confirmed_at) {
            return 6;
        }

        if (in_array($run->qc_status, ['approved', 'partial', 'rejected'], true)) {
            return 5;
        }

        if ($run->exists && (int) $run->quantity > 0) {
            return 4;
        }

        if ($run->batch_id) {
            return 3;
        }

        return 2;
    }

    public static function inProgressForRun(ProductionRun $run): bool
    {
        if ($run->stock_confirmed_at) {
            return false;
        }

        return in_array(self::stepForRun($run), [4, 5], true);
    }

    /**
     * Dashboard-level step highlighting where the factory should focus.
     */
    public static function dashboardStep(int $productsWithoutActiveBom, int $pendingQcCount, int $awaitingStockCount): int
    {
        if ($productsWithoutActiveBom > 0) {
            return 1;
        }

        if ($pendingQcCount > 0) {
            return 4;
        }

        if ($awaitingStockCount > 0) {
            return 5;
        }

        return 3;
    }

    public static function dashboardInProgress(int $productsWithoutActiveBom, int $pendingQcCount, int $awaitingStockCount): bool
    {
        return $productsWithoutActiveBom > 0
            || $pendingQcCount > 0
            || $awaitingStockCount > 0;
    }

    /**
     * @return Collection<int, Product>
     */
    public static function finishedProductsWithoutActiveBom(int $limit = 8): Collection
    {
        return Product::query()
            ->where(function ($query) {
                $query->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            })
            ->where('is_active', true)
            ->whereDoesntHave('billsOfMaterial', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public static function countFinishedProductsWithoutActiveBom(): int
    {
        return Product::query()
            ->where(function ($query) {
                $query->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            })
            ->where('is_active', true)
            ->whereDoesntHave('billsOfMaterial', function ($query) {
                $query->where('is_active', true);
            })
            ->count();
    }

    public static function productHasActiveBom(int $productId): bool
    {
        return BillOfMaterial::activeForProduct($productId) !== null;
    }
}
