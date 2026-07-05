<?php

namespace App\Support\Renewal;

use App\Models\ActivityLog;
use App\Models\ContractRenewal;
use App\Models\PlantillaRecord;
use App\Models\RenewalSignal;
use App\Models\User;

/**
 * Module 1A.6 — the ONE shared commit path. Every launch point (Batch
 * Renewal, a future Performance Management "process renewals" button,
 * Records' own entry point) must call commitOne() rather than writing
 * is_renewed directly. What differs per launch point is the filter/framing;
 * the commit path, guardrails, and audit trail are identical everywhere.
 *
 * Module 1A.7 — gated to renewal_commit authority (checked by the caller
 * via RenewalAuthority::assertCanCommit() before invoking this).
 */
class RenewalCommitService
{
    public function __construct(private readonly RenewalSignalService $signals)
    {
    }

    /**
     * @throws \RuntimeException on any guardrail failure — caller decides
     *         how to report it (this service never silently partial-commits
     *         a single record).
     */
    public function commitOne(
        PlantillaRecord $record,
        string $contractStartDate,
        string $contractEndDate,
        ?float $rate,
        ?string $rateType,
        User $actor,
        string $ratingPeriod,
        ?string $notes = null,
    ): ContractRenewal {
        if ($record->is_vacant) {
            throw new \RuntimeException('Record is vacant — cannot renew.');
        }

        $duplicate = ContractRenewal::where('plantilla_record_id', $record->id)
            ->where(function ($q) use ($contractStartDate, $contractEndDate) {
                $q->whereBetween('contract_start_date', [$contractStartDate, $contractEndDate])
                  ->orWhereBetween('contract_end_date', [$contractStartDate, $contractEndDate])
                  ->orWhere(fn ($q2) => $q2
                      ->where('contract_start_date', '<=', $contractStartDate)
                      ->where('contract_end_date', '>=', $contractEndDate));
            })->exists();

        if ($duplicate) {
            throw new \RuntimeException('A renewal already exists covering this period.');
        }

        $renewal = ContractRenewal::create([
            'plantilla_record_id' => $record->id,
            'contract_start_date' => $contractStartDate,
            'contract_end_date' => $contractEndDate,
            'rate' => $rate,
            'rate_type' => $rateType,
            'renewed_by' => $actor->id,
            'notes' => $notes,
        ]);

        // This IS the authoritative act — document it as a signal too, so
        // the signal history for this record stays complete.
        $this->signals->authoritative(
            $record,
            $ratingPeriod,
            'RECRUITMENT',
            'CONTRACT_UPLOADED',
            $actor,
            'Committed via RenewalCommitService'
        );

        $record->forceFill([
            'is_renewed' => true,
            'renewal_period' => $ratingPeriod,
        ])->save();

        ActivityLog::create([
            'user_id' => $actor->id,
            'action' => 'Committed Renewal',
            'description' => "Committed renewal for {$record->last_name}, {$record->first_name} — {$ratingPeriod} ({$contractStartDate} to {$contractEndDate}).",
        ]);

        return $renewal;
    }
}
