<?php

namespace Database\Seeders\Users;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

/**
 * Seed initial permissions and role → permission mappings.
 *
 * Super admin and admin are treated as having all permissions in code,
 * but we still seed mappings for other roles.
 */
class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'name'  => 'inventory.manage',
                'label' => 'Manage inventory & stock movements',
                'group' => 'Inventory & stock',
                'roles' => ['warehouse_manager'],
            ],
            [
                'name'  => 'control.products',
                'label' => 'Manage products, materials, packaging & tax classes',
                'group' => 'Masters & control',
                'roles' => ['admin'],
            ],
            [
                'name'  => 'control.agents',
                'label' => 'Manage agents, pricing & commissions',
                'group' => 'Masters & control',
                'roles' => ['sales_manager'],
            ],
            [
                'name'  => 'control.suppliers',
                'label' => 'Manage suppliers & purchase masters',
                'group' => 'Masters & control',
                'roles' => ['admin'],
            ],
            [
                'name'  => 'control.warehouses',
                'label' => 'Manage warehouses, locations & fleet',
                'group' => 'Masters & control',
                'roles' => ['warehouse_manager'],
            ],
            [
                'name'  => 'control.employees',
                'label' => 'Manage employees, contracts & HR data',
                'group' => 'Masters & control',
                'roles' => ['admin'],
            ],
            [
                'name'  => 'manufacturing.manage',
                'label' => 'Manage production orders and runs',
                'group' => 'Production & QC',
                'roles' => ['production_manager'],
            ],
            [
                'name'  => 'manufacturing.qc',
                'label' => 'Approve production QC',
                'group' => 'Production & QC',
                'roles' => ['qc_officer'],
            ],
            [
                'name'  => 'sales.manage',
                'label' => 'Manage sales orders and returns',
                'group' => 'Sales & returns',
                'roles' => ['sales_manager'],
            ],
            [
                'name'  => 'accounting.manage',
                'label' => 'Manage finance, bills, expenses & accounts',
                'group' => 'Accounting & finance',
                'roles' => ['admin'],
            ],
            [
                'name'  => 'reports.view',
                'label' => 'View management reports',
                'group' => 'Reports & analytics',
                'roles' => ['admin'],
            ],
            [
                'name'  => 'roles.manage',
                'label' => 'Manage user roles',
                'group' => 'Access control',
                'roles' => ['admin'],
            ],
            [
                'name'  => 'permissions.manage',
                'label' => 'Manage permissions',
                'group' => 'Access control',
                'roles' => ['super_admin'],
            ],
            [
                'name'  => 'system.settings',
                'label' => 'Manage system configuration & settings',
                'group' => 'System',
                'roles' => ['admin'],
            ],
        ];

        foreach ($definitions as $def) {
            $permission = Permission::firstOrCreate(
                ['name' => $def['name']],
                [
                    'label' => $def['label'],
                    'group' => $def['group'],
                ]
            );

            // Sync role mappings for this permission
            RolePermission::where('permission_name', $permission->name)->delete();

            foreach ($def['roles'] as $role) {
                RolePermission::create([
                    'role'            => $role,
                    'permission_name' => $permission->name,
                ]);
            }
        }
    }
}
