<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    protected $fillable = [
        'applicant_id', 'classification', 'table_number',
        'exam_date', 'exam_time', 'room', 'generated_by',
    ];

    protected $casts = [
        'exam_date' => 'date',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
