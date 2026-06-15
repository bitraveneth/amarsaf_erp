<?php

namespace Database\Seeders\Control\Products;

use App\Support\Seeding\LegacyCatalogSupport;
use Database\Seeders\Legacy\LegacyProductSqlImportSeeder;
use Illuminate\Database\Seeder;

/**
 * Orchestrator for the Control → Products area.
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
        ]);

        if (LegacyCatalogSupport::shouldImportLegacyCatalog() && LegacyCatalogSupport::hasLegacyDump()) {
            $this->call(LegacyProductSqlImportSeeder::class);
        } else {
            $this->call([
                MaterialsSeeder::class,
                ProductsSeeder::class,
            ]);
        }

        $this->call(PriceListsSeeder::class);
    }
}
