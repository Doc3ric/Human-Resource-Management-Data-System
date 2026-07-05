<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    protected $fillable = [
        'reference_no', 'incident_datetime', 'reported_at', 'location', 'reported_by',
        'reporter_name', 'personnel_id', 'witnesses', 'category', 'narrative',
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
