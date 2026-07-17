<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualificationStandard extends Model
{
    use HasFactory;

    protected $table = 'qualification_standards';

    protected $fillable = [
        'position_title',
        'education',
        'training',
        'experience',
        'eligibility',
    ];
}
