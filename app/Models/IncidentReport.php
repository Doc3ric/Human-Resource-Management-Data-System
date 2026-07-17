<?php

namespace App\Models;

use App\Support\Violations\ViolationLinkageService;
use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    protected $fillable = [
        'reference_no', 'incident_datetime', 'reported_at', 'location', 'reported_by',
        'reporter_name', 'personnel_id', 'involved_person_name', 'involved_position', 'involved_office',
        'witnesses', 'category', 'narrative',
        'immediate_action_taken', 'document_id',
        'advisory_citations', 'advisory_penalty_range', 'advisory_recommendation', 'advisory_generated_at',
        'status', 'reviewed_by', 'final_citations', 'review_notes',
        'final_track', 'finalized_by', 'finalized_at',
    ];

    protected $casts = [
        'incident_datetime' => 'datetime',
        'reported_at' => 'date',
        'advisory_citations' => 'array',
        'advisory_generated_at' => 'datetime',
        'final_citations' => 'array',
        'finalized_at' => 'datetime',
    ];

    /**
     * Enhancement Spec Sec. 4 — an incident only becomes a confirmed
     * violation once it's finalized (final_track set), not at the initial
     * draft/advisory stage where severity isn't decided yet.
     */
    protected static function booted()
    {
        static::updated(function (IncidentReport $report) {
            if (!$report->wasChanged('final_track') || empty($report->final_track) || empty($report->personnel_id)) {
                return;
            }

            $citations = collect($report->final_citations ?? [])->implode(', ');

            app(ViolationLinkageService::class)->recordViolation(
                sourceModule: 'IncidentReport',
                sourceRecordId: $report->id,
                plantillaRecordId: $report->personnel_id,
                violationType: $report->category,
                legalBasis: $citations !== '' ? $citations : '2025 RACCS (CSC Resolution No. 2500357)',
                severityLevel: $report->final_track === 'escalated' ? 'Grave' : 'Minor',
            );
        });
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function finalizer()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /** The PGB employee involved, if the incident names one (Module 3A involved-party capture). */
    public function personnelRecord()
    {
        return $this->belongsTo(PlantillaRecord::class, 'personnel_id');
    }

    public function counselingRecord()
    {
        return $this->hasOne(CounselingRecord::class);
    }

    /** Involved personnel is a party — used for the conflict-of-interest self-finalize guard. */
    public function involvesUser(User $user): bool
    {
        return $this->reported_by === $user->id;
    }
}
