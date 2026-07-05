<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwgScore extends Model
{
    protected $fillable = [
        'applicant_id', 'item_no', 'criterion_id',
        'auto_populated_value', 'assessor_value', 'is_edited',
        'assessor_notes', 'assessed_by', 'assessed_at',
    ];

    protected $casts = [
        'auto_populated_value' => 'decimal:2',
        'assessor_value' => 'decimal:2',
        'is_edited' => 'boolean',
        'assessed_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function criterion()
    {
        return $this->belongsTo(TwgRatingCriterion::class, 'criterion_id');
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
