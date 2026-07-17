<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTablePreference extends Model
{
    protected $fillable = ['user_id', 'tab_key', 'hidden_columns', 'column_widths'];

    protected $casts = [
        'hidden_columns' => 'array',
        'column_widths' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
