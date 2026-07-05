<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveViolation extends Model
{
    protected $fillable = [
        'plantilla_record_id', 'violation_type', 'offense_tier', 'rating_period',
        'details', 'status', 'document_id', 'issued_by', 'issued_at',
    ];

    protected $casts = [
        'details' => 'array',
        'issued_at' => 'datetime',
    ];

    public function plantillaRecord()
    {
        return $this->belongsTo(PlantillaRecord::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
