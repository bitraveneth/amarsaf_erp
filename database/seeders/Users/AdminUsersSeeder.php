<?php

namespace Database\Seeders\Users;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed core admin / system level users.
 */
class AdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Primary super admin – full control over roles & permissions
        User::firstOrCreate(
            ['email' => 'super@saferpv.local'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'role'     => 'super_admin',
            ]
        );

        // Normal admin account for day-to-day configuration
        User::firstOrCreate(
            ['email' => 'admin@saferpv.local'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );
    }
}
