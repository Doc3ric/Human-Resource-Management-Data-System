<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaccsAccessLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'raccs_access_log';

    protected $fillable = ['document_id', 'user_id', 'action', 'outcome', 'ip_address', 'denial_reason'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
