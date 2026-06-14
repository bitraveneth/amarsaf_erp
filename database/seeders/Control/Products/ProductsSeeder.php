<?php

namespace Database\Seeders\Control\Products;

use App\Models\PackagingType;
use App\Models\Product;
use App\Models\TaxClass;
use App\Support\WaterProductLineCatalog;
use Illuminate\Database\Seeder;

/**
 * Seed finished products for the SAF mineral water line (500ml, 1L, 2L, 20L jar).
 */
class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $vat15 = TaxClass::firstOrCreate(
            ['name' => 'Standard VAT 15%'],
            ['rate' => 15]
        );

        // Legacy SKU without pack suffix → standard SAF-500ML-BTL
        if (Product::where('sku', 'SAF-500ML')->exists()
            && ! Product::where('sku', 'SAF-500ML-BTL')->exists()) {
            Product::where('sku', 'SAF-500ML')->update(['sku' => 'SAF-500ML-BTL']);
        }

        $definitions = array_merge(
            WaterProductLineCatalog::primaryFinishedProducts(),
            WaterProductLineCatalog::optionalSingleBottleProducts()
        );

        $validSkus = collect($definitions)->pluck('sku')->all();

        foreach ($definitions as $row) {
            $packaging = PackagingType::where('name', $row['packaging_name'])->first();

            Product::updateOrCreate(
                ['sku' => $row['sku']],
                [
                    'product_type' => 'finished',
                    'name' => $row['name'],
                    'brand' => $row['brand'] ?? 'SAF',
                    'description' => $row['description'],
                    'size' => $row['size'],
                    'uom' => $row['uom'],
                    'volume_ml' => $row['volume_ml'],
                    'weight_g' => $row['weight_g'] ?? null,
                    'shelf_life_months' => $row['shelf_life_months'] ?? null,
                    'packaging_type_id' => $packaging?->id,
                    'tax_class_id' => $vat15->id,
                    'mineral_source' => 'SAF Plant',
                    'ph' => 7.2,
                    'tds' => 150,
                    'base_price' => $row['base_price'],
                    'mrp' => $row['mrp'] ?? null,
                    'standard_cost' => $row['standard_cost'],
                    'is_active' => true,
                ]
            );
        }

        Product::query()
            ->where('product_type', 'finished')
            ->where('sku', 'like', 'SAF-%')
            ->whereNotIn('sku', $validSkus)
            ->update(['is_active' => false]);
    }
}
