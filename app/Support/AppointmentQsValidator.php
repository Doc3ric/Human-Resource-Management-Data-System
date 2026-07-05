<?php

namespace App\Support;

use App\Models\Applicant;
use App\Models\Position;

class AppointmentQsValidator
{
    /**
     * Validate an applicant's or employee's credentials against a position's Qualification Standards (CSC per position).
     * 
     * @param Applicant|Employee|mixed $person
     * @param Position|mixed $position
     * @return array
     */
    public function validate($person, $position): array
    {
        // For now, assuming $position has QS properties or a related qualification_standard model
        // We will default to assuming the standard CSC fields exist either on Position or a related QualificationStandard.
        $qs = $position->qualificationStandard ?? null;

        $results = [
            'education' => ['status' => 'pending', 'message' => 'No QS defined'],
            'experience' => ['status' => 'pending', 'message' => 'No QS defined'],
            'training' => ['status' => 'pending', 'message' => 'No QS defined'],
            'eligibility' => ['status' => 'pending', 'message' => 'No QS defined'],
            'is_qualified' => false,
        ];

        if (!$qs) {
            return $results;
        }

        // 1. Validate Education
        $applicantEducationLevel = $this->getHighestEducationLevel($person);
        if ($this->meetsEducationReq($applicantEducationLevel, $qs->education_requirement)) {
            $results['education'] = ['status' => 'passed', 'message' => 'Meets education requirement.'];
        } else {
            $results['education'] = ['status' => 'failed', 'message' => 'Does not meet education requirement.'];
        }

        // 2. Validate Experience
        $applicantExperienceMonths = $this->getTotalRelevantExperienceMonths($person);
        $reqExperienceMonths = $qs->experience_months_requirement ?? 0;
        if ($applicantExperienceMonths >= $reqExperienceMonths) {
            $results['experience'] = ['status' => 'passed', 'message' => "Meets experience requirement ({$applicantExperienceMonths} / {$reqExperienceMonths} mos)."];
        } else {
            $results['experience'] = ['status' => 'failed', 'message' => "Does not meet experience requirement ({$applicantExperienceMonths} / {$reqExperienceMonths} mos)."];
        }

        // 3. Validate Training
        $applicantTrainingHours = $this->getTotalRelevantTrainingHours($person);
        $reqTrainingHours = $qs->training_hours_requirement ?? 0;
        if ($applicantTrainingHours >= $reqTrainingHours) {
            $results['training'] = ['status' => 'passed', 'message' => "Meets training requirement ({$applicantTrainingHours} / {$reqTrainingHours} hrs)."];
        } else {
            $results['training'] = ['status' => 'failed', 'message' => "Does not meet training requirement ({$applicantTrainingHours} / {$reqTrainingHours} hrs)."];
        }

        // 4. Validate Eligibility
        if ($this->meetsEligibilityReq($person, $qs->eligibility_requirement)) {
            $results['eligibility'] = ['status' => 'passed', 'message' => 'Meets eligibility requirement.'];
        } else {
            $results['eligibility'] = ['status' => 'failed', 'message' => 'Does not meet eligibility requirement.'];
        }

        // Overall qualification
        $results['is_qualified'] = (
            $results['education']['status'] === 'passed' &&
            $results['experience']['status'] === 'passed' &&
            $results['training']['status'] === 'passed' &&
            $results['eligibility']['status'] === 'passed'
        );

        return $results;
    }

    private function getHighestEducationLevel($person): int
    {
        // Helper to map degree types to level integer.
        // E.g., High School = 1, College = 2, Masters = 3, Doctorate = 4
        $level = 0;
        $education = $person->education ?? collect();
        foreach ($education as $edu) {
            $lvl = match(strtolower($edu->degree)) {
                'doctorate' => 4,
                'masters', 'master\'s degree' => 3,
                'bachelor', 'bachelor\'s degree', 'college graduate' => 2,
                'high school', 'high school graduate' => 1,
                default => 0,
            };
            if ($lvl > $level) $level = $lvl;
        }
        return $level;
    }

    private function meetsEducationReq(int $applicantLevel, ?string $reqString): bool
    {
        if (!$reqString || $reqString === 'None required') return true;
        
        $reqLevel = match(strtolower($reqString)) {
            'doctorate' => 4,
            'master\'s degree' => 3,
            'bachelor\'s degree' => 2,
            'high school graduate' => 1,
            default => 0,
        };

        return $applicantLevel >= $reqLevel;
    }

    private function getTotalRelevantExperienceMonths($person): int
    {
        $months = 0;
        $experience = $person->experience ?? collect();
        foreach ($experience as $exp) {
            // Assume relevant for now unless explicitly mapped
            $start = \Carbon\Carbon::parse($exp->date_from);
            $end = \Carbon\Carbon::parse($exp->date_to ?? now());
            $months += $start->diffInMonths($end);
        }
        return $months;
    }

    private function getTotalRelevantTrainingHours($person): int
    {
        return ($person->training ?? collect())->sum('number_of_hours');
    }

    private function meetsEligibilityReq($person, ?string $reqEligibility): bool
    {
        if (!$reqEligibility || $reqEligibility === 'None required') return true;
        
        // E.g. "Career Service (Professional)"
        $eligibility = $person->eligibility ?? collect();
        foreach ($eligibility as $el) {
            if (stripos($el->license_name, $reqEligibility) !== false || 
                stripos($reqEligibility, $el->license_name) !== false) {
                return true;
            }
        }
        return false;
    }
}
