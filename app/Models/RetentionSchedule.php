<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetentionSchedule extends Model
{
    protected $table = 'retention_schedule';

    protected $fillable = [
        'record_series_title',
        'description',
        'record_category',
        'time_value',
        'active_period',
        'storage_period',
        'total_retention',
        'disposition_action',
        'legal_basis',
        'nap_grds_item_ref',
        'remarks',
        'last_updated_by',
    ];

    public function isPermanent(): bool
    {
        return $this->time_value === 'PERMANENT' || $this->disposition_action === 'PERMANENT_PRESERVATION';
    }
}
