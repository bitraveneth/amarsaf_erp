<?php

namespace Database\Seeders;

use App\Models\MaterialCategory;
use Illuminate\Database\Seeder;

class MaterialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['group' => 'Preforms & Caps', 'name' => 'Preform', 'sort_order' => 10],
            ['group' => 'Preforms & Caps', 'name' => 'Cap-universal', 'sort_order' => 20],
            ['group' => 'Preforms & Caps', 'name' => 'Jar-cap', 'sort_order' => 30],
            ['group' => 'Labels & Wrap', 'name' => 'BOPP Label', 'sort_order' => 40],
            ['group' => 'Labels & Wrap', 'name' => 'Shrink Wrap', 'sort_order' => 50],
            ['group' => 'Labels & Wrap', 'name' => 'Jar-label', 'sort_order' => 60],
            ['group' => 'Labels & Wrap', 'name' => 'Jar-neck label', 'sort_order' => 70],
            ['group' => 'Labels & Wrap', 'name' => 'Jar-cap sticker', 'sort_order' => 80],
            ['group' => 'Minerals', 'name' => 'Potassium', 'sort_order' => 90],
            ['group' => 'Minerals', 'name' => 'Magnesium', 'sort_order' => 100],
            ['group' => 'Minerals', 'name' => 'Calcium', 'sort_order' => 110],
            ['group' => 'Minerals', 'name' => 'Minerals (Universal)', 'sort_order' => 120],
            ['group' => 'Other', 'name' => 'Hot Melt Glue', 'sort_order' => 130],
            ['group' => 'Other', 'name' => 'Carton', 'sort_order' => 140],
            ['group' => 'Other', 'name' => 'Production Service', 'sort_order' => 150],
            ['group' => 'Other', 'name' => 'In-house Step', 'sort_order' => 160],
        ];

        foreach ($categories as $row) {
            MaterialCategory::updateOrCreate(
                ['name' => $row['name']],
                [
                    'group' => $row['group'],
                    'sort_order' => $row['sort_order'],
                ]
            );
        }
    }
}
