<?php

namespace Database\Seeders;

use App\Support\MaterialCategoryAssigner;
use Illuminate\Database\Seeder;

class BackfillMaterialCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MaterialCategorySeeder::class);

        $updated = MaterialCategoryAssigner::backfillMissing();

        $this->command?->info("Backfilled material categories for {$updated} material(s).");
    }
}
