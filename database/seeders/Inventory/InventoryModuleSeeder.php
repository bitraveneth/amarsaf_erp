<?php

namespace Database\Seeders\Inventory;

use Illuminate\Database\Seeder;

/**
 * Orchestrator for the Inventory (Core Operations) module.
 *
 * Delegates to:
 * - InventoryDashboardSeeder
 * - TransfersModuleSeeder
 * - DeliveriesPodModuleSeeder
 * - VehicleLoadsModuleSeeder
 * - PackingSlipsModuleSeeder
 * - InventoryAdjustmentsModuleSeeder
 */
class InventoryModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            InventoryDashboardSeeder::class,
            MaterialStockSeeder::class,
            TransfersModuleSeeder::class,
            DeliveriesPodModuleSeeder::class,
            VehicleLoadsModuleSeeder::class,
            PackingSlipsModuleSeeder::class,
            InventoryAdjustmentsModuleSeeder::class,
        ]);
    }
}

