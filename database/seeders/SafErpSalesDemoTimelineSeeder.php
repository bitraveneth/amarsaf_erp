<?php

namespace Database\Seeders;

use App\Support\Seeding\SalesDemoConfig;
use App\Support\Seeding\SalesDemoTimelineBuilder;
use Illuminate\Database\Seeder;

class SafErpSalesDemoTimelineSeeder extends Seeder
{
    public function run(): void
    {
        if (! SalesDemoConfig::enabled()) {
            return;
        }

        (new SalesDemoTimelineBuilder($this->command?->getOutput()))->run();
    }
}
