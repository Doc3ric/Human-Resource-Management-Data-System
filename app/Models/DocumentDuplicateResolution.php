<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentDuplicateResolution extends Model
{
    protected $fillable = [
        'document_id',
        'new_document_id',
        'resolved_by',
        'action',
        'justification',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function newDocument()
    {
        return $this->belongsTo(Document::class, 'new_document_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
