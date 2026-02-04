<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core master / demo data
        $this->call(DemoErpDataSeeder::class);

        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@saferpv.local'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Demo agent + agent user (agent record itself created in DemoErpDataSeeder)
        $agent = Agent::where('name', 'Dhaka North Dealer 01')->first();

        if ($agent) {
            User::firstOrCreate(
                ['email' => 'agent@saferpv.local'],
                [
                    'name' => 'Demo Agent User',
                    'password' => Hash::make('password'),
                    'role' => 'agent',
                    'agent_id' => $agent->id,
                ]
            );
        }

        // Demo employee + employee user
        $employee = Employee::firstOrCreate(
            ['name' => 'Field Sales Rep 01'],
            [
                'work_email' => 'employee@demo.local',
                'work_mobile' => '01712-000001',
                'department' => 'Sales & Marketing',
                'job_position' => 'Sales Representative',
                'work_zone' => 'Dhaka North',
            ]
        );

        User::firstOrCreate(
            ['email' => 'employee@saferpv.local'],
            [
                'name' => 'Demo Employee User',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'employee_id' => $employee->id,
            ]
        );

        // Warehouse / inventory manager + user
        $warehouseManager = Employee::firstOrCreate(
            ['name' => 'Warehouse Manager'],
            [
                'work_email' => 'warehouse@demo.local',
                'work_mobile' => '01712-000002',
                'department' => 'Inventory & Logistics',
                'job_position' => 'Warehouse Manager',
                'work_zone' => 'Factory / Central Depot',
            ]
        );

        User::firstOrCreate(
            ['email' => 'warehouse@saferpv.local'],
            [
                'name' => 'Demo Warehouse Manager',
                'password' => Hash::make('password'),
                'role' => 'warehouse_manager',
                'employee_id' => $warehouseManager->id,
            ]
        );
    }
}
