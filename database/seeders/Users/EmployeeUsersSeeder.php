<?php

namespace Database\Seeders\Users;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed login users that are linked to employee profiles.
 */
class EmployeeUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Warehouse Manager login
        $warehouseManager = Employee::where('name', 'Warehouse Manager')->first();

        if ($warehouseManager) {
            User::firstOrCreate(
                ['email' => 'warehouse@saferpv.local'],
                [
                    'name'        => 'Demo Warehouse Manager',
                    'password'    => Hash::make('password'),
                    'role'        => 'warehouse_manager',
                    'employee_id' => $warehouseManager->id,
                ]
            );
        }

        // Production Manager login
        $productionManager = Employee::where('name', 'Production Manager')->first();

        if ($productionManager) {
            User::firstOrCreate(
                ['email' => 'production@saferpv.local'],
                [
                    'name'        => 'Demo Production Manager',
                    'password'    => Hash::make('password'),
                    'role'        => 'production_manager',
                    'employee_id' => $productionManager->id,
                ]
            );
        }

        // Field Sales Rep login
        $salesRep01 = Employee::where('name', 'Field Sales Rep 01')->first();

        if ($salesRep01) {
            User::firstOrCreate(
                ['email' => 'employee@saferpv.local'],
                [
                    'name'        => 'Demo Sales Rep',
                    'password'    => Hash::make('password'),
                    'role'        => 'employee',
                    'employee_id' => $salesRep01->id,
                ]
            );
        }

        // QC Officer login
        $qcOfficer = Employee::where('name', 'QC Officer')->first();

        if ($qcOfficer) {
            User::firstOrCreate(
                ['email' => 'qc@saferpv.local'],
                [
                    'name'        => 'Demo QC Officer',
                    'password'    => Hash::make('password'),
                    'role'        => 'qc_officer',
                    'employee_id' => $qcOfficer->id,
                ]
            );
        }
    }
}
