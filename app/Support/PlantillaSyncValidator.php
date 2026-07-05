<?php

namespace App\Support;

use App\Models\Applicant;
use App\Models\PlantillaRecord;

/**
 * Module 3.2 — Master Plantilla validation lock. Compares an applicant's
 * snapshotted position descriptors against the CURRENT Master Plantilla
 * record before recruitment actions proceed.
 */
class PlantillaSyncValidator
{
    public const MISMATCH_MESSAGE = 'Plantilla Mismatch Block: The structural specifications for this position have changed in the Master Plantilla database. Recruitment actions are locked until the active record is synchronized with current plantilla attributes.';

    /** Find the current Master Plantilla row this applicant's position maps to. */
    public function findCurrentMasterRecord(Applicant $applicant): ?PlantillaRecord
    {
        if ($applicant->item_no) {
            $byItem = PlantillaRecord::where('item_no_new', $applicant->item_no)->first();
            if ($byItem) {
                return $byItem;
            }
        }

        return PlantillaRecord::where('position_title', $applicant->position_applied)
            ->where('office_department', $applicant->office)
            ->first();
    }

    /** @return string|null the block message if mismatched, null if in sync (or nothing to compare against). */
    public function checkSync(Applicant $applicant): ?string
    {
        $current = $this->findCurrentMasterRecord($applicant);
        if (!$current) {
            return null; // Nothing in the master plantilla to compare against — not this validator's concern.
        }

        $mismatched = ($applicant->salary_grade_snapshot !== null && $applicant->salary_grade_snapshot != $current->salary_grade)
            || ($applicant->office !== null && $applicant->office !== $current->office_department)
            || ($applicant->item_no !== null && $applicant->item_no !== $current->item_no_new);

        return $mismatched ? self::MISMATCH_MESSAGE : null;
    }

    /** Snapshot the current master values onto the applicant (call at creation / on explicit re-sync). */
    public function snapshot(Applicant $applicant): void
    {
        $current = $this->findCurrentMasterRecord($applicant);
        if ($current) {
            $applicant->item_no = $current->item_no_new ?: $applicant->item_no;
            $applicant->office = $current->office_department ?: $applicant->office;
            $applicant->salary_grade_snapshot = $current->salary_grade;
        }
    }
}
