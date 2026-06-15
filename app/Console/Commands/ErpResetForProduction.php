<?php

namespace App\Console\Commands;

use App\Support\DatabaseBackupManager;
use Database\Seeders\ProductionSeeder;
use Illuminate\Console\Command;

class ErpResetForProduction extends Command
{
    protected $signature = 'erp:reset-for-production {--force : Confirm destructive database reset}';

    protected $description = 'Drop all tables, re-seed config-only data, and preserve SQL backup files on disk';

    public function handle(DatabaseBackupManager $backupManager): int
    {
        if (! $this->option('force')) {
            $this->error('This drops every table and re-seeds ProductionSeeder. SQL files in Admin → Settings → Database are kept. Re-run with --force.');

            return self::FAILURE;
        }

        if (app()->environment('production') && ! config('app.allow_production_wipe', false)) {
            $this->error('Refusing to reset production. Set ALLOW_PRODUCTION_WIPE=true to override.');

            return self::FAILURE;
        }

        $backups = $backupManager->list();
        $this->info(count($backups) . ' SQL backup file(s) in storage/app/backups/database will be preserved.');

        if (! $this->confirm('Run migrate:fresh with ProductionSeeder now?')) {
            $this->comment('Reset cancelled.');

            return self::SUCCESS;
        }

        $this->call('migrate:fresh', [
            '--force' => true,
            '--seeder' => ProductionSeeder::class,
        ]);

        $remaining = $backupManager->list();
        $this->info('Database reset complete. ' . count($remaining) . ' backup file(s) still available in the Database settings tab.');

        return self::SUCCESS;
    }
}
