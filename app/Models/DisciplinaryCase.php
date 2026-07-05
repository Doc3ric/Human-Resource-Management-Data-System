<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisciplinaryCase extends Model
{
    protected $fillable = [
        'case_no', 'personnel_id', 'incident_report_id', 'formal_charge',
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

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function personnel()
    {
        return $this->belongsTo(PlantillaRecord::class, 'personnel_id');
    }
}
