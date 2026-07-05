<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingParticipant extends Model
{
    protected $fillable = [
        'training_id', 'personnel_id', 'participant_name', 'participant_office',
        'is_resource_speaker', 'topic', 'attendance_status', 'hours_attended',
    ];

    protected $casts = [
        'is_resource_speaker' => 'boolean',
        'hours_attended' => 'decimal:2',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function plantillaRecord()
    {
        return $this->belongsTo(PlantillaRecord::class, 'personnel_id');
    }

    public function certificates()
    {
        return $this->hasMany(TrainingCertificate::class);
    }

    public function displayName(): string
    {
        if ($this->plantillaRecord) {
            return "{$this->plantillaRecord->last_name}, {$this->plantillaRecord->first_name}";
        }
        return $this->participant_name ?? 'Unknown Participant';
    }
}
