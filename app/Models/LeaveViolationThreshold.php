<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveViolationThreshold extends Model
{
    protected $fillable = ['violation_type', 'config', 'legal_basis'];

    protected $casts = [
        'config' => 'array',
    ];
}
