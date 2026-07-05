<?php

namespace App\Support;

use App\Models\Applicant;

/**
 * Module 6.6 — re-codes an applicant as birthdate(DDMMYY)+"&"+item number for
 * deliberation/scoring displays, so raw names never appear in scoring tables.
 */
class BlindScoringId
{
    public static function forApplicant(Applicant $applicant): string
    {
        $dob = $applicant->date_of_birth ? $applicant->date_of_birth->format('dmy') : '000000';
        $itemNo = $applicant->item_no ?: 'NOITEM';

        return "{$dob}&{$itemNo}";
    }
}
