<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = ['code', 'name', 'max_days_per_year', 'requires_medical_cert_over_days', 'advance_notice_days'];
}
