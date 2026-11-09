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
 * Password comes from SEED_USER_PASSWORD (required when APP_ENV=production).
 */
class ProductionUsersSeeder extends Seeder
{
    public function run(): void
    {
        $seedPassword = (string) env('SEED_USER_PASSWORD', '');

        if (app()->environment('production') && $seedPassword === '') {
            $this->command?->warn('SEED_USER_PASSWORD is empty — skipping production users.');

            return;
        }

        $seedPassword = $seedPassword !== '' ? $seedPassword : 'password';

        $accounts = [
            ['email' => 'super@saferpv.local', 'name' => 'Super Admin', 'role' => 'super_admin'],
            ['email' => 'admin@saferpv.local', 'name' => 'Admin User', 'role' => 'admin'],
            ['email' => 'accounts@saferpv.local', 'name' => 'Accounts Officer', 'role' => 'accounts_officer'],
            ['email' => 'purchase@saferpv.local', 'name' => 'Purchase Executive', 'role' => 'purchase_executive'],
            ['email' => 'warehouse@saferpv.local', 'name' => 'Warehouse Officer', 'role' => 'warehouse_officer'],
            ['email' => 'production@saferpv.local', 'name' => 'Production Officer', 'role' => 'production_officer'],
            ['email' => 'employee@saferpv.local', 'name' => 'Sales Officer', 'role' => 'sales_officer'],
            ['email' => 'sales.manager@saferpv.local', 'name' => 'Delivery Coordinator', 'role' => 'delivery_coordinator'],
            ['email' => 'logistics@saferpv.local', 'name' => 'Logistics Coordinator', 'role' => 'delivery_coordinator'],
            ['email' => 'qc@saferpv.local', 'name' => 'QC Officer', 'role' => 'qc_officer'],
        ];

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($seedPassword),
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
