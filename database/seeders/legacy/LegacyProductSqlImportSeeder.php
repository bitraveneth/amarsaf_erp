<?php

namespace Database\Seeders\Legacy;

use App\Support\Seeding\LegacyCatalogSqlImporter;
use App\Support\Seeding\LegacyCatalogSupport;
use Illuminate\Database\Seeder;

class LegacyProductSqlImportSeeder extends Seeder
{
    public function run(): void
    {
        if (! LegacyCatalogSupport::shouldImportLegacyCatalog()) {
            return;
        }

        if (! LegacyCatalogSupport::hasLegacyDump()) {
            $this->command?->warn('SEED_LEGACY_CATALOG is enabled but no dump found at ' . LegacyCatalogSupport::dumpPath());

            return;
        }

        $result = app(LegacyCatalogSqlImporter::class)->importIntoApplication();

        if (! ($result['imported'] ?? false)) {
            return;
        }

        $this->command?->info(sprintf(
            'Legacy catalog imported: %d categories, %d finished, %d raw materials.',
            $result['categories'] ?? 0,
            $result['products']['finished'] ?? 0,
            $result['products']['raw'] ?? 0,
        ));
    }
}
