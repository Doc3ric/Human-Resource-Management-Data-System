<?php

namespace App\Support\Violations;

use App\Models\DisciplinaryCase;
use App\Models\Violation;
use App\Models\ViolationStatusLog;
use App\Support\Notifications\HrmoNotifier;

/**
 * Enhancement Spec Sec. 4 — Violation -> 201-File / Administrative Case
 * Linkage. Called explicitly by the Leave and Incident Report modules
 * (via their model observers — see EventServiceProvider) at the moment a
 * violation is confirmed, not on first draft/detection.
 */
class ViolationLinkageService
{
    public function __construct(private readonly HrmoNotifier $notifier)
    {
    }

    public function recordViolation(
        string $sourceModule,
        int $sourceRecordId,
        int $plantillaRecordId,
        string $violationType,
        string $legalBasis,
        string $severityLevel,
    ): Violation {
        $violation = Violation::create([
            'plantilla_record_id' => $plantillaRecordId,
            'source_module' => $sourceModule,
            'source_record_id' => $sourceRecordId,
            'violation_type' => $violationType,
            'legal_basis' => $legalBasis,
            'severity_level' => $severityLevel,
            'date_created' => now(),
        ]);

        $this->logStatus($violation, 'Recorded');

        // Incident Reports already have their own human-gated formal-charge
        // flow (DisciplinaryCaseController::store) — auto-spawning here would
        // create a duplicate case alongside whatever staff file manually.
        // Leave violations have no equivalent existing flow, so this is the
        // only place a grave/repeat leave violation turns into a case.
        if ($sourceModule === 'Leave'
            && ($severityLevel === 'Grave' || $this->isRepeatOffense($plantillaRecordId, $violationType, $violation->violation_id))
        ) {
            $this->spawnAdminCase($violation);
        }

        return $violation;
    }

    /** The Violation ledger row already recorded for a given source record, if any — used to backfill originating_violation_id when a case is created manually. */
    public function findForSource(string $sourceModule, int $sourceRecordId): ?Violation
    {
        return Violation::where('source_module', $sourceModule)
            ->where('source_record_id', $sourceRecordId)
            ->latest('violation_id')
            ->first();
    }

    public function isRepeatOffense(int $plantillaRecordId, string $violationType, ?int $excludeViolationId = null): bool
    {
        return Violation::where('plantilla_record_id', $plantillaRecordId)
            ->where('violation_type', $violationType)
            ->when($excludeViolationId, fn ($q) => $q->where('violation_id', '!=', $excludeViolationId))
            ->exists();
    }

    public function spawnAdminCase(Violation $violation): DisciplinaryCase
    {
        $case = DisciplinaryCase::create([
            'case_no' => 'AC-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6)),
            'personnel_id' => $violation->plantilla_record_id,
            'originating_violation_id' => $violation->violation_id,
            'formal_charge' => "Auto-spawned from {$violation->source_module} violation: {$violation->violation_type}.",
            'offense_classification' => $violation->severity_level === 'Grave' ? 'grave' : 'light',
            'status' => 'registered',
        ]);

        $this->logStatus($violation, 'Escalated', "Admin Case #{$case->case_no} opened.");

        $this->notifier->notifyDivisionHead(
            'New Administrative Case Opened',
            "Admin case {$case->case_no} was auto-opened for {$violation->plantillaRecord->full_name} ({$violation->violation_type}, {$violation->severity_level})."
        );

        return $case;
    }

    private function logStatus(Violation $violation, string $status, ?string $remarks = null): void
    {
        ViolationStatusLog::create([
            'violation_id' => $violation->violation_id,
            'status' => $status,
            'date_logged' => now(),
            'remarks' => $remarks,
        ]);
    }
}
