<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreEvaluationResult extends Model
{
    protected $fillable = [
        'applicant_id',
        'result',
        'remarks',
        'evaluated_by',
        'evaluated_at',
        'is_locked',
        'reopened_by',
        'reopened_at',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'evaluated_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function evaluatedBy()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function reopenedBy()
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }
}
