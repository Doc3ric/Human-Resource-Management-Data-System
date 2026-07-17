<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('retirement:process')->daily();
Schedule::command('step-increments:process')->daily();
Schedule::command('hrdms:scan-policies')->dailyAt('08:00');
// Off-site driver is intentionally 'local' for now — the configured Google service
// account cannot hold file storage (Google rejects uploads with "Service Accounts
// do not have storage quota"; this needs a paid Workspace + Shared Drive, or a
// different off-site target). See docs/DISASTER_RECOVERY.md before changing to
// --driver=google_drive.
Schedule::command('backup:database --keep=14 --driver=local')->cron('0 */6 * * *')->withoutOverlapping();
// File-storage (IDCC documents/reports) changes far less often than the DB and is much
// larger to archive, so it runs once daily rather than every 6 hours.
Schedule::command('backup:files --keep=5 --driver=local')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('recruitment:advance-lifecycle')->daily();
Schedule::command('detail-orders:check-status')->daily();
Schedule::command('vppm:check-publication-status')->daily();
