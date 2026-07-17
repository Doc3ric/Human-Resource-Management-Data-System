<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupFiles extends Command
{
    protected $signature = 'backup:files
        {--keep=5 : Number of successful local archives to retain}
        {--driver=local : Storage destination — local, or google_drive (also keeps a local copy) once a working off-site target is configured — see docs/DISASTER_RECOVERY.md}';

    protected $description = 'Archive and encrypt IDCC documents/reports (not covered by the database dump), upload off-site, and prune old archives';

    public function handle(DatabaseBackupService $backupService): int
    {
        $driver = $this->option('driver');

        try {
            $log = $backupService->runFiles('scheduled', null, $driver);
            $this->info("File-storage backup created: {$log->filename} ({$log->formatted_size}) via {$driver}");
        } catch (\Throwable $e) {
            $this->error('File-storage backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $pruned = $backupService->pruneOldBackups((int) $this->option('keep'), 'files');
        if ($pruned > 0) {
            $this->info("Pruned {$pruned} old file-storage archive(s) beyond retention.");
        }

        return self::SUCCESS;
    }
}
