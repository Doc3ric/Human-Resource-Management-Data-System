<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PlantillaRecord extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'plantilla_records';

    protected $fillable = [
        'organizational_unit',
        'item',
        'position_title',
        'salary_grade',
        'authorized_annual_salary',
        'actual_annual_salary',
        'step',
        'area_code',
        'area_type',
        'level',
        'last_name',
        'first_name',
        'middle_name',
        'sex',
        'religion',
        'date_of_birth',
        'tin',
        'date_original_appointment',
        'date_last_promotion',
        'date_last_nolp',
        'employment_status',
        'civil_service_eligibility',
        'comment_annotation',
        'is_pwd',
        'type_of_disability',
        'indigenous_people',
        'solo_parent',
        'abolished',
        'dissolved',
        'gsis_bp_number',
        'position_classification',
        'umid',
        'is_vacant',
        'is_apprehended',
        'is_admin_charge',
        'apprehended_from',
        'admin_charge_from',
        'admin_charge_to',
        'admin_charge_type',
        'admin_charges',
        'lwop',
        'is_health_worker',
        'retired_at',
        'nature_of_appointment',
        'nature_of_separation',
        'date_separated',
        'loyalty_dismissed_at',
        'employee_code',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_original_appointment' => 'date',
        'date_last_promotion' => 'date',
        'date_last_nolp' => 'date',
        'retired_at' => 'date',
        'is_pwd' => 'boolean',
        'abolished' => 'boolean',
        'dissolved' => 'boolean',
        'is_vacant' => 'boolean',
        'is_apprehended' => 'boolean',
        'is_admin_charge' => 'boolean',
        'is_health_worker' => 'boolean',
        'apprehended_from' => 'date',
        'admin_charge_from' => 'date',
        'admin_charge_to' => 'date',
        'admin_charges' => 'array',
        'authorized_annual_salary' => 'decimal:2',
        'actual_annual_salary' => 'decimal:2',
        'date_separated' => 'date',
        'lwop' => 'integer',
        'loyalty_dismissed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "This plantilla record has been {$eventName}");
    }

    /**
     * Generate a unique employee code: DDMMYYYY + first letter of last name (uppercase).
     * If the base code already exists in this table, a numeric suffix is appended
     * (e.g. 01021980S → 01021980S2 → 01021980S3) until a free slot is found.
     * Returns null if either argument is missing.
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
            $query = static::withTrashed(); // check soft-deleted rows too
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
     * Full name: "LAST NAME, First Name Middle Name"
     */
    public function getFullNameAttribute(): string
    {
        if ($this->is_vacant)
            return 'VACANT';
        return trim(
            strtoupper($this->last_name ?? '') . ', ' .
            ($this->first_name ?? '') . ' ' .
            ($this->middle_name ?? '')
        );
    }

    /**
     * Years of service from date of original appointment.
     */
    public function getYearsOfServiceAttribute(): float
    {
        if (!$this->date_original_appointment)
            return 0;
        return $this->date_original_appointment->diffInYears(now());
    }

    /**
     * Determine if employee belongs to a Hospital or Medical organizational unit.
     */
    public function getIsHospitalPersonnelAttribute(): bool
    {
        if (empty($this->organizational_unit))
            return false;
        $ou = strtoupper($this->organizational_unit);
        return str_contains($ou, 'BPH') || str_contains($ou, 'HEALTH') || str_contains($ou, 'MEDICAL') || str_contains($ou, 'HOSPITAL');
    }

    /**
     * Date when next NOSI (Step Increment) is due (every 3 years = 1 step).
     */
    public function getNextNosiDueDateAttribute(): ?Carbon
    {
        if ($this->is_hospital_personnel)
            return null;
        if ($this->step >= 8)
            return null;
        if (!$this->date_original_appointment)
            return null;

        $baseDate = $this->date_last_promotion ?? $this->date_original_appointment;
        return $baseDate->copy()->addYears(3);
    }

    /**
     * Date when next NOLP (Longevity Pay) is due (every 5 years = 2 steps).
     * Only applies to Hospital/Medical personnel.
     */
    public function getNextNolpDueDateAttribute(): ?Carbon
    {
        if (!$this->is_hospital_personnel)
            return null;
        if ($this->step >= 8)
            return null;
        if (!$this->date_original_appointment)
            return null;

        $baseDate = $this->date_last_nolp ?? $this->date_original_appointment;
        return $baseDate->copy()->addYears(5);
    }

    /**
     * Returns true if either NOSI or NOLP are due today or in the past.
     */
    public function getIsStepDueAttribute(): bool
    {
        if ($this->is_vacant || $this->step >= 8)
            return false;

        $nosiDue = $this->next_nosi_due_date;
        $nolpDue = $this->next_nolp_due_date;

        if ($nosiDue && $nosiDue->lte(now()))
            return true;
        if ($nolpDue && $nolpDue->lte(now()))
            return true;

        return false;
    }

    /**
     * Return what the employee is due for (nosi, nolp, both, null).
     */
    public function getDueTypeAttribute(): ?string
    {
        if ($this->is_vacant || $this->step >= 8)
            return null;

        $nosiDue = $this->next_nosi_due_date;
        $nolpDue = $this->next_nolp_due_date;

        $isNosi = $nosiDue && $nosiDue->lte(now());
        $isNolp = $nolpDue && $nolpDue->lte(now());

        if ($isNosi && $isNolp)
            return 'both';
        if ($isNolp)
            return 'nolp';
        if ($isNosi)
            return 'nosi';
        return null;
    }

    /**
     * Returns the earliest upcoming step date (either NOSI or NOLP).
     */
    public function getNextStepDueDateAttribute(): ?Carbon
    {
        $nosiDue = $this->next_nosi_due_date;
        $nolpDue = $this->next_nolp_due_date;

        if ($nosiDue && $nolpDue) {
            return $nosiDue->lt($nolpDue) ? $nosiDue : $nolpDue;
        }

        return $nosiDue ?? $nolpDue;
    }

    /**
     * Formatted monthly salary (actual_annual_salary / 12).
     */
    public function getMonthlySalaryAttribute(): float
    {
        return round($this->actual_annual_salary / 12, 2);
    }

    /**
     * Age of the employee in years (null if no DOB).
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth)
            return null;
        return $this->date_of_birth->age;
    }

    /**
     * Whether this employee is overdue for compulsory retirement (age >= 65)
     * and the position hasn't been vacated yet.
     */
    public function getIsRetirementDueAttribute(): bool
    {
        if ($this->is_vacant || $this->retired_at)
            return false;
        return $this->age !== null && $this->age >= 65;
    }

    /**
     * Exact date when this employee turns/turned 65.
     */
    public function getRetirementDateAttribute(): ?Carbon
    {
        if (!$this->date_of_birth)
            return null;
        return $this->date_of_birth->copy()->addYears(65);
    }

    /** Step increment history logs for this record */
    public function stepIncrementHistories()
    {
        return $this->hasMany(StepIncrementHistory::class, 'plantilla_record_id')->orderBy('effective_date', 'desc');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    /** Only filled (non-vacant, non-abolished) records */
    public function scopeFilled($query)
    {
        return $query->where('is_vacant', false)
            ->where('abolished', false);
    }

    /** Only vacant records */
    public function scopeVacant($query)
    {
        return $query->where('is_vacant', true)->where('abolished', false);
    }

    /** Only abolished records */
    public function scopeAbolished($query)
    {
        return $query->where('abolished', true);
    }

    /** Filter by employment status (P, CT, E, Casual, JO, etc.) */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('employment_status', $status);
    }

    /** Filter by organizational unit (partial match) */
    public function scopeByOffice($query, string $office)
    {
        return $query->where('organizational_unit', 'like', "%{$office}%");
    }

    /** Employees overdue for compulsory retirement (age >= 65, not yet vacated) */
    public function scopeRetirementDue($query)
    {
        $cutoff = now()->subYears(65)->toDateString();
        return $query
            ->where('is_vacant', false)
            ->where('abolished', false)
            ->whereNotNull('date_of_birth')
            ->whereNull('retired_at')
            ->where('date_of_birth', '<=', $cutoff);
    }

    /** Employees turning 65 within the next N months */
    public function scopeNearRetirement($query, int $months = 12)
    {
        $start = now()->subYears(65)->addMonths(1)->toDateString();
        $end = now()->subYears(65)->addMonths($months)->toDateString();
        return $query
            ->where('is_vacant', false)
            ->where('abolished', false)
            ->whereNotNull('date_of_birth')
            ->whereNull('retired_at')
            ->whereBetween('date_of_birth', [$start, $end]);
    }

    /** Employees due for either NOSI (3 yrs, non-hospital) or NOLP (5 yrs, hospital) — Permanent only */
    public function scopeStepDue($query)
    {
        return $query
            ->filled() // not vacant, abolished
            ->where('is_apprehended', false)
            ->where('is_admin_charge', false)
            ->where('employment_status', 'P') // CSC rule: Permanent employees only
            ->where('step', '<', 8)
            ->whereNotNull('date_original_appointment')
            ->where(function ($q) {
                // NOSI: Non-hospital personnel, 3 years
                $threeYearsAgo = now()->subYears(3);
                $q->where(function ($nosiQuery) use ($threeYearsAgo) {
                    $nosiQuery->where(function ($sub) {
                        $sub->where('organizational_unit', 'not like', '%BPH%')
                            ->where('organizational_unit', 'not like', '%HEALTH%')
                            ->where('organizational_unit', 'not like', '%MEDICAL%')
                            ->where('organizational_unit', 'not like', '%HOSPITAL%');
                    })->where(function ($q2) use ($threeYearsAgo) {
                        $q2->where(function ($q3) use ($threeYearsAgo) {
                            $q3->whereNotNull('date_last_promotion')
                               ->where('date_last_promotion', '<=', $threeYearsAgo);
                        })->orWhere(function ($q3) use ($threeYearsAgo) {
                            $q3->whereNull('date_last_promotion')
                               ->where('date_original_appointment', '<=', $threeYearsAgo);
                        });
                    });
                });

                // NOLP: Hospital personnel, 5 years
                $fiveYearsAgo = now()->subYears(5);
                $q->orWhere(function ($nolpQuery) use ($fiveYearsAgo) {
                    $nolpQuery->where(function ($sub) {
                        $sub->where('organizational_unit', 'like', '%BPH%')
                            ->orWhere('organizational_unit', 'like', '%HEALTH%')
                            ->orWhere('organizational_unit', 'like', '%MEDICAL%')
                            ->orWhere('organizational_unit', 'like', '%HOSPITAL%');
                    })->where(function ($q2) use ($fiveYearsAgo) {
                        $q2->where(function ($q3) use ($fiveYearsAgo) {
                            $q3->whereNotNull('date_last_nolp')
                               ->where('date_last_nolp', '<=', $fiveYearsAgo);
                        })->orWhere(function ($q3) use ($fiveYearsAgo) {
                            $q3->whereNull('date_last_nolp')
                               ->where('date_original_appointment', '<=', $fiveYearsAgo);
                        });
                    });
                });
            });
    }

    /** Magna Carta: Health workers exactly 3 months away from their 65th birthday */
    public function scopeMagnaCartaNosaDue($query)
    {
        $targetMonth = now()->addMonths(3)->month;
        $targetYear = now()->addMonths(3)->year - 65;

        return $query->filled()
            ->where('is_health_worker', true)
            ->whereMonth('date_of_birth', $targetMonth)
            ->whereYear('date_of_birth', $targetYear);
    }

    /**
     * Get the list of file attachments for this record.
     */
    public function attachments()
    {
        return $this->hasMany(EmployeeAttachment::class);
    }
}
