<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = ['plantilla_record_id', 'leave_type_id', 'year', 'earned_days', 'used_days'];

    protected $casts = [
        'earned_days' => 'decimal:3',
        'used_days' => 'decimal:3',
    ];

    public function getRemainingDaysAttribute(): float
    {
        return (float) $this->earned_days - (float) $this->used_days;
    }

    public function plantillaRecord()
    {
        return $this->belongsTo(PlantillaRecord::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
