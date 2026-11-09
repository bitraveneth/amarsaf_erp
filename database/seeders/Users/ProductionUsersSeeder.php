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
 * Passwords come from .env only (never commit real passwords):
 * - SEED_USER_PASSWORD → super admin
 * - SEED_PASSWORD_ADMIN, SEED_PASSWORD_ACCOUNTS, … → other roles
 */
class ProductionUsersSeeder extends Seeder
{
    public function run(): void
    {
        $superPassword = (string) env('SEED_USER_PASSWORD', '');

        if ($superPassword === '') {
            $this->command?->warn('SEED_USER_PASSWORD is empty — skipping production users.');

            return;
        }

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
                'password' => (string) env('SEED_PASSWORD_ADMIN', ''),
            ],
            [
                'email' => 'accounts@saferpv.local',
                'name' => 'Accounts Officer',
                'role' => 'accounts_officer',
                'password' => (string) env('SEED_PASSWORD_ACCOUNTS', ''),
            ],
            [
                'email' => 'purchase@saferpv.local',
                'name' => 'Purchase Executive',
                'role' => 'purchase_executive',
                'password' => (string) env('SEED_PASSWORD_PURCHASE', ''),
            ],
            [
                'email' => 'warehouse@saferpv.local',
                'name' => 'Warehouse Officer',
                'role' => 'warehouse_officer',
                'password' => (string) env('SEED_PASSWORD_WAREHOUSE', ''),
            ],
            [
                'email' => 'production@saferpv.local',
                'name' => 'Production Officer',
                'role' => 'production_officer',
                'password' => (string) env('SEED_PASSWORD_PRODUCTION', ''),
            ],
            [
                'email' => 'employee@saferpv.local',
                'name' => 'Sales Officer',
                'role' => 'sales_officer',
                'password' => (string) env('SEED_PASSWORD_SALES', ''),
            ],
            [
                'email' => 'sales.manager@saferpv.local',
                'name' => 'Delivery Coordinator',
                'role' => 'delivery_coordinator',
                'password' => (string) env('SEED_PASSWORD_DELIVERY', ''),
            ],
            [
                'email' => 'logistics@saferpv.local',
                'name' => 'Logistics Coordinator',
                'role' => 'delivery_coordinator',
                'password' => (string) env('SEED_PASSWORD_LOGISTICS', ''),
            ],
            [
                'email' => 'qc@saferpv.local',
                'name' => 'QC Officer',
                'role' => 'qc_officer',
                'password' => (string) env('SEED_PASSWORD_QC', ''),
            ],
        ];

        foreach ($accounts as $account) {
            if ($account['password'] === '') {
                $this->command?->warn("Skipping {$account['email']} — password env is empty.");

                continue;
            }

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
