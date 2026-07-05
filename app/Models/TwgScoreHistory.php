<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwgScoreHistory extends Model
{
    protected $table = 'twg_score_history';

    protected $fillable = ['applicant_id', 'action', 'total_score', 'performed_by'];

    protected $casts = [
        'total_score' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
