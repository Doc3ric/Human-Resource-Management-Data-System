<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrmpsbInterviewCriterion extends Model
{
    protected $fillable = [
        'criterion_name',
        'point_value',
        'description',
        'is_active',
    ];

    protected $casts = [
        'point_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
