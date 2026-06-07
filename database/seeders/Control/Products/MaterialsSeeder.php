<?php

namespace Database\Seeders\Control\Products;

use App\Models\Product;
use App\Models\TaxClass;
use App\Support\MaterialCategoryAssigner;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Materials (raw / service / in‑house) used in BOMs.
 */
class MaterialsSeeder extends Seeder
{
    public function run(): void
    {
        $vatExempt = TaxClass::where('name', 'VAT exempt')->first();

        if (! $vatExempt) {
            $vatExempt = TaxClass::create([
                'name' => 'VAT exempt',
                'rate' => 0,
            ]);
        }

        $materials = [
            [
                'sku' => 'RM-PET-500',
                'name' => 'PET Bottle 500ml',
                'product_type' => 'raw',
                'uom' => 'piece',
                'standard_cost' => 5.00,
                'supplier_name' => 'ABC Plastics',
            ],
            [
                'sku' => 'RM-CAP-STD',
                'name' => 'Bottle Cap – Standard',
                'product_type' => 'raw',
                'uom' => 'piece',
                'standard_cost' => 0.80,
                'supplier_name' => 'ABC Plastics',
            ],
            [
                'sku' => 'RM-LABEL-500',
                'name' => 'BOPP Label – 500ml Bottle',
                'product_type' => 'raw',
                'uom' => 'piece',
                'standard_cost' => 0.60,
                'supplier_name' => 'XYZ Labels',
            ],
            [
                'sku' => 'RM-CARTON-12X500',
                'name' => 'Carton Box – 12 x 500ml',
                'product_type' => 'raw',
                'uom' => 'piece',
                'standard_cost' => 20.00,
                'supplier_name' => 'CartonCo',
            ],
            [
                'sku' => 'RM-SHRINK-CTN',
                'name' => 'Shrink Wrap Film – Carton',
                'product_type' => 'raw',
                'uom' => 'piece',
                'standard_cost' => 2.50,
                'supplier_name' => 'Packaging Ltd',
            ],
            [
                'sku' => 'RM-RO-WATER',
                'name' => 'Treated RO Water',
                'product_type' => 'raw',
                'uom' => 'liter',
                'standard_cost' => 0.30,
                'supplier_name' => 'Local Water Provider',
            ],
            [
                'sku' => 'SV-LAB-FACT',
                'name' => 'Labour – Factory Line',
                'product_type' => 'service',
                'uom' => 'day',
                'standard_cost' => 1200.00,
                'supplier_name' => 'John Contractor',
            ],
            [
                'sku' => 'SV-UTIL-SHIFT',
                'name' => 'Electricity / Utilities per Shift',
                'product_type' => 'service',
                'uom' => 'shift',
                'standard_cost' => 600.00,
                'supplier_name' => 'Local Utility',
            ],
            [
                'sku' => 'IH-LABEL-PRINT',
                'name' => 'Label Printing',
                'product_type' => 'inhouse',
                'uom' => 'piece',
                'standard_cost' => 0.00,
                'supplier_name' => null,
            ],
            [
                'sku' => 'IH-SHRINK-PROC',
                'name' => 'Shrink Wrapping / Case Making',
                'product_type' => 'inhouse',
                'uom' => 'piece',
                'standard_cost' => 0.00,
                'supplier_name' => null,
            ],
        ];

        foreach ($materials as $row) {
            $attributes = MaterialCategoryAssigner::withCategory(array_merge($row, [
                'tax_class_id' => $vatExempt->id,
                'base_price' => 0,
                'is_active' => true,
            ]), $row['sku'], $row['name']);

            Product::updateOrCreate(
                ['sku' => $row['sku']],
                $attributes
            );
        }
    }
}
