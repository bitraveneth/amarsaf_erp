<?php

namespace Database\Seeders;

use App\Models\MaterialCategory;
use App\Models\Product;
use App\Support\RawMaterialLineCatalog;
use Illuminate\Database\Seeder;

class MaterialCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (RawMaterialLineCatalog::categories() as $row) {
            $existing = MaterialCategory::where('code', $row['code'])->first()
                ?? MaterialCategory::where('name', $row['name'])->first();

            $payload = [
                'code' => $row['code'],
                'name' => $row['name'],
                'group' => $row['group'],
                'description' => $row['description'] ?? null,
                'sort_order' => $row['sort_order'],
                'is_system' => $row['is_system'],
                'parent_id' => null,
            ];

            if ($existing) {
                $existing->update($payload);
            } else {
                MaterialCategory::create($payload);
            }
        }

        foreach (RawMaterialLineCatalog::legacyCodeMigrations() as $oldCode => $newCode) {
            $old = MaterialCategory::where('code', $oldCode)->first();
            $new = MaterialCategory::where('code', $newCode)->first();

            if (! $old || ! $new || $old->id === $new->id) {
                if ($old && ! $new) {
                    continue;
                }

                if ($old && $old->id !== ($new->id ?? null)) {
                    Product::where('material_category_id', $old->id)
                        ->update(['material_category_id' => $new?->id]);
                    $old->delete();
                }

                continue;
            }

            Product::where('material_category_id', $old->id)
                ->update(['material_category_id' => $new->id]);

            $old->delete();
        }

        foreach (RawMaterialLineCatalog::legacyCategoryRenames() as $oldName => $newName) {
            $old = MaterialCategory::where('name', $oldName)->first();
            $new = MaterialCategory::where('name', $newName)->first();

            if (! $old) {
                continue;
            }

            if (! $new) {
                $old->update(['name' => $newName]);

                continue;
            }

            if ($old->id === $new->id) {
                continue;
            }

            Product::where('material_category_id', $old->id)
                ->update(['material_category_id' => $new->id]);

            $old->delete();
        }

        $validCodes = collect(RawMaterialLineCatalog::categories())->pluck('code')->all();

        MaterialCategory::query()
            ->where(function ($query) use ($validCodes) {
                $query->whereNull('code')
                    ->orWhereNotIn('code', $validCodes);
            })
            ->each(function (MaterialCategory $category) use ($validCodes) {
                if ($category->code && in_array($category->code, $validCodes, true)) {
                    return;
                }

                Product::where('material_category_id', $category->id)->update(['material_category_id' => null]);
                $category->delete();
            });
    }
}
