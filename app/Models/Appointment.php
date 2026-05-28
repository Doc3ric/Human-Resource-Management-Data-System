<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'appointments';

    protected $fillable = [
        'employee_id',
        'position_id',
        'appointment_start',
        'appointment_end',
        'appointment_type',
        'remarks',
        'status',
    ];

    protected $casts = [
        'appointment_start' => 'date',
        'appointment_end' => 'date',
        'status' => 'string',
    ];

    /**
     * Get the employee for this appointment.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the position for this appointment.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Check if appointment is currently active.
     */
    public function isActive(): bool
    {
        return is_null($this->appointment_end) && $this->appointment_start <= now();
    }
}
