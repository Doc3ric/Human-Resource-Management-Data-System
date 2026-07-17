<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database
        {--keep=14 : Number of successful local backups to retain}
        {--driver=local : Storage destination — local, or google_drive (also keeps a local copy) once a working off-site target is configured — see docs/DISASTER_RECOVERY.md}';

    protected $description = 'Create an encrypted database backup and prune old backups beyond the retention count';

    public function handle(DatabaseBackupService $backupService): int
    {
        $driver = $this->option('driver');

        try {
            $log = $backupService->run('scheduled', null, $driver);
            $this->info("Backup created: {$log->filename} ({$log->formatted_size}) via {$driver}");
        } catch (\Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $pruned = $backupService->pruneOldBackups((int) $this->option('keep'), 'database');
        if ($pruned > 0) {
            $this->info("Pruned {$pruned} old backup file(s) beyond retention.");
        }

        return self::SUCCESS;
    }
}
