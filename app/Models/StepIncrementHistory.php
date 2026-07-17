<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StepIncrementHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'plantilla_record_id',
        'salary_schedule_id',
        'type', // NOSI, NOLP, NOSA, SSL_ADJUSTMENT
        'previous_step',
        'new_step',
        'previous_salary_grade',
        'new_salary_grade',
        'previous_annual_salary',
        'new_annual_salary',
        'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'previous_annual_salary' => 'decimal:2',
        'new_annual_salary' => 'decimal:2',
        'previous_step' => 'integer',
        'new_step' => 'integer',
        'previous_salary_grade' => 'integer',
        'new_salary_grade' => 'integer',
        'salary_schedule_id' => 'integer',
    ];

    /**
     * Get the plantilla record that owns this history log.
     */
    public function plantillaRecord(): BelongsTo
    {
        return $this->belongsTo(PlantillaRecord::class, 'plantilla_record_id');
    }

    /** The Salary Schedule this row's SSL_ADJUSTMENT was reconciled against (null for NOSI/NOLP/NOSA). */
    public function salarySchedule(): BelongsTo
    {
        return $this->belongsTo(SalarySchedule::class, 'salary_schedule_id');
    }
}
