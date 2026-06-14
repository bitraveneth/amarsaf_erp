<?php

namespace Database\Seeders\Control\Products;

use App\Models\PackagingConversion;
use App\Models\PackagingType;
use App\Support\WaterProductLineCatalog;
use Illuminate\Database\Seeder;

/**
 * Seed bottle → carton conversion rules for vehicle load planning.
 */
class PackagingConversionsSeeder extends Seeder
{
    public function run(): void
    {
        $validIds = [];

        foreach (WaterProductLineCatalog::CARTON_PIECES as $size => $factor) {
            $bottle = PackagingType::where('name', 'Bottle ' . $size)->first();
            $carton = PackagingType::where('name', WaterProductLineCatalog::cartonPackagingName($size))->first();

            if (! $bottle || ! $carton) {
                continue;
            }

            $conversion = PackagingConversion::updateOrCreate(
                [
                    'from_packaging_type_id' => $bottle->id,
                    'to_packaging_type_id' => $carton->id,
                ],
                [
                    'factor' => $factor,
                    'notes' => 'SAF standard: ' . $factor . ' bottles = 1 carton',
                ]
            );

            $validIds[] = $conversion->id;
        }

        if ($validIds !== []) {
            PackagingConversion::whereNotIn('id', $validIds)->delete();
        }
    }
}
