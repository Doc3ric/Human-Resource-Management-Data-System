<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmpsbSignatory extends Model
{
    use HasFactory;

    protected $fillable = [
        'team',
        'role',
        'name',
        'title',
    ];
}
