<?php

namespace App\Models;

use App\Support\Leave\LeaveViolationLetterService;
use App\Support\Violations\ViolationLinkageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveViolation extends Model
{
    use SoftDeletes;

    /** Grave under the 2025 RACCS — everything else here is Light/Less Grave (Minor). */
    private const GRAVE_TYPES = ['AWOL', 'HABITUAL_ABSENTEEISM', 'SHOW_CAUSE'];

    protected $fillable = [
        'plantilla_record_id', 'violation_type', 'offense_tier', 'rating_period',
        'details', 'status', 'document_id', 'issued_by', 'issued_at',
    ];

    protected $casts = [
        'details' => 'array',
        'issued_at' => 'datetime',
    ];

    /**
     * Enhancement Spec Sec. 4 — every leave violation, regardless of the
     * controller path that created it, immutably feeds the 201-file ledger.
     */
    protected static function booted()
    {
        static::created(function (LeaveViolation $violation) {
            app(ViolationLinkageService::class)->recordViolation(
                sourceModule: 'Leave',
                sourceRecordId: $violation->id,
                plantillaRecordId: $violation->plantilla_record_id,
                violationType: $violation->violation_type,
                legalBasis: app(LeaveViolationLetterService::class)->getLegalBasis($violation->violation_type),
                severityLevel: in_array($violation->violation_type, self::GRAVE_TYPES, true) ? 'Grave' : 'Minor',
            );
        });
    }

    public function plantillaRecord()
    {
        return $this->belongsTo(PlantillaRecord::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
