<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyUpdate extends Model
{
    protected $fillable = [
        'title',
        'url',
        'source',
        'published_date',
        'matched_keyword',
        'description',
        'status',
    ];
}
