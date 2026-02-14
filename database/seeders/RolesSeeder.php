<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            $definitions = [
                ['key' => 'super_admin',        'label' => 'Super admin',        'is_system' => true],
                ['key' => 'admin',              'label' => 'Admin',              'is_system' => true],
                ['key' => 'warehouse_manager',  'label' => 'Warehouse manager',  'is_system' => true],
                ['key' => 'production_manager', 'label' => 'Production manager', 'is_system' => true],
                ['key' => 'sales_manager',      'label' => 'Sales manager',      'is_system' => true],
                ['key' => 'qc_officer',         'label' => 'QC officer',         'is_system' => true],
                ['key' => 'employee',           'label' => 'Field / office employee', 'is_system' => true],
            ];

            foreach ($definitions as $role) {
                Role::updateOrCreate(
                    ['key' => $role['key']],
                    ['label' => $role['label'], 'is_system' => $role['is_system']]
                );
            }
    }
}
