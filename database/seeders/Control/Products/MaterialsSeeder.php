<?php

namespace Database\Seeders\Control\Products;

use App\Models\Product;
use App\Models\TaxClass;
use App\Support\RawMaterialLineCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $this->migrateLegacySkus();

        foreach (RawMaterialLineCatalog::seedMaterials() as $row) {
            $categoryId = RawMaterialLineCatalog::resolveCategoryIdByCode($row['category_code']);

            Product::updateOrCreate(
                ['sku' => $row['sku']],
                [
                    'name' => $row['name'],
                    'product_type' => $row['product_type'],
                    'material_category_id' => $categoryId,
                    'uom' => $row['uom'],
                    'size' => $row['size'] ?? null,
                    'standard_cost' => $row['standard_cost'],
                    'supplier_name' => $row['supplier_name'] ?? null,
                    'chemical_name' => $row['chemical_name'] ?? null,
                    'sourcing' => $row['sourcing'] ?? 'purchased',
                    'tax_class_id' => $vatExempt->id,
                    'base_price' => 0,
                    'is_active' => true,
                ]
            );
        }
    }

    protected function migrateLegacySkus(): void
    {
        $map = [
            'RM-PET-500' => 'RM-PREF-500',
            'RM-CAP-STD' => 'RM-CAP-STD',
            'RM-LABEL-500' => 'RM-LBL-500',
            'RM-SHRINK-CTN' => 'RM-SHRINK',
            'RM-RO-WATER' => 'RM-WATER',
            'SV-LAB-FACT' => 'SV-LAB',
            'SV-UTIL-SHIFT' => 'SV-UTIL',
            'IH-LABEL-PRINT' => 'IH-LBL',
            'IH-SHRINK-PROC' => 'IH-SHRINK',
        ];

        foreach ($map as $oldSku => $newSku) {
            $old = Product::where('sku', $oldSku)->first();

            if (! $old) {
                continue;
            }

            $existing = Product::where('sku', $newSku)->where('id', '!=', $old->id)->first();

            if ($existing) {
                foreach ([
                    'bill_of_material_items' => 'component_product_id',
                    'purchase_order_items' => 'product_id',
                    'purchase_bill_items' => 'product_id',
                    'stock_entries' => 'product_id',
                    'goods_receipt_items' => 'product_id',
                ] as $table => $column) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->where($column, $old->id)->update([$column => $existing->id]);
                    }
                }

                $old->delete();

                continue;
            }

            $old->update(['sku' => $newSku]);
        }

        $cartonOld = Product::where('sku', 'RM-CARTON-12X500')->first();

        if ($cartonOld) {
            $cartonNew = Product::where('sku', 'RM-CTN-24X500')->first();

            if ($cartonNew && $cartonNew->id !== $cartonOld->id) {
                foreach ([
                    'bill_of_material_items' => 'component_product_id',
                    'purchase_order_items' => 'product_id',
                    'purchase_bill_items' => 'product_id',
                    'stock_entries' => 'product_id',
                    'goods_receipt_items' => 'product_id',
                ] as $table => $column) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->where($column, $cartonOld->id)->update([$column => $cartonNew->id]);
                    }
                }

                $cartonOld->delete();
            } else {
                $cartonOld->update([
                    'sku' => 'RM-CTN-24X500',
                    'name' => 'Carton Box – 24 x 500ml',
                ]);
            }
        }
    }
}
