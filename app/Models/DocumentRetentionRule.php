<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRetentionRule extends Model
{
    protected $fillable = [
        'doc_type_code',
        'record_series_id',
        'rule_label',
        'retention_years',
        'is_permanent',
        'disposal_requires_ledger_verification',
    ];

    protected $casts = [
        'is_permanent' => 'boolean',
        'disposal_requires_ledger_verification' => 'boolean',
    ];

    /** Module 1B.5 — links IDCC's per-doc_type_code rule to the canonical NAP series. */
    public function recordSeries()
    {
        return $this->belongsTo(RetentionSchedule::class, 'record_series_id');
    }
}
