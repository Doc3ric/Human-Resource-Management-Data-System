<?php

namespace App\Console\Commands;

use App\Support\Recruitment\RecruitmentLifecycleService;
use Illuminate\Console\Command;

/** Module 2.2 — daily stage-flip cron for the recruitment-record lifecycle. */
class AdvanceRecruitmentLifecycle extends Command
{
    protected $signature = 'recruitment:advance-lifecycle';

    protected $description = 'Recompute each recruitment record\'s lifecycle stage (Active/Archived/Valueless Holding Queue) — never deletes, only re-stages.';

    public function handle(RecruitmentLifecycleService $service): int
    {
        $changed = $service->advanceAll();
        $this->info("Recruitment lifecycle: {$changed} record(s) moved to a new stage.");

        return self::SUCCESS;
    }
}
