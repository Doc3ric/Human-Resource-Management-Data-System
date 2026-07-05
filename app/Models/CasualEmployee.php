<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class CasualEmployee extends PlantillaRecord
{
    /**
     * Boot the model.
     */
    protected static function booted()
    {
        // Always filter by 'CASUAL' employment_status
        static::addGlobalScope('casual_status', function (Builder $builder) {
            $builder->where('employment_status', 'CASUAL');
        });

        // Auto-set the employment_status on creation
        static::creating(function ($model) {
            $model->employment_status = 'CASUAL';
        });
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "This casual employee record has been {$eventName}");
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('last_name',      'like', "%{$term}%")
              ->orWhere('first_name',   'like', "%{$term}%")
              ->orWhere('position_title','like', "%{$term}%")
              ->orWhere('office_department', 'like', "%{$term}%");
        });
    }

    public function scopeByOffice($query, $office)
    {
        if (is_array($office)) {
            return $query->whereIn('office_department', $office);
        }
        return $query->where('office_department', 'like', "%{$office}%");
    }

    public function scopeByGender($query, string $gender)
    {
        return $query->where('sex', strtoupper($gender));
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function attachments()
    {
        return $this->hasMany(EmployeeAttachment::class, 'plantilla_record_id');
    }
}
