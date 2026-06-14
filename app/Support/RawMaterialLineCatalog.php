<?php

namespace App\Support;

use App\Models\MaterialCategory;
use App\Models\Product;

/**
 * SAF raw-material categories — flat list grouped by `group` in the UI.
 *
 * SKU pattern: RM-{CODE} · SV-{CODE} · IH-{CODE}
 */
class RawMaterialLineCatalog
{
    /**
     * Standard category groups shown as optgroup headers in dropdowns.
     *
     * @return array<int, string>
     */
    public static function categoryGroups(): array
    {
        return [
            'Preforms & Caps',
            'Labels & Wrap',
            'Minerals',
            'RO & Water Treatment',
            'Other',
        ];
    }

    /**
     * Virtual accordion section for in-house production steps (not a category optgroup).
     */
    public static function inhouseSectionLabel(): string
    {
        return 'In-house Production';
    }

    /**
     * Category groups where chemical / scientific names apply on the material form.
     *
     * @return array<int, string>
     */
    public static function chemicalNameGroups(): array
    {
        return ['Minerals', 'RO & Water Treatment'];
    }

    /**
     * @return array<int, array{code: string, name: string, group: string, sort_order: int, is_system: bool, description?: string}>
     */
    public static function categories(): array
    {
        return [
            // Preforms & Caps
            ['code' => 'PREF', 'name' => 'Preform', 'group' => 'Preforms & Caps', 'sort_order' => 10, 'is_system' => true],
            ['code' => 'CAP-STD', 'name' => 'Cap-universal', 'group' => 'Preforms & Caps', 'sort_order' => 20, 'is_system' => true],
            ['code' => 'CAP-JAR', 'name' => 'Jar-cap', 'group' => 'Preforms & Caps', 'sort_order' => 30, 'is_system' => true],

            // Labels & Wrap
            ['code' => 'BOPP', 'name' => 'BOPP Label', 'group' => 'Labels & Wrap', 'sort_order' => 40, 'is_system' => true],
            ['code' => 'SHRINK', 'name' => 'Shrink Wrap', 'group' => 'Labels & Wrap', 'sort_order' => 50, 'is_system' => true],
            ['code' => 'JAR-LBL', 'name' => 'Jar-label', 'group' => 'Labels & Wrap', 'sort_order' => 60, 'is_system' => true],
            ['code' => 'JAR-NECK', 'name' => 'Jar-neck label', 'group' => 'Labels & Wrap', 'sort_order' => 70, 'is_system' => true],
            ['code' => 'JAR-CAP-STK', 'name' => 'Jar-cap sticker', 'group' => 'Labels & Wrap', 'sort_order' => 80, 'is_system' => true],

            // Minerals
            ['code' => 'MIN-K', 'name' => 'Potassium', 'group' => 'Minerals', 'sort_order' => 90, 'is_system' => true],
            ['code' => 'MIN-MG', 'name' => 'Magnesium', 'group' => 'Minerals', 'sort_order' => 100, 'is_system' => true],
            ['code' => 'MIN-CA', 'name' => 'Calcium', 'group' => 'Minerals', 'sort_order' => 110, 'is_system' => true],
            ['code' => 'MIN-UNIV', 'name' => 'Minerals (Universal)', 'group' => 'Minerals', 'sort_order' => 120, 'is_system' => true],

            // RO & Water Treatment
            ['code' => 'RO-ANTISCAL', 'name' => 'Antiscalant', 'group' => 'RO & Water Treatment', 'sort_order' => 125, 'is_system' => true],
            ['code' => 'RO-CLEAN', 'name' => 'Membrane Cleaner', 'group' => 'RO & Water Treatment', 'sort_order' => 126, 'is_system' => true],
            ['code' => 'RO-CHLOR', 'name' => 'Chlorine / Hypochlorite', 'group' => 'RO & Water Treatment', 'sort_order' => 127, 'is_system' => true],
            ['code' => 'RO-ACID', 'name' => 'pH Acid', 'group' => 'RO & Water Treatment', 'sort_order' => 128, 'is_system' => true],
            ['code' => 'RO-ALKALI', 'name' => 'pH Alkali', 'group' => 'RO & Water Treatment', 'sort_order' => 129, 'is_system' => true],
            ['code' => 'RO-CARBON', 'name' => 'Activated Carbon', 'group' => 'RO & Water Treatment', 'sort_order' => 130, 'is_system' => true],
            ['code' => 'RO-SALT', 'name' => 'Softener Salt', 'group' => 'RO & Water Treatment', 'sort_order' => 131, 'is_system' => true],

            // Other
            ['code' => 'GLUE', 'name' => 'Hot Melt Glue', 'group' => 'Other', 'sort_order' => 130, 'is_system' => true],
            ['code' => 'CTN', 'name' => 'Carton', 'group' => 'Other', 'sort_order' => 140, 'is_system' => true],
            ['code' => 'WATER', 'name' => 'Treated Water', 'group' => 'Other', 'sort_order' => 145, 'is_system' => true],
            ['code' => 'SVC', 'name' => 'Production Service', 'group' => 'Other', 'sort_order' => 150, 'is_system' => true],
            ['code' => 'IH', 'name' => 'In-house Step', 'group' => 'Other', 'sort_order' => 160, 'is_system' => true],
        ];
    }

    /**
     * Map retired hierarchical codes → current flat codes.
     *
     * @return array<string, string>
     */
    public static function legacyCodeMigrations(): array
    {
        return [
            'PREF-500' => 'PREF',
            'PREF-1L' => 'PREF',
            'PREF-2L' => 'PREF',
            'CAP' => 'CAP-STD',
            'CAP-500' => 'CAP-STD',
            'CAP-1L' => 'CAP-STD',
            'CAP-2L' => 'CAP-STD',
            'LBL' => 'BOPP',
            'LBL-500' => 'BOPP',
            'LBL-1L' => 'BOPP',
            'LBL-2L' => 'BOPP',
            'LBL-20L' => 'JAR-LBL',
            'LBL-JAR-NECK' => 'JAR-NECK',
            'LBL-JAR-CAP' => 'JAR-CAP-STK',
            'MIN' => 'MIN-UNIV',
            'CTN-24X500' => 'CTN',
            'CTN-12X1L' => 'CTN',
            'CTN-6X2L' => 'CTN',
            'SV-LAB' => 'SVC',
            'SV-UTIL' => 'SVC',
            'IH-LBL' => 'IH',
            'IH-SHRINK' => 'IH',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function legacyCategoryRenames(): array
    {
        return [
            'Preform 500ml' => 'Preform',
            'Preform 1L' => 'Preform',
            'Preform 2L' => 'Preform',
            'Cap Universal' => 'Cap-universal',
            'Cap 500ml' => 'Cap-universal',
            'Cap 1L' => 'Cap-universal',
            'Cap 2L' => 'Cap-universal',
            'Jar Cap' => 'Jar-cap',
            'Label' => 'BOPP Label',
            'Label 500ml' => 'BOPP Label',
            'Label 1L' => 'BOPP Label',
            'Label 2L' => 'BOPP Label',
            'Label 20L Jar' => 'Jar-label',
            'Label Jar Neck' => 'Jar-neck label',
            'Label Jar Cap' => 'Jar-cap sticker',
            'Minerals Universal' => 'Minerals (Universal)',
            'Factory Labour' => 'Production Service',
            'Utilities / Shift' => 'Production Service',
            'Label Printing' => 'In-house Step',
            'Shrink / Case Making' => 'In-house Step',
            'Carton 24 x 500ml' => 'Carton',
            'Carton 12 x 1L' => 'Carton',
            'Carton 6 x 2L' => 'Carton',
        ];
    }

    public static function typePrefix(string $productType): string
    {
        return match ($productType) {
            'service' => 'SV',
            'inhouse' => 'IH',
            default => 'RM',
        };
    }

    public static function suggestSku(string $productType, ?MaterialCategory $category, ?string $size = null): string
    {
        $prefix = self::typePrefix($productType);

        if ($category?->code) {
            $code = strtoupper($category->code);
            if (str_starts_with($code, $prefix . '-')) {
                $base = $code;
            } else {
                $base = $prefix . '-' . $code;
            }
        } else {
            $sizeSegment = SkuGenerator::normalizeSizeSegment($size);
            $base = $prefix . '-' . ($sizeSegment !== 'STD' ? $sizeSegment : 'GEN');
        }

        return SkuGenerator::ensureUniquePublic(SkuGenerator::normalize($base));
    }

    /**
     * @return array<string, mixed>
     */
    public static function guideForUi(): array
    {
        return [
            'sku_pattern' => 'RM-{CATEGORY_CODE} · SV-{CODE} · IH-{CODE}',
            'groups' => self::categoryGroups(),
            'notes' => [
                'Categories are grouped: Preforms & Caps, Labels & Wrap, Minerals, RO & Water Treatment, Other.',
                'In-house steps live in the In-house Production section — use type In-house step on the form.',
                'For minerals and RO chemicals, add the scientific chemical name (e.g. Sodium Hypochlorite).',
                'Raw materials can be Purchased, Made in-house, or Both when you sometimes buy and sometimes produce.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function seedMaterials(): array
    {
        return [
            ['sku' => 'RM-PREF', 'name' => 'PET Preform', 'product_type' => 'raw', 'category_code' => 'PREF', 'uom' => 'piece', 'size' => '500ml', 'standard_cost' => 5.00, 'supplier_name' => 'ABC Plastics'],
            ['sku' => 'RM-CAP-STD', 'name' => 'Bottle Cap – Standard', 'product_type' => 'raw', 'category_code' => 'CAP-STD', 'uom' => 'piece', 'standard_cost' => 0.80, 'supplier_name' => 'ABC Plastics'],
            ['sku' => 'RM-WATER', 'name' => 'Treated RO Water', 'product_type' => 'raw', 'category_code' => 'WATER', 'uom' => 'liter', 'standard_cost' => 0.30, 'supplier_name' => null, 'sourcing' => 'inhouse'],
            ['sku' => 'RM-MIN-K', 'name' => 'Potassium Chloride', 'product_type' => 'raw', 'category_code' => 'MIN-K', 'uom' => 'kg', 'standard_cost' => 180.00, 'supplier_name' => 'ChemSupply', 'chemical_name' => 'Potassium Chloride (KCl)', 'sourcing' => 'purchased'],
            ['sku' => 'RM-MIN-MG', 'name' => 'Magnesium Sulphate', 'product_type' => 'raw', 'category_code' => 'MIN-MG', 'uom' => 'kg', 'standard_cost' => 95.00, 'supplier_name' => 'ChemSupply', 'chemical_name' => 'Magnesium Sulphate (MgSO₄)', 'sourcing' => 'purchased'],
            ['sku' => 'RM-RO-ANTISCAL', 'name' => 'RO Antiscalant', 'product_type' => 'raw', 'category_code' => 'RO-ANTISCAL', 'uom' => 'liter', 'standard_cost' => 450.00, 'supplier_name' => 'WaterTech', 'chemical_name' => 'Phosphonate-based antiscalant', 'sourcing' => 'purchased'],
            ['sku' => 'RM-RO-CHLOR', 'name' => 'RO Plant Chlorine', 'product_type' => 'raw', 'category_code' => 'RO-CHLOR', 'uom' => 'liter', 'standard_cost' => 35.00, 'supplier_name' => 'WaterTech', 'chemical_name' => 'Sodium Hypochlorite (NaOCl)', 'sourcing' => 'purchased'],
            ['sku' => 'RM-RO-SALT', 'name' => 'Softener Salt', 'product_type' => 'raw', 'category_code' => 'RO-SALT', 'uom' => 'kg', 'standard_cost' => 12.00, 'supplier_name' => 'Local Salt', 'chemical_name' => 'Sodium Chloride (NaCl)', 'sourcing' => 'purchased'],
            ['sku' => 'RM-BOPP', 'name' => 'BOPP Label', 'product_type' => 'raw', 'category_code' => 'BOPP', 'uom' => 'piece', 'size' => '500ml', 'standard_cost' => 0.60, 'supplier_name' => 'XYZ Labels', 'sourcing' => 'both'],
            ['sku' => 'RM-CTN-24X500', 'name' => 'Carton Box – 24 x 500ml', 'product_type' => 'raw', 'category_code' => 'CTN', 'uom' => 'piece', 'standard_cost' => 22.00, 'supplier_name' => 'CartonCo'],
            ['sku' => 'RM-CTN-12X1L', 'name' => 'Carton Box – 12 x 1L', 'product_type' => 'raw', 'category_code' => 'CTN', 'uom' => 'piece', 'standard_cost' => 24.00, 'supplier_name' => 'CartonCo'],
            ['sku' => 'RM-CTN-6X2L', 'name' => 'Carton Box – 6 x 2L', 'product_type' => 'raw', 'category_code' => 'CTN', 'uom' => 'piece', 'standard_cost' => 26.00, 'supplier_name' => 'CartonCo'],
            ['sku' => 'RM-SHRINK', 'name' => 'Shrink Wrap Film', 'product_type' => 'raw', 'category_code' => 'SHRINK', 'uom' => 'piece', 'standard_cost' => 2.50, 'supplier_name' => 'Packaging Ltd'],
            ['sku' => 'SV-SVC', 'name' => 'Labour – Factory Line', 'product_type' => 'service', 'category_code' => 'SVC', 'uom' => 'day', 'standard_cost' => 1200.00, 'supplier_name' => 'John Contractor'],
            ['sku' => 'SV-UTIL', 'name' => 'Electricity / Utilities per Shift', 'product_type' => 'service', 'category_code' => 'SVC', 'uom' => 'shift', 'standard_cost' => 600.00, 'supplier_name' => 'Local Utility'],
            ['sku' => 'IH-STEP', 'name' => 'Label Printing', 'product_type' => 'inhouse', 'category_code' => 'IH', 'uom' => 'piece', 'standard_cost' => 0.00, 'supplier_name' => null],
            ['sku' => 'IH-SHRINK', 'name' => 'Shrink Wrapping / Case Making', 'product_type' => 'inhouse', 'category_code' => 'IH', 'uom' => 'piece', 'standard_cost' => 0.00, 'supplier_name' => null],
        ];
    }

    public static function resolveCategoryIdByCode(string $code): ?int
    {
        return MaterialCategory::where('code', $code)->value('id');
    }
}
