<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * This IS the Enhancement Spec Sec. 4 "admin_cases" table — it predates that
 * spec (Module 10-M3's RACCS case registry) and is reused rather than
 * duplicated. Its `status` values are the real, already-in-use workflow
 * states, not the spec's literal enum; the correspondence is:
 *
 *   spec (admin_cases.case_status)      real (disciplinary_cases.status)
 *   -----------------------------------------------------------------
 *   Filed                             -> registered
 *   Preliminary Investigation         -> under_investigation
 *   Formal Charge                     -> hearing
 *   Decided                          -> decided (or appealed, mid-appeal)
 *   Closed                            -> closed
 *
 * Do not rename these column values to match the spec text — that would
 * break the existing Discipline module's controller, views, and real data.
 */
class DisciplinaryCase extends Model
{
    protected $fillable = [
        'case_no', 'personnel_id', 'incident_report_id', 'originating_violation_id', 'formal_charge',
        'offense_classification', 'status', 'decision', 'penalty', 'decided_at',
        'document_id', 'created_by', 'closed_at',
    ];

    protected $casts = [
        'decided_at' => 'date',
        'closed_at' => 'datetime',
    ];

    public function incidentReport()
    {
        return $this->belongsTo(IncidentReport::class);
    }

    /** Reference only, not a merge — the violation ledger row that triggered this case's auto-spawn, if any. */
    public function originatingViolation()
    {
        return $this->belongsTo(Violation::class, 'originating_violation_id', 'violation_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function personnel()
    {
        return $this->belongsTo(PlantillaRecord::class, 'personnel_id');
    }
}
