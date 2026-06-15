<?php

namespace App\Support\Seeding;

use App\Models\MaterialCategory;
use App\Models\Product;
use App\Models\TaxClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Symfony\Component\Process\Process;

class LegacyCatalogSqlImporter
{
    /**
     * @return array<string, mixed>
     */
    public function analyze(): array
    {
        $map = LegacyCatalogSupport::map();
        $dumpPath = LegacyCatalogSupport::dumpPath();

        if (! LegacyCatalogSupport::hasLegacyDump()) {
            return [
                'ok' => false,
                'message' => 'No dump found at ' . $dumpPath,
            ];
        }

        $this->ensureImportDatabase();
        $this->importDumpFile($dumpPath);

        $connection = $map['connection'] ?? 'legacy_import';
        $tables = collect(DB::connection($connection)->select('SHOW TABLES'))
            ->map(fn ($row) => (string) array_values((array) $row)[0])
            ->sort()
            ->values()
            ->all();

        $categoriesTable = $this->categoriesTable($map);
        $productsTable = $this->productsTable($map);

        return [
            'ok' => true,
            'dump' => $dumpPath,
            'import_database' => $map['import_database'] ?? 'saferp_legacy_import',
            'tables' => $tables,
            'categories_table' => $categoriesTable,
            'categories_exists' => in_array($categoriesTable, $tables, true),
            'categories_count' => $this->safeCount($connection, $categoriesTable),
            'products_table' => $productsTable,
            'products_exists' => in_array($productsTable, $tables, true),
            'products_count' => $this->safeCount($connection, $productsTable),
            'finished_count' => $this->safeProductTypeCount($connection, $map, 'finished'),
            'raw_count' => $this->safeProductTypeCount($connection, $map, 'raw'),
        ];
    }

    public function importIntoApplication(): array
    {
        if (! LegacyCatalogSupport::hasLegacyDump()) {
            return ['imported' => false, 'reason' => 'no_dump'];
        }

        $map = LegacyCatalogSupport::map();
        $this->ensureImportDatabase();
        $this->importDumpFile(LegacyCatalogSupport::dumpPath());

        $connection = $map['connection'] ?? 'legacy_import';
        $categoriesTable = $this->categoriesTable($map);
        $productsTable = $this->productsTable($map);

        if (! $this->tableExists($connection, $categoriesTable) || ! $this->tableExists($connection, $productsTable)) {
            throw new RuntimeException(
                "Legacy import tables not found. Expected {$categoriesTable} and {$productsTable}. "
                . 'Run php artisan saf:import-legacy-catalog --analyze to inspect the dump.'
            );
        }

        $vatExempt = TaxClass::firstOrCreate(['name' => 'VAT exempt'], ['rate' => 0]);
        $vat15 = TaxClass::firstOrCreate(['name' => 'Standard VAT 15%'], ['rate' => 15]);

        $categoryIdMap = $this->importCategories($connection, $map, $categoriesTable);
        $productStats = $this->importProducts($connection, $map, $productsTable, $categoryIdMap, $vatExempt, $vat15);

        return [
            'imported' => true,
            'categories' => count($categoryIdMap),
            'products' => $productStats,
        ];
    }

    protected function ensureImportDatabase(): void
    {
        $map = LegacyCatalogSupport::map();
        $database = (string) ($map['import_database'] ?? 'saferp_legacy_import');
        $config = config('database.connections.mysql');

        $sql = sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            str_replace('`', '``', $database)
        );

        DB::connection('mysql')->statement($sql);
    }

    protected function importDumpFile(string $absolutePath): void
    {
        $map = LegacyCatalogSupport::map();
        $database = (string) ($map['import_database'] ?? 'saferp_legacy_import');
        $config = config('database.connections.mysql');
        $mysql = $this->resolveBinary('mysql');

        $process = new Process(array_values(array_filter([
            $mysql,
            $this->hostFlag($config),
            $this->portFlag($config),
            $this->userFlag($config),
            $database,
        ])), base_path(), [
            'MYSQL_PWD' => $config['password'] ?? '',
        ]);

        $process->setInput(file_get_contents($absolutePath) ?: '');
        $process->setTimeout(600);
        $process->mustRun();
    }

    /**
     * @return array<int, int> legacy category id => new category id
     */
    protected function importCategories(string $connection, array $map, string $table): array
    {
        $columns = $this->categoriesColumns($map);
        $rows = DB::connection($connection)->table($table)->orderBy('id')->get();
        $idMap = [];

        foreach ($rows as $row) {
            $code = $this->readColumn($row, $columns['code']);
            $name = $this->readColumn($row, $columns['name']);

            if ($name === null || $name === '') {
                continue;
            }

            $category = MaterialCategory::updateOrCreate(
                ['code' => $code ?: MaterialCategory::query()->where('name', $name)->value('code') ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]+/', '', $name) ?: 'CAT', 0, 12))],
                [
                    'name' => $name,
                    'group' => $this->readColumn($row, $columns['group']),
                    'description' => $this->readColumn($row, $columns['description']),
                    'sort_order' => (int) ($this->readColumn($row, $columns['sort_order']) ?? 0),
                    'is_system' => (bool) ($this->readColumn($row, $columns['is_system']) ?? false),
                ]
            );

            $legacyId = (int) ($row->id ?? 0);
            if ($legacyId > 0) {
                $idMap[$legacyId] = $category->id;
            }
        }

        return $idMap;
    }

    /**
     * @param  array<int, int>  $categoryIdMap
     * @return array{finished: int, raw: int, skipped: int}
     */
    protected function importProducts(
        string $connection,
        array $map,
        string $table,
        array $categoryIdMap,
        TaxClass $vatExempt,
        TaxClass $vat15,
    ): array {
        $columns = $this->productsColumns($map);
        $rows = DB::connection($connection)->table($table)->orderBy('id')->get();
        $stats = ['finished' => 0, 'raw' => 0, 'skipped' => 0];

        foreach ($rows as $row) {
            $sku = trim((string) ($this->readColumn($row, $columns['sku']) ?? ''));
            $name = trim((string) ($this->readColumn($row, $columns['name']) ?? ''));

            if ($sku === '' || $name === '') {
                $stats['skipped']++;

                continue;
            }

            $productType = $this->normalizeProductType(
                $this->readColumn($row, $columns['product_type']),
                $map
            );

            $legacyCategoryId = (int) ($this->readColumn($row, $columns['material_category_id']) ?? 0);
            $materialCategoryId = $categoryIdMap[$legacyCategoryId] ?? null;

            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $name,
                    'product_type' => $productType,
                    'material_category_id' => $productType === 'raw' ? $materialCategoryId : null,
                    'description' => $this->readColumn($row, $columns['description']),
                    'size' => $this->readColumn($row, $columns['size']),
                    'uom' => $this->readColumn($row, $columns['uom']) ?: ($productType === 'raw' ? 'unit' : 'carton'),
                    'base_price' => (float) ($this->readColumn($row, $columns['base_price']) ?? 0),
                    'mrp' => $this->readColumn($row, $columns['mrp']),
                    'standard_cost' => (float) ($this->readColumn($row, $columns['standard_cost']) ?? 0),
                    'supplier_name' => $this->readColumn($row, $columns['supplier_name']),
                    'chemical_name' => $this->readColumn($row, $columns['chemical_name']),
                    'sourcing' => $this->readColumn($row, $columns['sourcing']) ?: 'purchased',
                    'brand' => $this->readColumn($row, $columns['brand']) ?: 'SAF',
                    'tax_class_id' => $productType === 'raw' ? $vatExempt->id : $vat15->id,
                    'is_active' => (bool) ($this->readColumn($row, $columns['is_active']) ?? true),
                ]
            );

            $stats[$productType === 'raw' ? 'raw' : 'finished']++;
        }

        return $stats;
    }

    protected function normalizeProductType(mixed $value, array $map): string
    {
        $key = strtolower(trim((string) ($value ?? 'finished')));
        $mapped = $map['product_type_map'][$key] ?? null;

        return $mapped === 'raw' ? 'raw' : 'finished';
    }

    protected function categoriesTable(array $map): string
    {
        return ($map['mode'] ?? 'v2_direct') === 'mapped'
            ? (string) ($map['legacy_categories_table'] ?? 'material_subgroups')
            : (string) ($map['categories_table'] ?? 'material_categories');
    }

    protected function productsTable(array $map): string
    {
        return ($map['mode'] ?? 'v2_direct') === 'mapped'
            ? (string) ($map['legacy_products_table'] ?? 'catalog_products')
            : (string) ($map['products_table'] ?? 'products');
    }

    /**
     * @return array<string, string|null>
     */
    protected function categoriesColumns(array $map): array
    {
        return ($map['mode'] ?? 'v2_direct') === 'mapped'
            ? ($map['legacy_categories_columns'] ?? [])
            : ($map['categories_columns'] ?? []);
    }

    /**
     * @return array<string, string|null>
     */
    protected function productsColumns(array $map): array
    {
        return ($map['mode'] ?? 'v2_direct') === 'mapped'
            ? ($map['legacy_products_columns'] ?? [])
            : ($map['products_columns'] ?? []);
    }

    protected function readColumn(object $row, ?string $column): mixed
    {
        if ($column === null || $column === '') {
            return null;
        }

        return $row->{$column} ?? null;
    }

    protected function tableExists(string $connection, string $table): bool
    {
        try {
            return Schema::connection($connection)->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function safeCount(string $connection, string $table): ?int
    {
        if (! $this->tableExists($connection, $table)) {
            return null;
        }

        return (int) DB::connection($connection)->table($table)->count();
    }

    protected function safeProductTypeCount(string $connection, array $map, string $type): ?int
    {
        $table = $this->productsTable($map);

        if (! $this->tableExists($connection, $table)) {
            return null;
        }

        $column = $this->productsColumns($map)['product_type'] ?? 'product_type';

        return (int) DB::connection($connection)->table($table)
            ->where($column, $type)
            ->count();
    }

    protected function resolveBinary(string $command): string
    {
        $configKey = $command === 'mysqldump' ? 'database.backup.mysqldump' : 'database.backup.mysql';
        $configured = trim((string) config($configKey, ''));

        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        if ($this->commandIsRunnable($command)) {
            return $command;
        }

        $executable = PHP_OS_FAMILY === 'Windows' ? $command . '.exe' : $command;
        foreach ($this->candidateBinaryPaths($executable) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        throw new RuntimeException("'{$command}' was not found. Set MYSQL_CLIENT_PATH in .env.");
    }

    protected function commandIsRunnable(string $command): bool
    {
        $probe = new Process([$command, '--version']);
        $probe->setTimeout(10);

        try {
            $probe->run();

            return $probe->isSuccessful();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<int, string>
     */
    protected function candidateBinaryPaths(string $executable): array
    {
        $paths = [];

        if (PHP_OS_FAMILY === 'Windows') {
            foreach (['C:\\Program Files', 'C:\\Program Files (x86)'] as $root) {
                foreach (glob($root . '\\MySQL\\MySQL Server *\\bin\\' . $executable) ?: [] as $match) {
                    $paths[] = $match;
                }
            }

            $paths[] = 'C:\\xampp\\mysql\\bin\\' . $executable;
            foreach (glob('C:\\laragon\\bin\\mysql\\*\\bin\\' . $executable) ?: [] as $match) {
                $paths[] = $match;
            }
        }

        return array_values(array_unique($paths));
    }

    protected function hostFlag(array $config): ?string
    {
        return ! empty($config['host']) ? '--host=' . $config['host'] : null;
    }

    protected function portFlag(array $config): ?string
    {
        return ! empty($config['port']) ? '--port=' . $config['port'] : null;
    }

    protected function userFlag(array $config): ?string
    {
        return ! empty($config['username']) ? '--user=' . $config['username'] : null;
    }
}
