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
        'type', // NOSI, NOLP, NOSA
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
    ];

    /**
     * Get the plantilla record that owns this history log.
     */
    public function plantillaRecord(): BelongsTo
    {
        return $this->belongsTo(PlantillaRecord::class, 'plantilla_record_id');
    }
}
