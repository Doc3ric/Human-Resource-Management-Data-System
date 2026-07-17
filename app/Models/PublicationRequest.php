<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A versioned CS Form No. 9 (Revised 2025) request. Editing any published
 * field after SUBMITTED_TO_CSC_FO spawns a new row (supersedes_request_id)
 * rather than overwriting — see VppmStatusService::republish().
 */
class PublicationRequest extends Model
{
    use LogsActivity;

    protected $fillable = [
        'vacant_position_id',
        'version_no',
        'supersedes_request_id',
        'agency_name',
        'agency_contact_person',
        'agency_contact_number',
        'agency_contact_email',
        'prepared_by_id',
        'status',
        'submission_mode',
        'submitted_to_csc_fo_date',
        'csc_fo_receiving_copy_path',
        'posting_start_date',
        'posting_min_required_days',
        'posting_actual_end_date',
        'validity_start_date',
        'validity_months',
        'validity_extended_reason',
        'validity_end_date',
        'edit_reason',
        'is_section26_exempt_edit',
    ];

    protected $casts = [
        'submitted_to_csc_fo_date' => 'date',
        'posting_start_date' => 'date',
        'posting_actual_end_date' => 'date',
        'validity_start_date' => 'date',
        'validity_end_date' => 'date',
        'is_section26_exempt_edit' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "This publication request has been {$eventName}");
    }

    public function vacantPosition()
    {
        return $this->belongsTo(VacantPosition::class);
    }

    public function supersedes()
    {
        return $this->belongsTo(self::class, 'supersedes_request_id');
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by_id');
    }

    public function postingSiteLogs()
    {
        return $this->hasMany(PostingSiteLog::class);
    }

    /** Generic e-signature capture (Module 10-M4) — Dept Head's CS Form 9 signature lives here, not on this table. */
    public function signatures()
    {
        return $this->morphMany(ESignature::class, 'signable');
    }

    public function getIsSignedAttribute(): bool
    {
        return $this->signatures()->exists();
    }

    /** Required posting period met — computed, not stored. */
    public function getPostingComplianceDateAttribute(): ?Carbon
    {
        if (!$this->posting_start_date) {
            return null;
        }

        return $this->posting_start_date->copy()->addDays($this->posting_min_required_days);
    }

    public function getDaysToPostingComplianceAttribute(): ?int
    {
        $target = $this->posting_compliance_date;

        return $target ? now()->startOfDay()->diffInDays($target, false) : null;
    }

    public function getDaysToExpiryAttribute(): ?int
    {
        return $this->validity_end_date
            ? now()->startOfDay()->diffInDays($this->validity_end_date, false)
            : null;
    }

    /** All 3 conspicuous-place sites logged with a posted_date (spec Sec.5 step 5). */
    public function getAllSitesConfirmedAttribute(): bool
    {
        return $this->postingSiteLogs()->count() >= 3;
    }

    /** Before submission, edits apply in place — no versioning needed (spec Sec.6). */
    public function getCanEditInPlaceAttribute(): bool
    {
        return in_array($this->status, ['DRAFT', 'PENDING_SIGNATURE'], true);
    }
}
