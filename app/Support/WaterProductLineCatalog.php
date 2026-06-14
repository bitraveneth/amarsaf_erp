<?php

namespace App\Support;

/**
 * Standard finished-goods catalog for SAF mineral water line.
 *
 * SKU pattern: {BRAND}-{SIZE}-{PACK}
 *   PACK  = CTN (carton) | BTL (single bottle) | JAR (20L jar)
 *
 * Standard carton pack sizes (pieces per carton):
 *   500ml → 24 | 1L → 12 | 2L → 6 | 20L jar → 1 (no outer carton)
 */
class WaterProductLineCatalog
{
    public const BRAND_LABEL = 'SAF Mineral Water';

    /** @var array<string, int> Size label => bottles per carton */
    public const CARTON_PIECES = [
        '500ml' => 24,
        '1L' => 12,
        '2L' => 6,
    ];

    /**
     * @return array<int, array{key: string, label: string, volume_ml: int, sku_size: string, default_pack: string}>
     */
    public static function sizeTree(): array
    {
        return [
            ['key' => '500ml', 'label' => '500ml', 'volume_ml' => 500, 'sku_size' => '500ML', 'default_pack' => 'CTN'],
            ['key' => '1l', 'label' => '1L', 'volume_ml' => 1000, 'sku_size' => '1L', 'default_pack' => 'CTN'],
            ['key' => '2l', 'label' => '2L', 'volume_ml' => 2000, 'sku_size' => '2L', 'default_pack' => 'CTN'],
            ['key' => '20l', 'label' => '20L Jar', 'volume_ml' => 20000, 'sku_size' => '20L', 'default_pack' => 'JAR'],
        ];
    }

    public static function cartonPiecesPerPack(string $sizeLabel): ?int
    {
        return self::CARTON_PIECES[$sizeLabel] ?? null;
    }

    public static function cartonPackagingName(string $sizeLabel): string
    {
        $count = self::cartonPiecesPerPack($sizeLabel);

        return 'Carton ' . $count . ' x ' . $sizeLabel;
    }

    public static function sizeCodeSegment(string $sizeLabel): string
    {
        foreach (self::sizeTree() as $size) {
            if ($size['label'] === $sizeLabel || $size['key'] === strtolower($sizeLabel)) {
                return $size['sku_size'];
            }
        }

        return strtoupper(str_replace(' ', '', $sizeLabel));
    }

    public static function packagingTypeCode(string $unit, ?string $sizeKey, ?int $unitsPerPack = null): string
    {
        $sizeCode = self::sizeCodeSegment($sizeKey ?? '');

        return match ($unit) {
            'carton' => 'CTN-' . $unitsPerPack . '-' . $sizeCode,
            'bottle' => 'BTL-' . $sizeCode,
            'jar' => 'JAR-' . $sizeCode,
            default => 'PKG-' . strtoupper(substr($unit ?: 'CUSTOM', 0, 8)),
        };
    }

    /**
     * Plain-language summary for packaging admin UI.
     */
    public static function packSummaryForUi(?string $unit, ?int $unitsPerPack): string
    {
        if ($unit === 'carton' && $unitsPerPack) {
            return $unitsPerPack . ' bottles';
        }

        if ($unit === 'bottle') {
            return '1 bottle';
        }

        if ($unit === 'jar') {
            return '1 jar';
        }

        if ($unit && $unitsPerPack && $unitsPerPack > 1) {
            return $unitsPerPack . ' ' . $unit;
        }

        return $unit ?: '—';
    }

    public static function cartonMaterialSku(string $sizeLabel): ?string
    {
        return match ($sizeLabel) {
            '500ml' => 'RM-CARTON-24X500',
            '1L' => 'RM-CARTON-12X1L',
            '2L' => 'RM-CARTON-6X2L',
            default => null,
        };
    }

    /**
     * @return array<int, array{code: string, name: string, unit: string, description: string, units_per_pack: int|null, size_key: string|null, is_system: bool}>
     */
    public static function packagingTypes(): array
    {
        $types = [];

        foreach (array_keys(self::CARTON_PIECES) as $size) {
            $count = self::cartonPiecesPerPack($size);

            $types[] = [
                'code' => self::packagingTypeCode('carton', $size, $count),
                'name' => self::cartonPackagingName($size),
                'unit' => 'carton',
                'description' => $count . ' × ' . $size . ' bottles in one carton',
                'units_per_pack' => $count,
                'size_key' => $size,
                'is_system' => true,
            ];
            $types[] = [
                'code' => self::packagingTypeCode('bottle', $size, 1),
                'name' => 'Bottle ' . $size,
                'unit' => 'bottle',
                'description' => 'Single ' . $size . ' PET bottle',
                'units_per_pack' => 1,
                'size_key' => $size,
                'is_system' => true,
            ];
        }

        $types[] = [
            'code' => self::packagingTypeCode('jar', '20L', 1),
            'name' => 'Jar 20L',
            'unit' => 'jar',
            'description' => 'Refillable 20L water jar (sold as one piece, no outer carton)',
            'units_per_pack' => 1,
            'size_key' => '20L',
            'is_system' => true,
        ];

        return $types;
    }

    /**
     * @return array<int, array{size: string, sell_unit: string, pieces: int|string, packaging: string, note: string}>
     */
    public static function packagingPresetsForUi(): array
    {
        $rows = [];

        foreach (self::CARTON_PIECES as $size => $count) {
            $rows[] = [
                'size' => $size,
                'sell_unit' => 'Carton',
                'pieces' => $count,
                'packaging' => self::cartonPackagingName($size),
                'note' => $count . ' bottles per carton',
            ];
        }

        $rows[] = [
            'size' => '20L',
            'sell_unit' => 'Jar',
            'pieces' => 1,
            'packaging' => 'Jar 20L',
            'note' => 'No outer carton — sold as one jar',
        ];

        return $rows;
    }

    /**
     * Legacy carton names replaced by SAF standard packs.
     *
     * @return array<string, string>
     */
    public static function legacyPackagingRenames(): array
    {
        $map = [];

        foreach (array_keys(self::CARTON_PIECES) as $size) {
            $count = self::cartonPiecesPerPack($size);
            $map[$size . ' carton · ' . $count . ' bottles'] = self::cartonPackagingName($size);
        }

        return $map;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function primaryFinishedProducts(): array
    {
        return [
            self::productRow('500ML', 'CTN', '500ml', 500, self::cartonPackagingName('500ml'), 'carton', 550, 380, null, 12500, 12),
            self::productRow('1L', 'CTN', '1L', 1000, self::cartonPackagingName('1L'), 'carton', 900, 620, null, 12600, 12),
            self::productRow('2L', 'CTN', '2L', 2000, self::cartonPackagingName('2L'), 'carton', 1400, 960, null, 13200, 12),
            self::productRow('20L', 'JAR', '20L', 20000, 'Jar 20L', 'jar', 250, 165, null, 22000, 12),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function optionalSingleBottleProducts(): array
    {
        return [
            self::productRow('500ML', 'BTL', '500ml', 500, 'Bottle 500ml', 'bottle', 45, 28, null, 520, 12),
            self::productRow('1L', 'BTL', '1L', 1000, 'Bottle 1L', 'bottle', 75, 50, null, 1050, 12),
            self::productRow('2L', 'BTL', '2L', 2000, 'Bottle 2L', 'bottle', 110, 75, null, 2100, 12),
        ];
    }

    public static function sku(string $sizeSegment, string $packCode): string
    {
        $brand = SkuGenerator::brandPrefix();

        return SkuGenerator::normalize($brand . '-' . $sizeSegment . '-' . $packCode);
    }

    public static function displayName(string $sizeLabel, string $packCode): string
    {
        if ($packCode === 'CTN') {
            $count = self::cartonPiecesPerPack($sizeLabel) ?? 1;

            return self::BRAND_LABEL . ' ' . $sizeLabel . ' – Carton (' . $count . ' bottles)';
        }

        if ($packCode === 'JAR') {
            return self::BRAND_LABEL . ' ' . $sizeLabel . ' – Jar';
        }

        return self::BRAND_LABEL . ' ' . $sizeLabel . ' – Bottle';
    }

    /**
     * Standard finished-good names with spec defaults for the product form combobox.
     *
     * @return array<int, array{name: string, volume_ml: int, size: string, uom: string, packaging_name: string, sku: string, shelf_life_months: int}>
     */
    public static function namePresetsForForm(): array
    {
        return collect(self::primaryFinishedProducts())
            ->merge(self::optionalSingleBottleProducts())
            ->map(fn (array $row) => [
                'name' => $row['name'],
                'volume_ml' => $row['volume_ml'],
                'size' => $row['size'],
                'uom' => $row['uom'],
                'packaging_name' => $row['packaging_name'],
                'sku' => $row['sku'],
                'shelf_life_months' => $row['shelf_life_months'],
            ])
            ->unique('name')
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function standardProductNames(): array
    {
        return collect(self::namePresetsForForm())->pluck('name')->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function guideForUi(): array
    {
        $rows = [];
        foreach (self::primaryFinishedProducts() as $product) {
            $rows[] = [
                'size' => $product['size'],
                'sku' => $product['sku'],
                'name' => $product['name'],
                'uom' => $product['uom'],
            ];
        }

        return [
            'pattern' => SkuGenerator::brandPrefix() . '-{SIZE}-{PACK}',
            'name_pattern' => self::BRAND_LABEL . ' {size} – {pack}',
            'pack_codes' => [
                'CTN' => 'Carton — 500ml×24, 1L×12, 2L×6',
                'BTL' => 'Single bottle (optional retail SKU)',
                'JAR' => '20L refill jar — 1 piece, no carton',
            ],
            'carton_packs' => self::CARTON_PIECES,
            'tree' => collect(self::sizeTree())->pluck('label')->all(),
            'primary_skus' => $rows,
        ];
    }

    protected static function productRow(
        string $sizeSegment,
        string $packCode,
        string $sizeLabel,
        int $volumeMl,
        string $packagingName,
        string $uom,
        float $basePrice,
        float $standardCost,
        ?float $mrp = null,
        ?int $weightG = null,
        int $shelfLifeMonths = 12,
        string $brand = 'SAF'
    ): array {
        $sku = self::sku($sizeSegment, $packCode);
        $sizeField = $packCode === 'CTN' ? $sizeLabel . ' carton' : $sizeLabel;

        return [
            'sku' => $sku,
            'name' => self::displayName($sizeLabel, $packCode),
            'brand' => $brand,
            'description' => self::BRAND_LABEL . ', ' . strtolower($sizeLabel) . ' — ' . strtolower($packagingName),
            'size' => $sizeField,
            'uom' => $uom,
            'volume_ml' => $volumeMl,
            'weight_g' => $weightG,
            'shelf_life_months' => $shelfLifeMonths,
            'packaging_name' => $packagingName,
            'base_price' => $basePrice,
            'mrp' => $mrp ?? round($basePrice * 1.2, 2),
            'standard_cost' => $standardCost,
        ];
    }
}
