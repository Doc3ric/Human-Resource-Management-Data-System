<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $fillable = [
        'plantilla_record_id', 'leave_type_id', 'date_from', 'date_to',
        'days_requested', 'filed_at', 'status', 'reason', 'disapproval_reason',
        'document_id', 'filed_by', 'acted_by',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'filed_at' => 'date',
        'days_requested' => 'decimal:3',
    ];

    public function plantillaRecord()
    {
        return $this->belongsTo(PlantillaRecord::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
