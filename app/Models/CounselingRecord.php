<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CounselingRecord extends Model
{
    protected $fillable = [
        'incident_report_id', 'recommendation', 'action_plan', 'target_date',
        'session_log', 'follow_up_date', 'created_by',
    ];

    protected $casts = [
        'target_date' => 'date',
        'follow_up_date' => 'date',
        'session_log' => 'array',
    ];

    public function incidentReport()
    {
        return $this->belongsTo(IncidentReport::class);
    }
}
