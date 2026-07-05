<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=14 : Number of successful local backups to retain}';

    protected $description = 'Create an encrypted database backup and prune old backups beyond the retention count';

    public function handle(DatabaseBackupService $backupService): int
    {
        try {
            $log = $backupService->run('scheduled', null, 'local');
            $this->info("Backup created: {$log->filename} ({$log->formatted_size})");
        } catch (\Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $pruned = $backupService->pruneOldBackups((int) $this->option('keep'));
        if ($pruned > 0) {
            $this->info("Pruned {$pruned} old backup file(s) beyond retention.");
        }

        return self::SUCCESS;
    }
}
