<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViolationStatusLog extends Model
{
    public $timestamps = false;

    protected $table = 'violation_status_log';

    protected $primaryKey = 'log_id';

    protected $fillable = [
        'violation_id',
        'status',
        'date_logged',
        'remarks',
        'logged_by',
    ];

    protected $casts = [
        'date_logged' => 'datetime',
    ];

    public function violation()
    {
        return $this->belongsTo(Violation::class, 'violation_id', 'violation_id');
    }

    public function loggedBy()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
