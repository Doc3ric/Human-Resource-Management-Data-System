<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Enhancement Spec Sec. 4 — immutable ledger. Never updated or deleted at
 * the application layer; the 201-file view joins this with
 * violation_status_log at read-time to show current state.
 */
class Violation extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'violation_id';

    protected $fillable = [
        'plantilla_record_id',
        'source_module',
        'source_record_id',
        'violation_type',
        'legal_basis',
        'severity_level',
        'date_created',
    ];

    protected $casts = [
        'date_created' => 'datetime',
    ];

    public function plantillaRecord()
    {
        return $this->belongsTo(PlantillaRecord::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ViolationStatusLog::class, 'violation_id', 'violation_id')->orderByDesc('date_logged');
    }

    public function currentStatus(): string
    {
        return $this->statusLogs()->value('status') ?? 'Recorded';
    }

    /** The originating record in whichever module reported this violation. */
    public function sourceRecord(): LeaveViolation|IncidentReport|null
    {
        return match ($this->source_module) {
            'Leave' => LeaveViolation::find($this->source_record_id),
            'IncidentReport' => IncidentReport::find($this->source_record_id),
            default => null,
        };
    }
}
