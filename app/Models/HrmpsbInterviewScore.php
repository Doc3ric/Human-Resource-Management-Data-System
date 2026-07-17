<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrmpsbInterviewScore extends Model
{
    protected $fillable = [
        'applicant_id',
        'criterion_id',
        'member_user_id',
        'score',
        'remarks',
        'scored_at',
        'is_consolidated',
        'consolidated_by',
        'consolidated_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'is_consolidated' => 'boolean',
        'scored_at' => 'datetime',
        'consolidated_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function criterion()
    {
        return $this->belongsTo(HrmpsbInterviewCriterion::class);
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_user_id');
    }

    public function consolidatedBy()
    {
        return $this->belongsTo(User::class, 'consolidated_by');
    }
}
