<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpiAccessLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'spi_access_log';

    protected $fillable = ['document_id', 'user_id', 'action'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
