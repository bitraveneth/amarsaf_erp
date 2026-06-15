<?php

namespace App\Console\Commands;

use App\Support\DatabaseBackupManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class BackupDatabase extends Command
{
    protected $signature = 'erp:backup-database {--no-prune : Keep all existing backups regardless of retention policy}';

    protected $description = 'Create a database SQL backup and prune old snapshots';

    public function handle(DatabaseBackupManager $backupManager): int
    {
        try {
            $filename = $backupManager->create();
            $this->info("Database backup created: {$filename}");
        } catch (RuntimeException|Throwable $exception) {
            $this->error('Database backup failed: ' . $exception->getMessage());
            Log::error('erp:backup-database failed', ['message' => $exception->getMessage()]);

            return self::FAILURE;
        }

        if ($this->option('no-prune')) {
            return self::SUCCESS;
        }

        $retentionDays = (int) config('database.backup.retention_days', 14);
        $result = $backupManager->prune($retentionDays);

        if ($result['count'] > 0) {
            $this->info('Pruned ' . $result['count'] . ' backup(s) older than ' . $retentionDays . ' days.');
        }

        return self::SUCCESS;
    }
}
