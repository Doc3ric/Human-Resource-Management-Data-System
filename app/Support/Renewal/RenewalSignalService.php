<?php

namespace App\Support\Renewal;

use App\Models\PlantillaRecord;
use App\Models\RenewalSignal;
use App\Models\User;

/**
 * Module 1A.2/1A.4 — any module calls this to record that it noticed
 * activity implying an employee is active. This NEVER mutates is_renewed —
 * only RenewalCommitService does that, and only from an AUTHORITATIVE
 * signal or explicit commit authority.
 */
class RenewalSignalService
{
    public function record(
        PlantillaRecord $record,
        string $ratingPeriod,
        string $sourceModule,
        string $signalType,
        string $strength,
        ?User $actor = null,
        ?string $notes = null,
    ): RenewalSignal {
        return RenewalSignal::create([
            'plantilla_record_id' => $record->id,
            'rating_period' => $ratingPeriod,
            'source_module' => $sourceModule,
            'signal_type' => $signalType,
            'signal_strength' => $strength,
            'created_by' => $actor?->id,
            'notes' => $notes,
        ]);
    }

    /** Convenience for the common corroborating case (Module 1A.4). */
    public function corroborate(PlantillaRecord $record, string $ratingPeriod, string $sourceModule, string $signalType, ?User $actor = null, ?string $notes = null): RenewalSignal
    {
        return $this->record($record, $ratingPeriod, $sourceModule, $signalType, RenewalSignal::STRENGTH_CORROBORATING, $actor, $notes);
    }

    /** Only Records (uploading a signed renewal doc) or Appointments should ever call this. */
    public function authoritative(PlantillaRecord $record, string $ratingPeriod, string $sourceModule, string $signalType, ?User $actor = null, ?string $notes = null): RenewalSignal
    {
        return $this->record($record, $ratingPeriod, $sourceModule, $signalType, RenewalSignal::STRENGTH_AUTHORITATIVE, $actor, $notes);
    }

    /** Signals accumulated for a record in a given period, for the synchronized widget. */
    public function forRecord(PlantillaRecord $record, ?string $ratingPeriod = null)
    {
        $query = RenewalSignal::where('plantilla_record_id', $record->id)->latest();
        if ($ratingPeriod) {
            $query->where('rating_period', $ratingPeriod);
        }
        return $query->get();
    }
}
