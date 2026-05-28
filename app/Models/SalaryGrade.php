<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryGrade extends Model
{
    use SoftDeletes;

    protected $table = 'salary_grades';

    protected $fillable = ['salary_schedule_id', 'grade', 'step', 'monthly_salary'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function salarySchedule(): BelongsTo
    {
        return $this->belongsTo(SalarySchedule::class, 'salary_schedule_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Get monthly salary for a given grade and step.
     *
     * Priority:
     *  1. Active salary schedule's rows (salary_schedule_id = active ID)
     *  2. Legacy rows where salary_schedule_id IS NULL (pre-tranche data)
     */
    public static function getRate(?int $grade, ?int $step): float
    {
        if ($grade === null || $step === null) {
            return 0;
        }

        $activeSchedule = SalarySchedule::getActive();

        if ($activeSchedule) {
            $record = static::where('salary_schedule_id', $activeSchedule->id)
                            ->where('grade', $grade)
                            ->where('step', $step)
                            ->first();
        } else {
            // Fall back to legacy rows (no schedule assigned)
            $record = static::whereNull('salary_schedule_id')
                            ->where('grade', $grade)
                            ->where('step', $step)
                            ->first();
        }

        return $record ? (float) $record->monthly_salary : 0;
    }

    /**
     * Get monthly salary for a given grade and step from a SPECIFIC schedule.
     * Used by NOSA to compare previous schedule vs active schedule.
     */
    public static function getRateForSchedule(?int $scheduleId, ?int $grade, ?int $step): float
    {
        if ($scheduleId === null || $grade === null || $step === null) {
            return 0;
        }

        $record = static::where('salary_schedule_id', $scheduleId)
                        ->where('grade', $grade)
                        ->where('step', $step)
                        ->first();

        return $record ? (float) $record->monthly_salary : 0;
    }
}
