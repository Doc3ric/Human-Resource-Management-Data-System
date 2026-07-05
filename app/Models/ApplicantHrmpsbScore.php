<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantHrmpsbScore extends Model
{
    protected $guarded = [];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
    
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
