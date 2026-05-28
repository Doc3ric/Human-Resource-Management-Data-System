<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'plantilla_record_id',
        'from_date',
        'to_date',
        'designation',
        'status',
        'salary',
        'station_branch',
        'lwp',
        'separation_date',
        'cause',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'separation_date' => 'date',
        'salary' => 'decimal:2',
    ];

    /**
     * Get the plantilla record that owns this service record row.
     */
    public function plantillaRecord(): BelongsTo
    {
        return $this->belongsTo(PlantillaRecord::class, 'plantilla_record_id');
    }
}
