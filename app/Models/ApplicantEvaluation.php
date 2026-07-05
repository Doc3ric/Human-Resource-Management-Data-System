<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantEvaluation extends Model
{
    protected $fillable = [
        'applicant_id',
        'qs_requirement',
        'exam_status',
        'docs_complete',
        'final_rating',
        'remarks',
        'evaluated_by',
        'evaluated_at',
    ];

    protected $casts = [
        'evaluated_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function getFinalRatingColorAttribute(): string
    {
        return $this->final_rating === 'Qualified' ? '#155724' : '#721c24';
    }

    public function getFinalRatingBgAttribute(): string
    {
        return $this->final_rating === 'Qualified' ? '#d4edda' : '#f8d7da';
    }
}
