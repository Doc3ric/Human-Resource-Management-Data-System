<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwgScoreSubmission extends Model
{
    protected $fillable = [
        'applicant_id', 'total_auto', 'total_assessor', 'percentage',
        'adjectival_classification', 'recommendation', 'overall_notes',
        'certification_accepted', 'status', 'submitted_by', 'submitted_at',
        'unlocked_by', 'unlocked_at',
    ];

    protected $casts = [
        'total_auto' => 'decimal:2',
        'total_assessor' => 'decimal:2',
        'percentage' => 'decimal:2',
        'certification_accepted' => 'boolean',
        'submitted_at' => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function isLocked(): bool
    {
        return $this->status === 'submitted';
    }
}
