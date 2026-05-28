<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttachment extends Model
{
    protected $fillable = [
        'plantilla_record_id',
        'job_order_id',
        'casual_employee_id',
        'file_name',
        'file_path',
        'file_type',
        'document_type',
        'uploaded_by',
    ];

    public function plantillaRecord()
    {
        return $this->belongsTo(PlantillaRecord::class);
    }

    public function jobOrder()
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function casualEmployee()
    {
        return $this->belongsTo(CasualEmployee::class);
    }
}
