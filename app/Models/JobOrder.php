<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class JobOrder extends PlantillaRecord
{
    /**
     * Boot the model.
     */
    protected static function booted()
    {
        // Always filter by 'JO' employment_status
        static::addGlobalScope('jo_status', function (Builder $builder) {
            $builder->where('employment_status', 'JO');
        });

        // Auto-set the employment_status on creation
        static::creating(function ($model) {
            $model->employment_status = 'JO';
        });
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeByOffice($query, string $office)
    {
        return $query->where('detailed_unit', 'like', "%{$office}%");
    }

    public function scopeByCharges($query, string $charges)
    {
        return $query->where('office_department', 'like', "%{$charges}%");
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('last_name',  'like', "%{$term}%")
              ->orWhere('first_name', 'like', "%{$term}%")
              ->orWhere('position_title', 'like', "%{$term}%")
              ->orWhere('detailed_unit', 'like', "%{$term}%")
              ->orWhere('office_department', 'like', "%{$term}%");
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function attachments()
    {
        return $this->hasMany(EmployeeAttachment::class, 'plantilla_record_id');
    }
}
