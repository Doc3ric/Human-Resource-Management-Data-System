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

    public function findCurrentMasterRecord(Applicant $applicant): ?PlantillaRecord
    {
        if ($applicant->item_no) {
            $query = PlantillaRecord::where('item_no_new', $applicant->item_no);
            
            // If the applicant explicitly specified an office, ensure the item belongs to it.
            if ($applicant->office) {
                $query->where('office_department', $applicant->office);
            }
            
            $byItem = $query->first();
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
            // Only snapshot the salary grade for future validation.
            // Do NOT auto-populate or overwrite the user's explicit office or item_no.
            $applicant->salary_grade_snapshot = $current->salary_grade;
        }
    }
}
