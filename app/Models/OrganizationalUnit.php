<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationalUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organizational_units';

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the positions for this organizational unit.
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
