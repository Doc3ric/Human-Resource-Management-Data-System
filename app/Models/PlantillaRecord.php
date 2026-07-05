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
        'office_department',
        'detailed_unit',
        'item_no_new',
        'position_title',
        'salary_grade',
        'authorized_annual_salary',
        'base_salary_amount',
        'salary_type',
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
        'date_original_appt_casual',
        'date_last_promotion',
        'date_last_nolp',
        'employment_status',
        'is_renewed',
        'renewal_period',
        'lifecycle_status',
        'lifecycle_effective_date',
        'lifecycle_basis',
        'civil_service_eligibility',
        'remarks_annotation',
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
        'name_extension', 'nature_of_work', 'nature_of_work_detail',
        'first_day_of_service', 'civil_status', 'address',
        'first_level_eligibility', 'second_level_eligibility', 'reemployment',
        'item_no_old', 'legislative_district', 'sg_proposed', 'step_proposed',
        'salary_proposed', 'increase_decrease', 'previous_rate',
        'spms_rating', 'office_code',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_original_appointment' => 'date',
        'date_last_promotion' => 'date',
        'date_last_nolp' => 'date',
        'retired_at' => 'date',
        'first_day_of_service' => 'date',
        'is_pwd' => 'boolean',
        'is_renewed' => 'boolean',
        'lifecycle_effective_date' => 'date',
        'abolished' => 'boolean',
        'dissolved' => 'boolean',
        'is_vacant' => 'boolean',
        'is_apprehended' => 'boolean',
        'is_admin_charge' => 'boolean',
        'is_health_worker' => 'boolean',
        'first_level_eligibility' => 'boolean',
        'second_level_eligibility' => 'boolean',
        'reemployment' => 'boolean',
        'admin_charges' => 'array',
        'apprehended_from' => 'date',
        'admin_charge_from' => 'date',
        'admin_charge_to' => 'date',
        'admin_charges' => 'array',
        'authorized_annual_salary' => 'decimal:2',
        'base_salary_amount' => 'decimal:2',
        'spms_rating' => 'decimal:2',
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
     * Generate a unique employee code: DDMMYYYY + first letter of First Name.
     * Collision resolution (in order):
     *   1. DDMMYYYY + F
     *   2. DDMMYYYY + F + first letter of Middle Name
     *   3. DDMMYYYY + F + M + first letter of Last Name
     *   4. Numeric suffix on #3 (e.g. 15081978CLC2) if all three are taken.
     *
     * @param int|null $excludeId  Exclude this record's own ID when checking (for updates).
     */
    public static function generateEmployeeCode(
        ?string $firstName,
        mixed   $dob,
        ?int    $excludeId = null,
        ?string $middleName = null,
        ?string $lastName   = null
    ): ?string {
        if (empty($firstName) || empty($dob)) {
            return null;
        }
        try {
            $date   = \Carbon\Carbon::parse($dob);
            $prefix = $date->format('dmY');
            $f      = strtoupper(substr(trim($firstName), 0, 1));
            $m      = $middleName ? strtoupper(substr(trim($middleName), 0, 1)) : '';
            $l      = $lastName   ? strtoupper(substr(trim($lastName),   0, 1)) : '';

            // Build candidates in priority order, skipping empty combinations
            $candidates = array_values(array_unique(array_filter([
                $prefix . $f,
                $m !== '' ? $prefix . $f . $m         : null,
                $m !== '' && $l !== '' ? $prefix . $f . $m . $l : null,
            ])));

            $query = static::withTrashed();
            foreach ($candidates as $candidate) {
                if (!$query->clone()->where('employee_code', $candidate)
                           ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                           ->exists()) {
                    return $candidate;
                }
            }

            // All letter-based candidates taken — numeric suffix on the longest one
            $base   = end($candidates);
            $suffix = 2;
            do {
                $candidate = $base . $suffix;
                $suffix++;
            } while ($query->clone()->where('employee_code', $candidate)
                           ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                           ->exists());

            return $candidate;
        } catch (\Exception) {
            return null;
        }
    }

    // ── Mutators (Dynamic Reference Management) ──────────────────────────────

    public function setPositionTitleAttribute($value)
    {
        $this->attributes['position_title'] = empty($value) ? null : strtoupper(trim($value));
    }

    public function setOrganizationalUnitAttribute($value)
    {
        $this->attributes['office_department'] = empty($value) ? null : strtoupper(trim($value));
    }

    public function setChargesAttribute($value)
    {
        $this->attributes['office_department'] = empty($value) ? null : strtoupper(trim($value));
    }

    public function setNatureOfWorkDetailAttribute($value)
    {
        $this->attributes['nature_of_work_detail'] = empty($value) ? null : strtoupper(trim($value));
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
        return (int) $this->date_original_appointment->diffInYears(now());
    }

    public function getMonthsOfServiceAttribute(): int
    {
        if (!$this->date_original_appointment) return 0;
        $years = $this->years_of_service;
        return (int) $this->date_original_appointment->copy()->addYears($years)->diffInMonths(now());
    }

    // Removed backwards compatibility accessors based on normalization plan

    // ── Helper ──────────────────────────────────────────────────────────────

    /**
     * Determine if employee belongs to a Hospital or Medical organizational unit.
     */
    public function getIsHospitalPersonnelAttribute(): bool
    {
        if (empty($this->office_department))
            return false;
        $ou = strtoupper($this->office_department);
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
     * Formatted monthly salary (base_salary_amount / 12).
     */
    public function getMonthlySalaryAttribute(): float
    {
        return round($this->base_salary_amount / 12, 2);
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

    /** Casual/JO/contractual employment_status values the renewal gate applies to (Module 1.1). */
    public const RENEWAL_GATED_STATUSES = ['CASUAL', 'Casual', 'Cas', 'C', 'JO', 'Job Order', 'J.O.', 'J'];

    public function scopeFilled($query)
    {
        return $query->where('is_vacant', false)
            ->where('abolished', false)
            ->whereNull('nature_of_separation')
            // Module 1B.1/1B.2 — only lifecycle_status=ACTIVE is "active for
            // reporting"; separated/retired/terminated/etc. never appear in
            // live queries even if not yet formally archived.
            ->where('lifecycle_status', 'ACTIVE')
            // Module 1.1 — casual/JO/contractual personnel excluded from live
            // reporting queries when not renewed for the active period.
            // Permanent/other statuses are untouched by this flag.
            ->where(function ($q) {
                $q->whereNotIn('employment_status', self::RENEWAL_GATED_STATUSES)
                  ->orWhere('is_renewed', true);
            });
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
        return $query->where('office_department', 'like', "%{$office}%");
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
                        $sub->where('office_department', 'not like', '%BPH%')
                            ->where('office_department', 'not like', '%HEALTH%')
                            ->where('office_department', 'not like', '%MEDICAL%')
                            ->where('office_department', 'not like', '%HOSPITAL%');
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
                        $sub->where('office_department', 'like', '%BPH%')
                            ->orWhere('office_department', 'like', '%HEALTH%')
                            ->orWhere('office_department', 'like', '%MEDICAL%')
                            ->orWhere('office_department', 'like', '%HOSPITAL%');
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

    public function contractRenewals()
    {
        return $this->hasMany(\App\Models\ContractRenewal::class);
    }

    public function latestRenewal()
    {
        return $this->hasOne(\App\Models\ContractRenewal::class)->latestOfMany();
    }

    public function ipcrRatings()
    {
        return $this->hasMany(\App\Models\IpcrRating::class, 'plantilla_record_id');
    }
}
