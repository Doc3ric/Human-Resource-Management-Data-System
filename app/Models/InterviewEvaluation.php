<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'rater_id',
        'panel_member_id',
        'rater_name',
        'office',
        'position',
        'ratings',
        'remarks',
        'total_score',
    ];

    protected $casts = [
        'ratings' => 'array',
        'total_score' => 'decimal:2',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function panelMember()
    {
        return $this->belongsTo(\App\Models\PanelMember::class, 'panel_member_id');
    }

    /** Display name regardless of whether rater is a system user or panel member */
    public function getRaterDisplayNameAttribute(): string
    {
        if ($this->rater_name) {
            return $this->rater_name;
        }
        return $this->rater?->name ?? 'Unknown';
    }
}
