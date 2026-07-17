<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/** Enhancement Spec Sec. 3 — 1-year detail tracking & auto-drafted recall letter. */
class DetailOrder extends Model
{
    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'plantilla_record_id',
        'home_unit',
        'detailed_unit',
        'detail_order_no',
        'date_issued',
        'date_effective_start',
        'date_effective_end',
        'document_id',
        'status',
        'recall_letter_id',
        'remarks',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'date_effective_start' => 'date',
        'date_effective_end' => 'date',
    ];

    public function plantillaRecord()
    {
        return $this->belongsTo(PlantillaRecord::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function recallLetter()
    {
        return $this->belongsTo(Document::class, 'recall_letter_id');
    }

    /** CSC's 1-year detail limit, measured from the effective start date. */
    public function getOneYearMarkAttribute(): Carbon
    {
        return $this->date_effective_start->copy()->addDays(365);
    }

    public function getDaysRemainingAttribute(): int
    {
        return now()->startOfDay()->diffInDays($this->one_year_mark, false);
    }
}
