<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingCertificate extends Model
{
    public $timestamps = false; // issued_at (with its own default) serves this role

    protected $fillable = ['training_participant_id', 'certificate_type', 'reference_no', 'document_id', 'issued_by'];

    protected $casts = ['issued_at' => 'datetime'];

    public function participant()
    {
        return $this->belongsTo(TrainingParticipant::class, 'training_participant_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
