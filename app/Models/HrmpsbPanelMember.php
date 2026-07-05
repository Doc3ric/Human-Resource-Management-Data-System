<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrmpsbPanelMember extends Model
{
    protected $fillable = [
        'position_applied',
        'panel_member_id',
        'user_id',
        'member_role',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function panelMember()
    {
        return $this->belongsTo(PanelMember::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
