<?php

namespace Database\Seeders\Control\Suppliers;

use App\Models\Supplier;
use App\Models\SupplierProductCategory;
use Illuminate\Database\Seeder;

class SupplierProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'PET Preforms', 'description' => 'Preforms for bottle blowing', 'sort_order' => 10],
            ['name' => 'Labels & Shrink Wrap', 'description' => 'BOPP labels, shrink sleeves, stickers', 'sort_order' => 20],
            ['name' => 'Cartons & Packaging', 'description' => 'Corrugated cartons, trays, wraps', 'sort_order' => 30],
            ['name' => 'Caps & Closures', 'description' => 'Bottle caps, jar lids, seals', 'sort_order' => 40],
            ['name' => 'RO Water & Utilities', 'description' => 'Treated water, electricity, gas', 'sort_order' => 50],
            ['name' => 'Services & Labour', 'description' => 'Contract labour, maintenance, consulting', 'sort_order' => 60],
            ['name' => 'Logistics & Transport', 'description' => 'Inbound freight and delivery services', 'sort_order' => 70],
            ['name' => 'Other Purchases', 'description' => 'Miscellaneous vendor supplies', 'sort_order' => 80],
        ];

        foreach ($categories as $category) {
            SupplierProductCategory::firstOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $assignments = [
            'ABC Plastics' => ['PET Preforms', 'Caps & Closures'],
            'XYZ Labels' => ['Labels & Shrink Wrap'],
            'CartonCo' => ['Cartons & Packaging'],
            'Packaging Ltd' => ['Cartons & Packaging', 'Labels & Shrink Wrap'],
            'Local Water Provider' => ['RO Water & Utilities'],
            'John Contractor' => ['Services & Labour'],
            'WaterTech Services' => ['Services & Labour', 'RO Water & Utilities'],
            'XYZ Logistics' => ['Logistics & Transport'],
        ];

        foreach ($assignments as $supplierName => $categoryNames) {
            $supplier = Supplier::where('name', $supplierName)->first();

            if (! $supplier) {
                continue;
            }

            $ids = SupplierProductCategory::query()
                ->whereIn('name', $categoryNames)
                ->pluck('id')
                ->all();

            $supplier->productCategories()->syncWithoutDetaching($ids);
        }
    }
}
