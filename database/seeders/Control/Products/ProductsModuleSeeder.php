<?php

namespace Database\Seeders\Control\Products;

use Illuminate\Database\Seeder;

/**
 * Orchestrator for the Control → Products area.
 *
 * This seeder simply delegates to the more granular seeders:
 * - ProductsSeeder
 * - MaterialsSeeder
 * - PackagingTypesSeeder
 * - TaxVatClassesSeeder
 * - PriceListsSeeder
 */
class ProductsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductsSeeder::class,
            MaterialsSeeder::class,
            PackagingTypesSeeder::class,
            TaxVatClassesSeeder::class,
            PriceListsSeeder::class,
        ]);
    }
}
