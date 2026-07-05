<?php

namespace App\Support\Leave;

use App\Models\LeaveApplication;
use App\Models\LeaveViolation;
use App\Models\LeaveViolationThreshold;
use App\Models\PlantillaRecord;

/**
 * Module 2B.1/2B.2 — detects leave-administration violations from data this
 * app actually has: leave_applications/leave_balances (Module 10-M2). There
 * is no DTR/attendance minute-level data source in this app, so tardiness/
 * undertime detection is NOT implemented here — only AWOL and habitual
 * absenteeism, both inferable from disapproved/unauthorized leave applications.
 * Habitual tardiness stays a manually-logged violation type (see controller).
 *
 * Module 2B.9-11 adds leave-FILING/procedure violations (late/improper
 * filing, missing supporting docs, disapproval notices) as their own
 * category, distinct from the attendance violations above — see
 * scanFilingViolations().
 */
class LeaveViolationDetectionService
{
    /** Omnibus Rules on Leave Sec. 63 — AWOL at 30+ continuous unauthorized calendar days. */
    public const AWOL_DAYS_THRESHOLD = 30;

    /** 2025 RACCS — habitual absenteeism: unauthorized absences > 2.5 days/month for 3+ months. */
    public const HABITUAL_ABSENTEEISM_MONTHLY_DAYS = 2.5;
    public const HABITUAL_ABSENTEEISM_MONTHS_REQUIRED = 3;

    /** Omnibus Rules on Leave Sec. 53 — default grace period if not seeded via leave_violation_thresholds. */
    public const DEFAULT_SICK_LEAVE_FILING_GRACE_DAYS = 1;

    public function scan(PlantillaRecord $record, string $ratingPeriod): array
    {
        if (LeaveGuardrail::isExcluded($record)) {
            return []; // JO/COS are not subject to leave-credit violations at all.
        }

        // 2B.9-11: filing/procedure violations apply regardless of whether the
        // application was ultimately disapproved, so this runs independently
        // of (and before) the disapproved-application gate below.
        $flags = $this->scanFilingViolations($record, $ratingPeriod);

        $unauthorized = LeaveApplication::where('plantilla_record_id', $record->id)
            ->where('status', 'disapproved')
            ->where('date_to', '<', now())
            ->get();

        if ($unauthorized->isEmpty()) {
            return $flags;
        }

        $totalUnauthorizedDays = (float) $unauthorized->sum('days_requested');

        // AWOL: a single continuous unauthorized stretch >= 30 days, OR
        // cumulative unauthorized days in this scan reaching that threshold.
        $longestStretch = (float) $unauthorized->max('days_requested');
        if ($longestStretch >= self::AWOL_DAYS_THRESHOLD || $totalUnauthorizedDays >= self::AWOL_DAYS_THRESHOLD) {
            $flags[] = $this->recordViolation($record, 'AWOL', $ratingPeriod, [
                'total_unauthorized_days' => $totalUnauthorizedDays,
                'longest_stretch_days' => $longestStretch,
                'applications' => $unauthorized->pluck('id'),
            ]);
        }

        // Habitual absenteeism: unauthorized days grouped by month, flag if
        // >2.5 days in 3+ distinct months.
        $byMonth = $unauthorized->groupBy(fn ($app) => $app->date_from->format('Y-m'))
            ->map(fn ($apps) => (float) $apps->sum('days_requested'));
        $qualifyingMonths = $byMonth->filter(fn ($days) => $days > self::HABITUAL_ABSENTEEISM_MONTHLY_DAYS);

        if ($qualifyingMonths->count() >= self::HABITUAL_ABSENTEEISM_MONTHS_REQUIRED) {
            $flags[] = $this->recordViolation($record, 'HABITUAL_ABSENTEEISM', $ratingPeriod, [
                'qualifying_months' => $qualifyingMonths->toArray(),
                'months_count' => $qualifyingMonths->count(),
            ]);
        }

        // LWOP Detection
        $balances = \App\Models\LeaveBalance::where('plantilla_record_id', $record->id)
            ->where('year', date('Y'))
            ->get();

        $totalLwopDays = 0;
        foreach ($balances as $balance) {
            if ($balance->used_days > $balance->earned_days) {
                $totalLwopDays += ($balance->used_days - $balance->earned_days);
            }
        }

        if ($totalLwopDays > 0) {
            $flags[] = $this->recordViolation($record, 'LWOP', $ratingPeriod, [
                'total_lwop_days' => $totalLwopDays,
                'sec_62_warning' => $totalLwopDays >= 300, // Near 1 year
            ]);
        }

        return $flags;
    }

    /**
     * Module 2B.10 — leave APPLICATION/FILING procedure violations: late
     * filing of sick leave (Sec. 53), late/improper filing of vacation leave
     * or other leave types (Sec. 51/54/56), missing supporting documents, and
     * disapproval notices. These are procedural violations of the FILING
     * itself, independent of the attendance-pattern violations above — a
     * late-filed application that still gets approved is still a filing
     * violation, so this runs over ALL applications, not just disapproved ones.
     */
    private function scanFilingViolations(PlantillaRecord $record, string $ratingPeriod): array
    {
        $flags = [];

        $applications = LeaveApplication::with('leaveType')
            ->where('plantilla_record_id', $record->id)
            ->whereNotNull('filed_at')
            ->get();

        foreach ($applications as $app) {
            $type = $app->leaveType;
            if (!$type) {
                continue;
            }

            if (strtoupper($type->code) === 'SL') {
                $threshold = LeaveViolationThreshold::where('violation_type', 'LATE_FILING_SICK_LEAVE')->first();
                $graceDays = (int) ($threshold->config['grace_days'] ?? self::DEFAULT_SICK_LEAVE_FILING_GRACE_DAYS);

                if ($app->filed_at->gt($app->date_to->copy()->addDays($graceDays))) {
                    $flags[] = $this->recordFilingViolation($record, 'LATE_FILING_SICK_LEAVE', $ratingPeriod, $app, [
                        'days_late' => $app->date_to->diffInDays($app->filed_at),
                        'filed_at' => $app->filed_at->toDateString(),
                        'date_to' => $app->date_to->toDateString(),
                    ]);
                }

                // Sec. 53 — sick leave beyond the medical-certificate threshold
                // with no supporting document attached at all (this app can't
                // distinguish a medical certificate from an affidavit-in-lieu;
                // it only knows whether ANY document was attached).
                $certThreshold = (float) ($type->requires_medical_cert_over_days ?? 0);
                if ($certThreshold > 0 && (float) $app->days_requested > $certThreshold && !$app->document_id) {
                    $flags[] = $this->recordFilingViolation($record, 'MISSING_SUPPORTING_DOCS', $ratingPeriod, $app, [
                        'days_requested' => (float) $app->days_requested,
                        'cert_threshold_days' => $certThreshold,
                    ]);
                }
            } elseif ($type->advance_notice_days) {
                // Sec. 51 (vacation leave) and the prior-filing window for
                // other leave types (e.g. SPL) both come from the same
                // leave_types.advance_notice_days config — single source.
                $requiredBy = $app->date_from->copy()->subDays($type->advance_notice_days);
                if ($app->filed_at->gt($requiredBy)) {
                    $violationType = strtoupper($type->code) === 'VL'
                        ? 'LATE_FILING_VACATION_LEAVE'
                        : 'LATE_FILING_OTHER_LEAVE';

                    $flags[] = $this->recordFilingViolation($record, $violationType, $ratingPeriod, $app, [
                        'leave_type' => $type->name,
                        'advance_notice_days_required' => $type->advance_notice_days,
                        'days_short' => $requiredBy->diffInDays($app->filed_at),
                        'filed_at' => $app->filed_at->toDateString(),
                        'date_from' => $app->date_from->toDateString(),
                    ]);
                }
            }

            // Consequence logic (2B.10): a disapproved application gets its own
            // Notice of Disapproval (2B.11-b). The resulting unauthorized/
            // without-pay absence is counted toward AWOL/Habitual Absenteeism
            // exactly once, by the existing disapproved-application scan in
            // scan() above (which reads the same status='disapproved' rows) —
            // this flag does not add a second count there.
            if ($app->status === 'disapproved') {
                $flags[] = $this->recordFilingViolation($record, 'LEAVE_DISAPPROVED', $ratingPeriod, $app, [
                    'disapproval_reason' => $app->disapproval_reason,
                    'date_from' => $app->date_from->toDateString(),
                    'date_to' => $app->date_to->toDateString(),
                    'contributes_to_absenteeism_tracking' => true,
                ]);
            }
        }

        return array_values(array_filter($flags));
    }

    /**
     * Filing violations are keyed per leave APPLICATION (not per rating
     * period like recordViolation()) — an employee can have several distinct
     * late filings in the same period, each its own procedural infraction.
     */
    private function recordFilingViolation(PlantillaRecord $record, string $type, string $ratingPeriod, LeaveApplication $application, array $details): ?LeaveViolation
    {
        $exists = LeaveViolation::where('plantilla_record_id', $record->id)
            ->where('violation_type', $type)
            ->where('details->leave_application_id', $application->id)
            ->exists();

        if ($exists) {
            return null;
        }

        $offenseTier = LeaveViolation::where('plantilla_record_id', $record->id)
            ->where('violation_type', $type)
            ->where('status', '!=', 'detected')
            ->count() + 1;

        return LeaveViolation::create([
            'plantilla_record_id' => $record->id,
            'violation_type' => $type,
            'rating_period' => $ratingPeriod,
            'offense_tier' => $offenseTier,
            'details' => array_merge($details, ['leave_application_id' => $application->id]),
            'status' => 'detected',
        ]);
    }

    private function recordViolation(PlantillaRecord $record, string $type, string $ratingPeriod, array $details): LeaveViolation
    {
        $offenseTier = LeaveViolation::where('plantilla_record_id', $record->id)
            ->where('violation_type', $type)
            ->where('status', '!=', 'detected') // only count already-issued prior offenses
            ->count() + 1;

        return LeaveViolation::firstOrCreate(
            [
                'plantilla_record_id' => $record->id,
                'violation_type' => $type,
                'rating_period' => $ratingPeriod,
            ],
            [
                'offense_tier' => $offenseTier,
                'details' => $details,
                'status' => 'detected',
            ]
        );
    }
}
