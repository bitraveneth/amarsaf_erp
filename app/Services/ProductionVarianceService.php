<?php

namespace App\Services;

use App\Models\BillOfMaterial;
use App\Models\ProductionRun;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ProductionVarianceService
{
    public function report(Carbon $from, Carbon $to): Collection
    {
        $runs = ProductionRun::with('product')
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('stock_confirmed_at')
            ->get();

        $productIds = $runs->pluck('product_id')->unique()->filter()->all();
        $boms = collect();

        if (Schema::hasTable('bill_of_materials') && ! empty($productIds)) {
            $boms = BillOfMaterial::whereIn('product_id', $productIds)
                ->where('is_active', true)
                ->with(['items.component'])
                ->orderByDesc('id')
                ->get()
                ->keyBy('product_id');
        }

        return $runs->map(function (ProductionRun $run) use ($boms) {
            $actualUnit = (float) $run->quantity > 0
                ? (float) $run->material_total_cost / (float) $run->quantity
                : 0.0;

            $standardUnit = $this->standardUnitCost($boms->get($run->product_id));
            $varianceUnit = round($actualUnit - $standardUnit, 4);
            $varianceTotal = round($varianceUnit * (float) $run->quantity, 2);

            return [
                'run' => $run,
                'product' => $run->product,
                'quantity' => (float) $run->quantity,
                'standard_unit_cost' => $standardUnit,
                'actual_unit_cost' => round($actualUnit, 4),
                'variance_unit' => $varianceUnit,
                'variance_total' => $varianceTotal,
            ];
        })->sortByDesc(fn (array $row) => abs($row['variance_total']))->values();
    }

    protected function standardUnitCost(?BillOfMaterial $bom): float
    {
        if (! $bom) {
            return 0.0;
        }

        if ($bom->material_unit_cost !== null && (float) $bom->material_unit_cost > 0) {
            return (float) $bom->material_unit_cost;
        }

        $total = 0.0;
        foreach ($bom->items as $item) {
            $unit = $item->unit_cost !== null
                ? (float) $item->unit_cost
                : (float) ($item->component?->standard_cost ?? 0);

            $total += $unit * (float) $item->quantity;
        }

        return round($total, 4);
    }
}
