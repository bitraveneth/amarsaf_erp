<?php

namespace App\Services\Manufacturing;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Support\Carbon;

class BatchFactory
{
    public static function createForProduction(Product $product, ?Carbon $productionDate = null): Batch
    {
        $productionDate = ($productionDate ?? now())->startOfDay();

        $expiryDate = null;
        if ($product->shelf_life_months) {
            $expiryDate = $productionDate->copy()->addMonths((int) $product->shelf_life_months);
        }

        return Batch::create([
            'product_id' => $product->id,
            'batch_code' => self::generateBatchCode($product, $productionDate),
            'production_date' => $productionDate->toDateString(),
            'expiry_date' => $expiryDate?->toDateString(),
            'qc_status' => 'pending',
        ]);
    }

    public static function generateBatchCode(Product $product, Carbon $productionDate): string
    {
        $sku = preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($product->sku ?: ('P' . $product->id))));
        $dateKey = $productionDate->format('Ymd');
        $prefix = "{$sku}-{$dateKey}-";

        $lastCode = Batch::query()
            ->where('product_id', $product->id)
            ->where('batch_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('batch_code');

        $sequence = 1;
        if ($lastCode && preg_match('/(\d+)$/', $lastCode, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    public static function previewBatchCode(Product $product, ?Carbon $productionDate = null): string
    {
        return self::generateBatchCode($product, ($productionDate ?? now())->startOfDay());
    }
}
