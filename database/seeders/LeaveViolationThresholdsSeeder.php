<?php

namespace Database\Seeders;

use App\Models\LeaveViolationThreshold;
use Illuminate\Database\Seeder;

/**
 * Module 2B.2/2B.10 — admin-configurable thresholds, seeded with the current
 * law. VL/SPL late-filing and the sick-leave medical-certificate deficiency
 * both reuse leave_types.advance_notice_days / requires_medical_cert_over_days
 * (already the single source for those rules) — only the sick-leave
 * "filed immediately upon return" grace period needs a new configurable value.
 */
class LeaveViolationThresholdsSeeder extends Seeder
{
    public function run(): void
    {
        LeaveViolationThreshold::updateOrCreate(
            ['violation_type' => 'LATE_FILING_SICK_LEAVE'],
            [
                'config' => ['grace_days' => 1],
                'legal_basis' => 'Omnibus Rules on Leave (CSC MC No. 41 s.1998), Sec. 53 — sick leave application must be filed immediately upon the employee\'s return from leave.',
            ]
        );
    }
}
