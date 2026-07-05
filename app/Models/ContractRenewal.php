<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractRenewal extends Model
{
    protected $fillable = [
        'plantilla_record_id',
        'contract_start_date',
        'contract_end_date',
        'rate',
        'rate_type',
        'renewed_by',
        'notes',
    ];

    protected $casts = [
        'contract_start_date' => 'date',
        'contract_end_date'   => 'date',
        'rate'                => 'decimal:2',
    ];

    public function record()
    {
        return $this->belongsTo(PlantillaRecord::class, 'plantilla_record_id');
    }

    public function renewedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'renewed_by');
    }
}
