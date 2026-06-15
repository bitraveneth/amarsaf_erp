<?php

namespace Database\Seeders;

use Database\Seeders\Accounting\ChartOfAccountsModuleSeeder;
use Database\Seeders\Accounting\ExpenseCategorySeeder;
use Database\Seeders\Control\Products\PackagingConversionsSeeder;
use Database\Seeders\Control\Products\PackagingTypesSeeder;
use Database\Seeders\Control\Products\TaxVatClassesSeeder;
use Database\Seeders\Control\SystemSettings\HelpConfigurationSeeder;
use Database\Seeders\Users\AdminUsersSeeder;
use Database\Seeders\Users\PermissionsSeeder;
use Illuminate\Database\Seeder;

/**
 * Production bootstrap — config and reference data only (no demo transactions).
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            MenuStructureSeeder::class,
            ChartOfAccountsModuleSeeder::class,
            ExpenseCategorySeeder::class,
            UnitOfMeasureSeeder::class,
            MaterialCategorySeeder::class,
            TaxVatClassesSeeder::class,
            PackagingTypesSeeder::class,
            PackagingConversionsSeeder::class,
            KycDocumentTypeSeeder::class,
            HelpConfigurationSeeder::class,
            AdminUsersSeeder::class,
        ]);
    }
}
