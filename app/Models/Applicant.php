<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        // Identity
        'reference_no', 'ain',
        'applied_at', 'photo_url', 'jaf_url',
        'photo_source', 'photo_uploaded_at', 'photo_confirmed',
        'application_letter_document_id', 'application_letter_override', 'application_letter_override_reason',

        // Personal
        'last_name', 'first_name', 'middle_name', 'name_extension',
        'sex', 'date_of_birth', 'religion', 'indigenous_people',
        'is_pwd', 'phone_number', 'address', 'email_address',

        // Current PGB employment
        'is_pgb_employee', 'pgb_status', 'length_of_service',
        'current_position', 'years_in_present_position',
        'years_permanent', 'years_coterminous', 'years_casual', 'years_job_order',

        // Non-PGB work experience
        'has_non_pgb_employment', 'np_employment_status',
        'np_employer', 'np_designation', 'np_period',

        // Vacancy applied for
        'position_applied', 'item_no', 'office', 'salary_grade_snapshot',

        // Education
        'highest_educational_attainment', 'degree', 'tor_url',

        // Eligibility
        'eligibility', 'eligibility_url',

        // Training
        'training_url', 'training_hours',

        // Work experience
        'experience_url', 'responsibilities',

        // Performance
        'performance_rating', 'performance_form_url',

        // Awards & competencies
        'award', 'award_certificate_url', 'competencies',

        // Declaration
        'acknowledged',

        // Module 5 — deliberation workspace / exam routing
        'deliberation_phase', 'is_exam_exempt',

        // Module 2.2/2.3 — recruitment-record lifecycle
        'is_filled', 'date_filled', 'lifecycle_stage',
    ];

    protected $casts = [
        'date_of_birth'          => 'date',
        'applied_at'             => 'datetime',
        'is_pgb_employee'        => 'boolean',
        'is_pwd'                => 'boolean',
        'has_non_pgb_employment' => 'boolean',
        'acknowledged'           => 'boolean',
        'is_exam_exempt'         => 'boolean',
        'application_letter_override' => 'boolean',
        'photo_confirmed'        => 'boolean',
        'photo_uploaded_at'      => 'datetime',
        'is_filled'              => 'boolean',
        'date_filled'            => 'date',
    ];

    public static function generateAin($dateOfBirth, $firstName, $middleName = null, $ignoreId = null)
    {
        $fn = preg_replace('/[^A-Z]/', '', strtoupper($firstName));
        $mn = preg_replace('/[^A-Z]/', '', strtoupper($middleName ?? ''));
        
        $dob = \Carbon\Carbon::parse($dateOfBirth)->format('dmY');

        $firstInitial = substr($fn, 0, 1);
        $middleInitial = substr($mn, 0, 1);

        $baseAin = $dob . $firstInitial;

        $query = self::where('ain', $baseAin);
        if ($ignoreId) $query->where('id', '!=', $ignoreId);
        
        if (!$query->exists()) {
            return $baseAin;
        }

        if ($middleInitial) {
            $level1Ain = $baseAin . $middleInitial;
            $query = self::where('ain', $level1Ain);
            if ($ignoreId) $query->where('id', '!=', $ignoreId);
            
            if (!$query->exists()) {
                return $level1Ain;
            }
            $baseToIncrement = $level1Ain;
        } else {
            $baseToIncrement = $baseAin;
        }

        $suffix = 'A';
        while (true) {
            $level2Ain = $baseToIncrement . $suffix;
            $query = self::where('ain', $level2Ain);
            if ($ignoreId) $query->where('id', '!=', $ignoreId);
            
            if (!$query->exists()) {
                return $level2Ain;
            }
            $suffix++;
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($applicant) {
            $applicant->ain = self::generateAin($applicant->date_of_birth, $applicant->first_name, $applicant->middle_name);
        });
    }

    public function getFullNameAttribute()
    {
        $name = $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . substr($this->middle_name, 0, 1) . '.';
        }
        $name .= ' ' . $this->last_name;
        if ($this->name_extension) {
            $name .= ' ' . $this->name_extension;
        }
        return strtoupper($name);
    }

    public function evaluation()
    {
        return $this->hasOne(ApplicantEvaluation::class);
    }

    public function hrmpsbScore()
    {
        return $this->hasOne(ApplicantHrmpsbScore::class);
    }

    /**
     * Convert any Google Drive sharing URL to a direct embeddable image URL.
     * Supports: /open?id=, /file/d/ID/view, /uc?id=, /uc?export=view&id=
     */
    public function getPhotoEmbedUrlAttribute(): ?string
    {
        $url = $this->photo_url;
        if (!$url) return null;

        $id = null;

        // Format: open?id=FILE_ID or uc?id=FILE_ID or uc?export=view&id=FILE_ID
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $m)) {
            $id = $m[1];
        }
        // Format: /file/d/FILE_ID/
        elseif (preg_match('~/file/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
            $id = $m[1];
        }
        // Format: /d/FILE_ID/ (shortened)
        elseif (preg_match('~/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
            $id = $m[1];
        }

        return $id
            ? "https://drive.google.com/uc?export=view&id={$id}"
            : $url;
    }

    /** Module 4.2/9A.6 — the application letter, captured via the IDCC pipeline like any other attachment. */
    public function applicationLetterDocument()
    {
        return $this->belongsTo(Document::class, 'application_letter_document_id');
    }
}
