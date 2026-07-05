<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PanelMember extends Authenticatable
{
    use Notifiable;

    protected $guard = 'panel';

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'position',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'      => 'hashed',
            'last_login_at' => 'datetime',
            'is_active'     => 'boolean',
        ];
    }

    public function isHrmpsb(): bool
    {
        return $this->type === 'hrmpsb';
    }

    public function isTwg(): bool
    {
        return $this->type === 'twg';
    }

    public function typeLabel(): string
    {
        return $this->type === 'twg' ? 'TWG Member' : 'HRMPSB Member';
    }
}
