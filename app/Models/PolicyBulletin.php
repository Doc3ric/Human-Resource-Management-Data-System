<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyBulletin extends Model
{
    protected $fillable = [
        'source_agency',
        'reference_no',
        'title',
        'summary',
        'url',
        'date_issued',
        'applicability_score',
        'is_acknowledged',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'is_acknowledged' => 'boolean',
        'applicability_score' => 'integer',
    ];
}
