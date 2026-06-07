<?php

namespace Database\Seeders;

use App\Models\KycDocumentType;
use Illuminate\Database\Seeder;

class KycDocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'NID', 'sort_order' => 10],
            ['name' => 'Trade License', 'sort_order' => 20],
            ['name' => 'BIN', 'sort_order' => 30],
            ['name' => 'TIN', 'sort_order' => 40],
            ['name' => 'Passport', 'sort_order' => 50],
        ];

        foreach ($types as $type) {
            KycDocumentType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
