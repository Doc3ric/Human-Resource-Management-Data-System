<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class JobOrder extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'job_orders';

    protected $fillable = [
        'charges',
        'last_name',
        'first_name',
        'middle_initial',
        'name_extension',
        'position_title',
        'nature_of_work',
        'office',
        'rate_per_day',
        'first_day_of_service',
        'birthdate',
        'address',
        'eligibility',
        'gender',
        'first_level_eligibility',
        'second_level_eligibility',
        'ip_community_membership',
        'solo_parent',
        'reemployment',
        'remarks',
        'employee_code',
    ];

    protected $casts = [
        'first_day_of_service'    => 'date',
        'birthdate'               => 'date',
        'rate_per_day'            => 'decimal:2',
        'first_level_eligibility' => 'boolean',
        'second_level_eligibility'=> 'boolean',
        'solo_parent'             => 'boolean',
        'reemployment'            => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "This job order record has been {$eventName}");
    }

    /**
     * Generate a unique employee code: DDMMYYYY + first letter of last name (uppercase).
     * Appends a numeric suffix if the base code already exists (e.g. 01021980S2, S3…).
     *
     * @param int|null $excludeId  Exclude this record's own ID when checking (for updates).
     */
    public static function generateEmployeeCode(?string $lastName, mixed $dob, ?int $excludeId = null): ?string
    {
        if (empty($lastName) || empty($dob)) {
            return null;
        }
        try {
            $date    = \Carbon\Carbon::parse($dob);
            $initial = strtoupper(substr(trim($lastName), 0, 1));
            $base    = $date->format('dmY') . $initial;

            $candidate = $base;
            $suffix    = 2;
            $query = static::withTrashed();
            while ($query->clone()->where('employee_code', $candidate)
                         ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                         ->exists()) {
                $candidate = $base . $suffix;
                $suffix++;
            }

            return $candidate;
        } catch (\Exception) {
            return null;
        }
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    /**
     * Full name: "LASTNAME, Firstname M.I. Ext"
     */
    public function getFullNameAttribute(): string
    {
        $name = strtoupper($this->last_name ?? '');
        if ($this->first_name) {
            $name .= ', ' . $this->first_name;
        }
        if ($this->middle_initial) {
            $name .= ' ' . strtoupper($this->middle_initial) . '.';
        }
        if ($this->name_extension) {
            $name .= ' ' . $this->name_extension;
        }
        return trim($name);
    }

    /**
     * Age of the employee in years (null if no birthdate).
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birthdate) return null;
        return $this->birthdate->age;
    }

    /**
     * Whole years of service computed from first_day_of_service.
     */
    public function getYearsOfServiceAttribute(): int
    {
        if (!$this->first_day_of_service) return 0;
        return (int) $this->first_day_of_service->diffInYears(now());
    }

    /**
     * Remaining months of service after full years are subtracted.
     */
    public function getMonthsOfServiceAttribute(): int
    {
        if (!$this->first_day_of_service) return 0;
        $years = $this->years_of_service;
        return (int) $this->first_day_of_service->copy()->addYears($years)->diffInMonths(now());
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeByOffice($query, string $office)
    {
        return $query->where('office', 'like', "%{$office}%");
    }

    public function scopeByCharges($query, string $charges)
    {
        return $query->where('charges', 'like', "%{$charges}%");
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('last_name',  'like', "%{$term}%")
              ->orWhere('first_name', 'like', "%{$term}%")
              ->orWhere('position_title', 'like', "%{$term}%");
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function attachments()
    {
        return $this->hasMany(EmployeeAttachment::class, 'job_order_id');
    }
}
