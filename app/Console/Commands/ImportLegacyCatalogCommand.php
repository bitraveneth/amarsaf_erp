<?php

namespace App\Console\Commands;

use App\Support\Seeding\LegacyCatalogSqlImporter;
use App\Support\Seeding\LegacyCatalogSupport;
use Illuminate\Console\Command;

class ImportLegacyCatalogCommand extends Command
{
    protected $signature = 'saf:import-legacy-catalog
                            {--analyze : Inspect the SQL dump without copying into the application database}
                            {--import : Copy legacy catalog into the current application database}';

    protected $description = 'Import products and raw materials (with category subgroups) from storage/app/imports/legacy-catalog.sql';

    public function handle(LegacyCatalogSqlImporter $importer): int
    {
        if (! LegacyCatalogSupport::hasLegacyDump()) {
            $this->error('No dump found at: ' . LegacyCatalogSupport::dumpPath());
            $this->line('Place your SQL file there, then run this command again.');

            return self::FAILURE;
        }

        if ($this->option('analyze') || ! $this->option('import')) {
            $analysis = $importer->analyze();

            if (! ($analysis['ok'] ?? false)) {
                $this->error($analysis['message'] ?? 'Analysis failed.');

                return self::FAILURE;
            }

            $this->info('Legacy dump analysis');
            $this->line('Dump: ' . $analysis['dump']);
            $this->line('Import DB: ' . $analysis['import_database']);
            $this->line('Categories table: ' . $analysis['categories_table'] . ' (' . ($analysis['categories_count'] ?? 0) . ' rows)');
            $this->line('Products table: ' . $analysis['products_table'] . ' (' . ($analysis['products_count'] ?? 0) . ' rows)');
            $this->line('Finished / raw: ' . ($analysis['finished_count'] ?? '?') . ' / ' . ($analysis['raw_count'] ?? '?'));
            $this->newLine();
            $this->line('Tables found: ' . implode(', ', array_slice($analysis['tables'] ?? [], 0, 20))
                . (count($analysis['tables'] ?? []) > 20 ? '…' : ''));

            if (! $this->option('import')) {
                $this->newLine();
                $this->comment('Run with --import to copy catalog into the application database.');
            }
        }

        if ($this->option('import')) {
            $result = $importer->importIntoApplication();
            $this->info(sprintf(
                'Imported %d categories, %d finished products, %d raw materials.',
                $result['categories'] ?? 0,
                $result['products']['finished'] ?? 0,
                $result['products']['raw'] ?? 0,
            ));
        }

        return self::SUCCESS;
    }
}
