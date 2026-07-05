<?php

namespace App\Support\Recruitment;

use App\Models\Applicant;
use Carbon\Carbon;

/**
 * Module 2.2/2.3 — the three-stage recruitment-record lifecycle.
 * Stage A ACTIVE: 0-1yr since applied_at, visible/modifiable by default.
 * Stage B ARCHIVED: 1-2yr since applied_at, hidden from the default view
 *   but searchable, with an on-open banner.
 * Stage C VALUELESS_QUEUE: the vacancy was filled (is_filled) and it has
 *   been 2+ years (730 days) since date_filled — R.A. 9470's "keep 2 years
 *   after filling" rule. Anti-auto-delete: this stage only queues records
 *   for the Disposal View; nothing here ever deletes anything.
 */
class RecruitmentLifecycleService
{
    public const STAGE_ACTIVE = 'ACTIVE';
    public const STAGE_ARCHIVED = 'ARCHIVED';
    public const STAGE_VALUELESS_QUEUE = 'VALUELESS_QUEUE';

    public const ARCHIVE_AFTER_DAYS = 365;
    public const VALUELESS_QUEUE_AFTER_FILLED_DAYS = 730;

    public function computeStage(Applicant $applicant): string
    {
        if ($applicant->is_filled && $applicant->date_filled) {
            $daysSinceFilled = Carbon::parse($applicant->date_filled)->diffInDays(now());
            if ($daysSinceFilled >= self::VALUELESS_QUEUE_AFTER_FILLED_DAYS) {
                return self::STAGE_VALUELESS_QUEUE;
            }
        }

        $reference = $applicant->applied_at ?? $applicant->created_at;
        if ($reference && Carbon::parse($reference)->diffInDays(now()) >= self::ARCHIVE_AFTER_DAYS) {
            return self::STAGE_ARCHIVED;
        }

        return self::STAGE_ACTIVE;
    }

    /**
     * Recompute and persist the stage for every applicant whose stage no
     * longer matches what computeStage() would return. Never deletes —
     * query-level staging only, exactly like Module 1B.2's live-report
     * exclusion.
     *
     * @return int number of records whose stage changed
     */
    public function advanceAll(): int
    {
        $changed = 0;

        Applicant::query()->chunkById(200, function ($applicants) use (&$changed) {
            foreach ($applicants as $applicant) {
                $newStage = $this->computeStage($applicant);
                if ($newStage !== $applicant->lifecycle_stage) {
                    $applicant->forceFill(['lifecycle_stage' => $newStage])->save();
                    $changed++;
                }
            }
        });

        return $changed;
    }
}
