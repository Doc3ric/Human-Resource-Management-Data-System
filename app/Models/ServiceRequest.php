<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'request_type', 'requester_name', 'requester_contact', 'plantilla_record_id',
        'date_requested', 'due_at', 'status', 'escalated_at', 'handled_by',
    ];

    protected $casts = [
        'date_requested' => 'date',
        'due_at' => 'date',
        'escalated_at' => 'datetime',
    ];

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_at->isPast();
    }

    /** Days remaining until the Citizen's Charter deadline (negative = overdue). */
    public function daysRemaining(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->due_at, false);
    }
}
