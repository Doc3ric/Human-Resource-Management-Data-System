<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliberationSessionAccess extends Model
{
    protected $fillable = [
        'position_applied',
        'user_id',
        'role_in_session',
        'granted_by',
        'granted_at',
        'revoked_by',
        'revoked_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
}
