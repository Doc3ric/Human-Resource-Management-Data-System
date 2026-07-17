<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** VPPM — RA 7041 / 2025 ORAOHRA Sec. 26/30/31. One row per vacancy episode eligible for publication. */
class VacantPosition extends Model
{
    use LogsActivity;

    protected $fillable = [
        'plantilla_record_id',
        'item_no_snapshot',
        'position_title',
        'parenthetical_title',
        'salary_grade',
        'monthly_salary',
        'place_of_assignment',
        'office_division',
        'appointment_status',
        'vacancy_type',
        'vice_whom',
        'vacated_date',
        'is_anticipated',
        'anticipated_incumbent_separation_date',
        'qs_education',
        'qs_training',
        'qs_experience',
        'qs_eligibility',
        'created_by',
        'status',
    ];

    protected $casts = [
        'vacated_date' => 'date',
        'is_anticipated' => 'boolean',
        'anticipated_incumbent_separation_date' => 'date',
        'monthly_salary' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "This vacant position has been {$eventName}");
    }

    public function plantillaRecord()
    {
        return $this->belongsTo(PlantillaRecord::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publicationRequests()
    {
        return $this->hasMany(PublicationRequest::class);
    }

    /** The currently-active (highest version_no, not superseded-away) request, if any. */
    public function latestPublicationRequest()
    {
        return $this->hasOne(PublicationRequest::class)->latestOfMany('version_no');
    }

    /** Sec 31: anticipated vacancies may publish up to 180 days before separation. */
    public function getAnticipatedPublicationEligibleAttribute(): bool
    {
        if (!$this->is_anticipated || !$this->anticipated_incumbent_separation_date) {
            return false;
        }

        return now()->diffInDays($this->anticipated_incumbent_separation_date, false) <= 180;
    }

    /**
     * Spec Sec.5 step 6 ties PUBLICATION_DEFICIENT to a flag at HRMPSB
     * deliberation start (Phase B decision: warn, never block — this system
     * generally prefers surfacing risk over hard-stopping an existing flow;
     * the one place elsewhere a hard block was spec'd, Module 3.2's Plantilla
     * Mismatch Block, was never actually wired to enforce either).
     * Matches by plantilla item_no since Applicant has no direct FK to a vacancy.
     */
    public static function deficientRequestForItemNo(?string $itemNo): ?PublicationRequest
    {
        if (!$itemNo) {
            return null;
        }

        return PublicationRequest::whereHas(
            'vacantPosition.plantillaRecord',
            fn ($q) => $q->where('item_no_new', $itemNo)
        )->where('status', 'PUBLICATION_DEFICIENT')
            ->latest('id')
            ->first();
    }
}
