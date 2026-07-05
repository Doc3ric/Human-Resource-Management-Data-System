<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RenewalSignal extends Model
{
    const UPDATED_AT = null;

    public const STRENGTH_AUTHORITATIVE = 'AUTHORITATIVE';
    public const STRENGTH_CORROBORATING = 'CORROBORATING';
    public const STRENGTH_WEAK = 'WEAK';

    protected $fillable = [
        'plantilla_record_id',
        'rating_period',
        'source_module',
        'signal_type',
        'signal_strength',
        'created_by',
        'notes',
    ];

    public function plantillaRecord()
    {
        return $this->belongsTo(PlantillaRecord::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
