<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LguDocument extends Model
{
    protected $fillable = ['type', 'number', 'title', 'date_issued', 'document_id', 'created_by'];

    protected $casts = ['date_issued' => 'date'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
