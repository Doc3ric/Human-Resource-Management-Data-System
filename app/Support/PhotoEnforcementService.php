<?php

namespace App\Support;

use App\Models\Applicant;
use App\Models\PlantillaRecord;

/**
 * Module 7 — Photo Management. Scoring cannot be submitted until a photo is
 * attached (manual upload or mirrored from the 201-file, with confirmation).
 */
class PhotoEnforcementService
{
    public const BLOCK_MESSAGE = 'Scoring cannot be submitted until a photo is attached to this applicant\'s profile. Upload a photo to proceed.';

    public function hasValidPhoto(Applicant $applicant): bool
    {
        if (empty($applicant->photo_url)) {
            return false;
        }

        // A 201-file-imported photo additionally requires the reviewer's confirmation.
        if ($applicant->photo_source === '201_import' && !$applicant->photo_confirmed) {
            return false;
        }

        return true;
    }

    /** Best-effort match to an active 201-file (plantilla) profile picture by name. */
    public function findMirrorCandidate(Applicant $applicant): ?PlantillaRecord
    {
        return PlantillaRecord::filled()
            ->whereNotNull('profile_picture')
            ->where('last_name', $applicant->last_name)
            ->where('first_name', $applicant->first_name)
            ->first();
    }

    public function importFrom201File(Applicant $applicant, PlantillaRecord $record): void
    {
        $applicant->update([
            'photo_url' => $record->profile_picture,
            'photo_source' => '201_import',
            'photo_uploaded_at' => now(),
            'photo_confirmed' => false, // still needs the "I confirm..." checkbox
        ]);
    }

    public function confirmImportedPhoto(Applicant $applicant): void
    {
        $applicant->update(['photo_confirmed' => true]);
    }

    public function recordManualUpload(Applicant $applicant, string $photoUrl): void
    {
        $applicant->update([
            'photo_url' => $photoUrl,
            'photo_source' => 'manual_upload',
            'photo_uploaded_at' => now(),
            'photo_confirmed' => true, // manual uploads are self-evidently correct
        ]);
    }
}
