<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalarySchedule extends Model
{
    use SoftDeletes;

    protected $table = 'salary_schedules';

    protected $fillable = [
        'name',
        'law_name',
        'lbc_number',
        'effective_date',
        'is_active',
        'description',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active'      => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function salaryGrades(): HasMany
    {
        return $this->hasMany(SalaryGrade::class, 'salary_schedule_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Return the currently active schedule, or null if none is set.
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Activate this schedule (deactivates all others).
     */
    public function activate(): void
    {
        static::where('id', '!=', $this->id)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }
}
