<?php

namespace Database\Seeders\Control\Products;

use Illuminate\Database\Seeder;

/**
 * Orchestrator for the Control → Products area.
 *
 * This seeder simply delegates to the more granular seeders:
 * - TaxVatClassesSeeder
 * - PackagingTypesSeeder
 * - MaterialsSeeder
 * - ProductsSeeder
 * - PriceListsSeeder
 */
class ProductsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TaxVatClassesSeeder::class,
            PackagingTypesSeeder::class,
            PackagingConversionsSeeder::class,
            \Database\Seeders\MaterialCategorySeeder::class,
            \Database\Seeders\UnitOfMeasureSeeder::class,
            MaterialsSeeder::class,
            ProductsSeeder::class,
            PriceListsSeeder::class,
        ]);
    }
}
