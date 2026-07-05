<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Module 1B.4 — one row per authorized disposal, referencing the signed
 * NAP Form No. 3 (Authority to Dispose) that legally permits it. Never
 * updated after creation; a record either has one on file or it doesn't.
 */
class DisposalAuthorization extends Model
{
    protected $fillable = [
        'disposable_type',
        'disposable_id',
        'nap_form_reference',
        'file_path',
        'authorized_by',
        'authorized_at',
        'notes',
    ];

    protected $casts = [
        'authorized_at' => 'datetime',
    ];

    public function disposable(): MorphTo
    {
        return $this->morphTo();
    }

    public function authorizedBy()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
