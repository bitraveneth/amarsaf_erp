<?php

namespace Database\Seeders\Control\Products;

use App\Models\PackagingConversion;
use App\Models\PackagingType;
use App\Models\Product;
use App\Support\WaterProductLineCatalog;
use Illuminate\Database\Seeder;

/**
 * Seed SAF standard packaging types (bottles, cartons, jar).
 */
class PackagingTypesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (WaterProductLineCatalog::packagingTypes() as $type) {
            PackagingType::updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'unit' => $type['unit'],
                    'description' => $type['description'],
                    'units_per_pack' => $type['units_per_pack'],
                    'size_key' => $type['size_key'],
                    'is_system' => $type['is_system'],
                ]
            );
        }

        foreach (WaterProductLineCatalog::legacyPackagingRenames() as $oldName => $newName) {
            $oldType = PackagingType::where('name', $oldName)->first();
            $newType = PackagingType::where('name', $newName)->first();

            if (! $oldType) {
                continue;
            }

            if (! $newType) {
                $oldType->update(['name' => $newName]);

                continue;
            }

            if ($oldType->id === $newType->id) {
                continue;
            }

            Product::where('packaging_type_id', $oldType->id)
                ->update(['packaging_type_id' => $newType->id]);

            PackagingConversion::where('from_packaging_type_id', $oldType->id)
                ->update(['from_packaging_type_id' => $newType->id]);
            PackagingConversion::where('to_packaging_type_id', $oldType->id)
                ->update(['to_packaging_type_id' => $newType->id]);

            $oldType->delete();
        }

        $this->pruneRemovedTypes();
    }

    protected function pruneRemovedTypes(): void
    {
        $validNames = collect(WaterProductLineCatalog::packagingTypes())->pluck('name')->all();

        PackagingType::whereNotIn('name', $validNames)->each(function (PackagingType $type) {
            Product::where('packaging_type_id', $type->id)->update(['packaging_type_id' => null]);

            PackagingConversion::query()
                ->where('from_packaging_type_id', $type->id)
                ->orWhere('to_packaging_type_id', $type->id)
                ->delete();

            $type->delete();
        });
    }
}
