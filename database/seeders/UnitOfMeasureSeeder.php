<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class UnitOfMeasureSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['code' => 'piece', 'name' => 'Piece', 'symbol' => 'pc', 'sort_order' => 10],
            ['code' => 'liter', 'name' => 'Litre', 'symbol' => 'L', 'sort_order' => 20],
            ['code' => 'ml', 'name' => 'Millilitre', 'symbol' => 'ml', 'sort_order' => 30],
            ['code' => 'kg', 'name' => 'Kilogram', 'symbol' => 'kg', 'sort_order' => 40],
            ['code' => 'gram', 'name' => 'Gram', 'symbol' => 'g', 'sort_order' => 50],
            ['code' => 'carton', 'name' => 'Carton', 'symbol' => 'ctn', 'sort_order' => 60],
            ['code' => 'roll', 'name' => 'Roll', 'symbol' => 'roll', 'sort_order' => 70],
            ['code' => 'day', 'name' => 'Day', 'symbol' => 'day', 'sort_order' => 80],
            ['code' => 'shift', 'name' => 'Shift', 'symbol' => 'shift', 'sort_order' => 90],
            ['code' => 'bottle', 'name' => 'Bottle', 'symbol' => 'btl', 'sort_order' => 100],
            ['code' => 'jar', 'name' => 'Jar', 'symbol' => 'jar', 'sort_order' => 110],
        ];

        foreach ($units as $unit) {
            UnitOfMeasure::updateOrCreate(
                ['code' => $unit['code']],
                array_merge($unit, ['is_system' => true])
            );
        }
    }
}
