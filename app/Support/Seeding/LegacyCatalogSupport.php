<?php

namespace App\Support\Seeding;

use Illuminate\Support\Facades\File;

class LegacyCatalogSupport
{
    public const IMPORT_RELATIVE_PATH = 'imports/legacy-catalog.sql';

    public static function dumpPath(): string
    {
        return storage_path('app/' . self::IMPORT_RELATIVE_PATH);
    }

    public static function mapPath(): string
    {
        return database_path('seeders/legacy/legacy-catalog-map.php');
    }

    public static function hasLegacyDump(): bool
    {
        return File::isFile(self::dumpPath()) && File::size(self::dumpPath()) > 0;
    }

    public static function shouldImportLegacyCatalog(): bool
    {
        if (filter_var((string) env('SEED_LEGACY_CATALOG', false), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        return self::hasLegacyDump();
    }

    /**
     * @return array<string, mixed>
     */
    public static function map(): array
    {
        $path = self::mapPath();

        if (! File::isFile($path)) {
            return [];
        }

        $map = require $path;

        return is_array($map) ? $map : [];
    }
}
