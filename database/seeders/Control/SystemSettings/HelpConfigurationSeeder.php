<?php

namespace Database\Seeders\Control\SystemSettings;

use App\Models\MenuGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seed data for:
 * - Help & configuration guide metadata.
 */
class HelpConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        // For now we just ensure the Help page has some
        // basic section metadata stored in the menu_groups
        // table so the sidebar and help screen feel connected.

        // If a "System settings" group exists, attach a simple
        // JSON payload of tips in its meta column (if present).
        if (! Schema::hasColumn('menu_groups', 'meta')) {
            return;
        }

        $group = MenuGroup::where('title', 'System settings')->first();

        if (! $group) {
            return;
        }

        $group->meta = json_encode([
            'help_sections' => [
                [
                    'key'   => 'getting-started',
                    'title' => 'Getting started',
                    'items' => [
                        'How to log in as super admin',
                        'How to add employees and assign roles',
                        'How to configure warehouses and opening stock',
                    ],
                ],
                [
                    'key'   => 'modules',
                    'title' => 'Modules overview',
                    'items' => [
                        'Control (masters & settings)',
                        'Manufacturing and QC',
                        'Inventory and stock movements',
                        'Sales orders and deliveries',
                        'Accounting and reports',
                    ],
                ],
                [
                    'key'   => 'support',
                    'title' => 'Support',
                    'items' => [
                        'Contact your implementation partner for live setup',
                        'Use this Help screen as a quick reference',
                    ],
                ],
            ],
        ]);

        $group->save();
    }
}
