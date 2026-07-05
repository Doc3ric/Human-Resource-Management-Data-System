<?php

namespace App\Support\Leave;

use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\PlantillaRecord;
use App\Models\User;

/**
 * Module 10-M2 — files a leave application after balance validation.
 * The JO/COS guardrail applies before anything else (Module 10 cross-cutting rule).
 */
class LeaveApplicationService
{
    /** @throws \RuntimeException on the JO/COS guardrail or insufficient balance. */
    public function file(
        PlantillaRecord $record,
        LeaveType $type,
        string $dateFrom,
        string $dateTo,
        float $daysRequested,
        string $filedAt,
        User $filedBy,
        ?string $reason = null,
        ?int $documentId = null,
    ): LeaveApplication {
        LeaveGuardrail::assertNotExcluded($record);

        $year = (int) date('Y', strtotime($dateFrom));
        $balance = LeaveBalance::where('plantilla_record_id', $record->id)
            ->where('leave_type_id', $type->id)
            ->where('year', $year)
            ->first();

        $remaining = $balance ? ((float) $balance->earned_days - (float) $balance->used_days) : 0.0;

        if ($daysRequested > $remaining) {
            throw new \RuntimeException("Insufficient {$type->code} balance: requesting {$daysRequested} day(s), only {$remaining} available for {$year}.");
        }

        return LeaveApplication::create([
            'plantilla_record_id' => $record->id,
            'leave_type_id' => $type->id,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'days_requested' => $daysRequested,
            'filed_at' => $filedAt,
            'status' => 'pending',
            'reason' => $reason,
            'document_id' => $documentId,
            'filed_by' => $filedBy->id,
        ]);
    }

    /** Approving a leave deducts the balance; disapproving does not. */
    public function approve(LeaveApplication $application, User $actor): void
    {
        $application->update(['status' => 'approved', 'acted_by' => $actor->id]);

        $balance = LeaveBalance::firstOrCreate(
            [
                'plantilla_record_id' => $application->plantilla_record_id,
                'leave_type_id' => $application->leave_type_id,
                'year' => (int) $application->date_from->format('Y'),
            ],
            ['earned_days' => 0, 'used_days' => 0]
        );
        $balance->increment('used_days', (float) $application->days_requested);
    }

    public function disapprove(LeaveApplication $application, User $actor, string $reason): void
    {
        $application->update(['status' => 'disapproved', 'disapproval_reason' => $reason, 'acted_by' => $actor->id]);
    }
}
