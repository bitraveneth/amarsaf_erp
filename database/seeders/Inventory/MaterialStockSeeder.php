<?php

namespace Database\Seeders\Inventory;

use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

/**
 * Seed opening stock for all active raw materials in the Factory warehouse.
 */
class MaterialStockSeeder extends Seeder
{
    public function run(): void
    {
        $factory = Warehouse::where('name', 'Factory')->first();

        if (! $factory) {
            return;
        }

        $materials = Product::query()
            ->where('product_type', 'raw')
            ->where('is_active', true)
            ->orderBy('sku')
            ->get();

        foreach ($materials as $index => $product) {
            $qty = 5000 + (($index + 1) * 1370);

            if (str_contains(strtolower($product->uom ?? ''), 'l')) {
                $qty = 50000 + (($index + 1) * 8000);
            }

            StockEntry::updateOrCreate(
                [
                    'warehouse_id' => $factory->id,
                    'product_id' => $product->id,
                    'batch_id' => null,
                ],
                [
                    'quantity' => $qty,
                    'status' => 'available',
                ]
            );

            if ($product->reorder_level === null) {
                $product->update(['reorder_level' => max(1000, (int) round($qty * 0.35))]);
            }
        }
    }
}
