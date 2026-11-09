<?php

namespace Database\Seeders\Users;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Production login accounts only — no employees, agents, or transactional demo data.
 *
 * Super Admin password: SEED_USER_PASSWORD (required when APP_ENV=production).
 * Other roles use fixed simple passwords defined below (change after first login).
 */
class ProductionUsersSeeder extends Seeder
{
    public function run(): void
    {
        $superPassword = (string) env('SEED_USER_PASSWORD', '');

        if (app()->environment('production') && $superPassword === '') {
            $this->command?->warn('SEED_USER_PASSWORD is empty — skipping production users.');

            return;
        }

        $superPassword = $superPassword !== '' ? $superPassword : 'password';

        $accounts = [
            [
                'email' => 'super@saferpv.local',
                'name' => 'Super Admin',
                'role' => 'super_admin',
                'password' => $superPassword,
            ],
            [
                'email' => 'admin@saferpv.local',
                'name' => 'Admin User',
                'role' => 'admin',
                'password' => 'OceanWave92',
            ],
            [
                'email' => 'accounts@saferpv.local',
                'name' => 'Accounts Officer',
                'role' => 'accounts_officer',
                'password' => 'LedgerFox47',
            ],
            [
                'email' => 'purchase@saferpv.local',
                'name' => 'Purchase Executive',
                'role' => 'purchase_executive',
                'password' => 'CargoMint31',
            ],
            [
                'email' => 'warehouse@saferpv.local',
                'name' => 'Warehouse Officer',
                'role' => 'warehouse_officer',
                'password' => 'ShelfBlue28',
            ],
            [
                'email' => 'production@saferpv.local',
                'name' => 'Production Officer',
                'role' => 'production_officer',
                'password' => 'ForgeSun64',
            ],
            [
                'email' => 'employee@saferpv.local',
                'name' => 'Sales Officer',
                'role' => 'sales_officer',
                'password' => 'TrailGold55',
            ],
            [
                'email' => 'sales.manager@saferpv.local',
                'name' => 'Delivery Coordinator',
                'role' => 'delivery_coordinator',
                'password' => 'RouteMaple73',
            ],
            [
                'email' => 'logistics@saferpv.local',
                'name' => 'Logistics Coordinator',
                'role' => 'delivery_coordinator',
                'password' => 'FleetAmber41',
            ],
            [
                'email' => 'qc@saferpv.local',
                'name' => 'QC Officer',
                'role' => 'qc_officer',
                'password' => 'CheckNova86',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                    'employee_id' => null,
                ]
            );
            $this->syncPrimaryRole($user);
        }
    }

    protected function syncPrimaryRole(User $user): void
    {
        if (! Schema::hasTable('user_roles') || empty($user->role)) {
            return;
        }

        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $user->id, 'role_key' => $user->role],
            ['updated_at' => now(), 'created_at' => now()]
        );
    }
}
