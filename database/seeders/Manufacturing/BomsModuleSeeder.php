<?php

namespace Database\Seeders\Manufacturing;

use App\Models\BillOfMaterial;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seed data for Manufacturing → BOMs (Bill of Materials).
 *
 * For now we create one standard BOM for:
 * - SAF-500ML-CTN (500ml carton, 12 bottles)
 */
class BomsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $finished = Product::where('sku', 'SAF-500ML-CTN')->first();

        if (! $finished) {
            return;
        }

        $petBottle  = Product::where('sku', 'RM-PET-500')->first();
        $cap        = Product::where('sku', 'RM-CAP-STD')->first();
        $label      = Product::where('sku', 'RM-LABEL-500')->first();
        $carton     = Product::where('sku', 'RM-CARTON-12X500')->first();
        $shrinkWrap = Product::where('sku', 'RM-SHRINK-CTN')->first();
        $water      = Product::where('sku', 'RM-RO-WATER')->first();
        $labour     = Product::where('sku', 'SV-LAB-FACT')->first();
        $utilities  = Product::where('sku', 'SV-UTIL-SHIFT')->first();

        $bom = BillOfMaterial::firstOrCreate(
            [
                'product_id' => $finished->id,
                'name'       => 'SAF-500ML standard',
            ],
            [
                'is_active' => true,
                'notes'     => '1 carton = 12 bottles, 12 caps, 12 labels, carton box, shrink wrap, 6L RO water, labour & utilities.',
            ]
        );

        if ($bom->items()->count() > 0) {
            return;
        }

        // Build items with dummy unit_cost seeded from material standard_cost
        $items = [
            [
                'component_product_id' => optional($petBottle)->id,
                'quantity'             => 12,
                'unit_cost'           => optional($petBottle)->standard_cost,
                'unit'                 => 'bottle',
            ],
            [
                'component_product_id' => optional($cap)->id,
                'quantity'             => 12,
                'unit_cost'           => optional($cap)->standard_cost,
                'unit'                 => 'cap',
            ],
            [
                'component_product_id' => optional($label)->id,
                'quantity'             => 12,
                'unit_cost'           => optional($label)->standard_cost,
                'unit'                 => 'label',
            ],
            [
                'component_product_id' => optional($carton)->id,
                'quantity'             => 1,
                'unit_cost'           => optional($carton)->standard_cost,
                'unit'                 => 'carton',
            ],
            [
                'component_product_id' => optional($shrinkWrap)->id,
                'quantity'             => 1,
                'unit_cost'           => optional($shrinkWrap)->standard_cost,
                'unit'                 => 'wrap',
            ],
            [
                'component_product_id' => optional($water)->id,
                'quantity'             => 6.0,
                'unit_cost'           => optional($water)->standard_cost,
                'unit'                 => 'liter',
            ],
            [
                'component_product_id' => optional($labour)->id,
                'quantity'             => 0.5,
                'unit_cost'           => optional($labour)->standard_cost,
                'unit'                 => 'day',
            ],
            [
                'component_product_id' => optional($utilities)->id,
                'quantity'             => 0.2,
                'unit_cost'           => optional($utilities)->standard_cost,
                'unit'                 => 'shift',
            ],
        ];

        $bom->items()->createMany($items);

        // Also seed a BOM-level material_unit_cost (sum of qty * unit_cost)
        $materialUnitCost = 0.0;
        foreach ($items as $item) {
            if (! empty($item['unit_cost'])) {
                $materialUnitCost += (float) $item['unit_cost'] * (float) $item['quantity'];
            }
        }

        if ($materialUnitCost > 0) {
            $bom->material_unit_cost = $materialUnitCost;
            $bom->save();
        }
    }
}
