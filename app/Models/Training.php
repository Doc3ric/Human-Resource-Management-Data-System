<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'type', 'competency_area', 'institution', 'venue',
        'date_from', 'date_to', 'total_hours', 'organizer', 'fund_source',
        'reference_authority', 'document_id', 'created_by',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'total_hours' => 'decimal:2',
    ];

    public function participants()
    {
        return $this->hasMany(TrainingParticipant::class);
    }
}
