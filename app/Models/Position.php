<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'positions';

    protected $fillable = [
        'organizational_unit_id',
        'title',
        'code',
        'salary_grade',
        'abolished',
        'status',
    ];

    protected $casts = [
        'abolished' => 'boolean',
        'status' => 'string',
    ];

    /**
     * Get the organizational unit that owns this position.
     */
    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }

    /**
     * Get the appointments for this position.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Check if position is filled (has active appointment).
     */
    public function isFilled(): bool
    {
        return $this->appointments()->exists();
    }

    /**
     * Check if position is vacant.
     */
    public function isVacant(): bool
    {
        return !$this->isFilled() && !$this->abolished;
    }
}
