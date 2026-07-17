<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, LogsActivity, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'office_department_id',
        'profile_picture',
        'last_login',
        'status',
        'is_approved',
        'must_change_password',
        'meets_complexity_gate',
        'theme_preference',
        'google2fa_secret',
        'google2fa_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'meets_complexity_gate' => 'boolean',
            'google2fa_secret' => 'encrypted',
            'google2fa_enabled' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "This user has been {$eventName}");
    }

    // ── Role Helpers ──────────────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('System & Administration');
    }

    public function isSalaryAdmin(): bool
    {
        return $this->hasRole('Welfare & Benefits');
    }

    public function isInventoryAdmin(): bool
    {
        return $this->hasRole('Personnel Records');
    }

    public function isAppointmentAdmin(): bool
    {
        return $this->hasRole('Appointment');
    }

    public function isAppointmentEncoder(): bool
    {
        return $this->hasRole('Appointment Encoder');
    }

    public function isPerformanceAdmin(): bool
    {
        return $this->hasRole('Performance Management');
    }

    public function isLeaveAdmin(): bool
    {
        return $this->hasRole('Leave Administration');
    }

    public function isViewer(): bool
    {
        return $this->hasRole('Viewer');
    }

    /**
     * Check if user can access a given module.
     * Modules: 'plantilla', 'step_increment', 'import'
     */
    public function canAccess(string $module): bool
    {
        if ($this->isSuperAdmin()) return true;
        return match($module) {
            'plantilla'      => $this->isInventoryAdmin() || $this->isViewer(),
            'step_increment' => $this->isSalaryAdmin(),
            'import'         => false,
            default          => false,
        };
    }

    /**
     * Human-readable role label.
     */
    public function getRoleLabelAttribute(): string
    {
        $role = $this->roles()->first();
        return $role ? $role->name : 'No Role';
    }

    /**
     * Get the user's activity logs.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
