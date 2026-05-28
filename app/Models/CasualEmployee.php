<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CasualEmployee extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'casual_employees';

    protected $fillable = [
        'office',
        'item_no_old',
        'item_no_new',
        'position_title',
        'is_vacant',
        'last_name',
        'first_name',
        'middle_initial',
        'name_extension',
        'legislative_district',
        'sg_current',
        'step_current',
        'salary_current',
        'sg_proposed',
        'step_proposed',
        'salary_proposed',
        'increase_decrease',
        'previous_rate',
        'current_rate',
        'gender',
        'birthdate',
        'first_day_of_service',
        'eligibility',
        'address',
        'solo_parent',
        'ip_community_membership',
        'annotation',
        'employee_code',
    ];

    protected $casts = [
        'is_vacant'           => 'boolean',
        'solo_parent'         => 'boolean',
        'birthdate'           => 'date',
        'first_day_of_service'=> 'date',
        'salary_current'      => 'decimal:2',
        'salary_proposed'     => 'decimal:2',
        'increase_decrease'   => 'decimal:2',
        'previous_rate'       => 'decimal:2',
        'current_rate'        => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "This casual employee record has been {$eventName}");
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
        if ($this->is_vacant) return 'VACANT';
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
     * Monthly salary from annual (current year).
     */
    public function getMonthlySalaryCurrentAttribute(): float
    {
        return round((float)($this->salary_current ?? 0) / 12, 2);
    }

    /**
     * Monthly salary from annual (proposed).
     */
    public function getMonthlySalaryProposedAttribute(): float
    {
        return round((float)($this->salary_proposed ?? 0) / 12, 2);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('last_name',      'like', "%{$term}%")
              ->orWhere('first_name',   'like', "%{$term}%")
              ->orWhere('position_title','like', "%{$term}%")
              ->orWhere('office',       'like', "%{$term}%");
        });
    }

    public function scopeByOffice($query, $office)
    {
        if (is_array($office)) {
            return $query->whereIn('office', $office);
        }
        return $query->where('office', 'like', "%{$office}%");
    }

    public function scopeByGender($query, string $gender)
    {
        return $query->where('gender', strtoupper($gender));
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function attachments()
    {
        return $this->hasMany(EmployeeAttachment::class, 'casual_employee_id');
    }
}
