<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    // No updated_at — this table is INSERT-only at the application level
    const UPDATED_AT = null;

    protected $fillable = [
        'filename', 'type', 'size_bytes', 'status',
        'storage_path', 'storage_driver', 'encrypted', 'notes', 'created_by',
    ];

    protected $casts = [
        'encrypted'   => 'boolean',
        'size_bytes'  => 'integer',
        'created_at'  => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getFormattedSizeAttribute(): string
    {
        $b = $this->size_bytes ?? 0;
        if ($b >= 1073741824) return round($b / 1073741824, 2) . ' GB';
        if ($b >= 1048576)    return round($b / 1048576, 2) . ' MB';
        if ($b >= 1024)       return round($b / 1024, 2) . ' KB';
        return $b . ' B';
    }
}
