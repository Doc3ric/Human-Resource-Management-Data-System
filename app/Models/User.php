<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, LogsActivity;

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
        'organizational_unit_id',
        'profile_picture',
        'last_login',
        'status',
        'is_approved',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        return $this->role === 'super_admin';
    }

    public function isSalaryAdmin(): bool
    {
        return $this->role === 'salary_admin';
    }

    public function isInventoryAdmin(): bool
    {
        return $this->role === 'inventory_admin';
    }

    /**
     * Check if user can access a given module.
     * Modules: 'plantilla', 'step_increment', 'import'
     */
    public function canAccess(string $module): bool
    {
        if ($this->isSuperAdmin()) return true;
        return match($module) {
            'plantilla'      => $this->isInventoryAdmin(),
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
        return match($this->role) {
            'super_admin'     => 'Super Admin',
            'salary_admin'    => 'Salary Admin',
            'inventory_admin' => 'Inventory Admin',
            default           => ucfirst($this->role),
        };
    }

    /**
     * Get the user's activity logs.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
