<?php

namespace App\Support;

use App\Models\Applicant;
use App\Models\HrmpsbRatingScale;
use App\Models\InterviewEvaluation;
use App\Models\Position;
use App\Models\TwgRatingCriterion;
use App\Models\TwgScore;
use App\Models\TwgScoreHistory;
use App\Models\TwgScoreSubmission;
use App\Models\User;
use InvalidArgumentException;
use RuntimeException;

/**
 * Module 6.1-6.4 — the dynamic, criterion-driven TWG scoring engine,
 * additive alongside the existing fixed-column applicant_hrmpsb_scores/
 * interview_evaluations tables (see HrmpsbScoreController). Criteria live in
 * twg_rating_criteria (admin-editable, no deploy needed to add one); each
 * applicant gets one twg_scores row per active criterion.
 *
 * For bracket-based criteria (education/experience/training/awards/ipcr/
 * length_of_service), auto_populated_value is set by the assessor selecting
 * a qualifying tier from the existing HrmpsbRatingScale bracket data (JAF
 * source fields on Applicant are free-text, not clean numerics — silently
 * auto-extracting a score from them would risk a wrong score presented as
 * authoritative, so this app does not attempt that). psb_interview is the
 * one criterion computed genuinely automatically, from real numeric panel
 * interview_evaluations data.
 */
class TwgDynamicScoringService
{
    public const RECOMMENDATIONS = [
        'Highly Recommended', 'Recommended', 'Recommended with Reservation', 'Not Recommended',
    ];

    /**
     * Ensure a TwgScore row exists for every active criterion, and keep
     * psb_interview's auto-populated value fresh from the current panel
     * average (mirrors the old saveScore()'s "always compute PSB from panel
     * evaluations" behavior).
     */
    public function initialize(Applicant $applicant): \Illuminate\Support\Collection
    {
        $category = $this->positionCategory($applicant);

        $criteria = TwgRatingCriterion::where('is_active', true)
            ->whereIn('criterion_category', ['all', $category])
            ->orderBy('sort_order')
            ->get();

        foreach ($criteria as $criterion) {
            $score = TwgScore::firstOrCreate(
                ['applicant_id' => $applicant->id, 'criterion_id' => $criterion->id],
                ['item_no' => $applicant->item_no]
            );

            if ($criterion->criterion_key === 'psb_interview') {
                $this->refreshPsbInterview($score, $criterion, $applicant);
            }
        }

        return TwgScore::with('criterion')
            ->where('applicant_id', $applicant->id)
            ->whereHas('criterion', fn ($q) => $q->where('is_active', true)->whereIn('criterion_category', ['all', $category]))
            ->get()
            ->sortBy(fn ($s) => $s->criterion->sort_order)
            ->values();
    }

    /**
     * Same auto-detection HrmpsbScoreController::create() already uses, so
     * both scoring engines classify a given applicant identically.
     */
    public function positionCategory(Applicant $applicant): string
    {
        $hasEligibility = !empty($applicant->eligibility)
            && !in_array(strtolower(trim($applicant->eligibility)), ['n/a', 'none', 'not applicable', 'no', 'na']);
        $hasItemNo = !empty($applicant->item_no);

        return ($hasEligibility || $hasItemNo) ? 'eligibility' : 'no_eligibility';
    }

    /** SG 1-10 = First Level, SG 11+ = Second Level — same rule HrmpsbScoreController::create() uses. */
    public function educationLevel(Applicant $applicant): string
    {
        $position = Position::whereRaw('UPPER(title) = UPPER(?)', [$applicant->position_applied])->first();
        if ($position && is_numeric($position->salary_grade)) {
            return ((int) $position->salary_grade <= 10) ? 'first_level' : 'second_level';
        }

        return 'second_level';
    }

    /**
     * The qualifying HrmpsbRatingScale bracket rows an assessor may pick from
     * for a bracket-based criterion, matching the same filters the existing
     * fixed-column TWG form already applies (single source of bracket data).
     */
    public function bracketOptionsFor(TwgScore $score, Applicant $applicant): \Illuminate\Support\Collection
    {
        $key = $score->criterion->rating_scale_key;
        if (!$key) {
            return collect();
        }

        $category = $this->positionCategory($applicant);
        $query = HrmpsbRatingScale::where('criterion', $key);

        if ($key === 'education') {
            if ($category === 'no_eligibility') {
                $query->where('position_category', 'no_eligibility');
            } else {
                $query->where('level', $this->educationLevel($applicant));
            }
        } elseif ($key === 'ipcr') {
            $query->where('position_category', $category === 'no_eligibility' ? 'no_eligibility' : 'all');
        } else {
            $query->whereIn('position_category', ['all', $category]);
        }

        return $query->orderByDesc('points')->get();
    }

    private function refreshPsbInterview(TwgScore $score, TwgRatingCriterion $criterion, Applicant $applicant): void
    {
        $panelAvg = InterviewEvaluation::where('applicant_id', $applicant->id)->avg('total_score');
        $auto = $panelAvg !== null ? round($panelAvg * ((float) $criterion->point_value / 100), 2) : null;

        $wasSynced = !$score->is_edited; // not yet manually overridden
        $score->auto_populated_value = $auto;
        if ($wasSynced) {
            $score->assessor_value = $auto;
        }
        $score->save();
    }

    /**
     * The assessor selects a qualifying bracket (from HrmpsbRatingScale) for
     * a bracket-based criterion. This becomes the new auto-populated value;
     * if the assessor hasn't manually overridden the score yet, the assessor
     * value tracks it too (Module 6.2's "Initialize assessor_value=
     * auto_populated_value, is_edited=false").
     */
    public function setBracketSelection(TwgScore $score, float $bracketPoints): TwgScore
    {
        $max = (float) $score->criterion->point_value;
        if ($bracketPoints < 0 || $bracketPoints > $max) {
            throw new InvalidArgumentException("Exceeds maximum allowable score of {$max} points for this criterion.");
        }

        $wasSynced = !$score->is_edited;
        $score->auto_populated_value = $bracketPoints;
        if ($wasSynced) {
            $score->assessor_value = $bracketPoints;
        }
        $score->save();

        return $score->fresh('criterion');
    }

    /** Module 6.3 — the assessor's editable numeric override, with the exceedance block. */
    public function updateAssessorValue(TwgScore $score, float $value, ?string $notes, User $user): TwgScore
    {
        $max = (float) $score->criterion->point_value;
        if ($value < 0 || $value > $max) {
            throw new InvalidArgumentException("Exceeds maximum allowable score of {$max} points for this criterion.");
        }

        $score->assessor_value = $value;
        $score->assessor_notes = $notes;
        $score->is_edited = round($value, 2) !== round((float) ($score->auto_populated_value ?? 0), 2);
        $score->assessed_by = $user->id;
        $score->assessed_at = now();
        $score->save();

        return $score->fresh('criterion');
    }

    public function computeTotals(Applicant $applicant): array
    {
        $scores = TwgScore::with('criterion')
            ->where('applicant_id', $applicant->id)
            ->whereHas('criterion', fn ($q) => $q->where('is_active', true))
            ->get();

        $totalAuto = (float) $scores->sum('auto_populated_value');
        $totalAssessor = (float) $scores->sum('assessor_value');
        $maxPossible = (float) $scores->sum(fn ($s) => (float) $s->criterion->point_value);
        $percentage = $maxPossible > 0 ? round(($totalAssessor / $maxPossible) * 100, 2) : 0.0;

        return [
            'total_auto' => round($totalAuto, 2),
            'total_assessor' => round($totalAssessor, 2),
            'max_possible' => round($maxPossible, 2),
            'percentage' => $percentage,
            'adjectival_classification' => $this->classify($percentage),
            'edited_count' => $scores->where('is_edited', true)->count(),
        ];
    }

    /**
     * General indicative bands (not a specific cited CSC issuance for TWG
     * total-score classification — kept deliberately plain-language rather
     * than implying a legal citation this codebase can't verify).
     */
    public function classify(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'Outstanding',
            $percentage >= 80 => 'Very Satisfactory',
            $percentage >= 70 => 'Satisfactory',
            default => 'Below Satisfactory',
        };
    }

    /**
     * Module 6.4 — submit & lock. Requires: a valid photo (Module 7), the
     * certification checkbox, and every active criterion scored within range.
     */
    public function submit(Applicant $applicant, User $user, string $recommendation, ?string $overallNotes, bool $certificationAccepted): TwgScoreSubmission
    {
        if (!app(PhotoEnforcementService::class)->hasValidPhoto($applicant)) {
            throw new RuntimeException(PhotoEnforcementService::BLOCK_MESSAGE);
        }

        if (!$certificationAccepted) {
            throw new RuntimeException('You must certify that this evaluation was conducted in accordance with the approved TWG Rating Criteria before submitting.');
        }

        if (!in_array($recommendation, self::RECOMMENDATIONS, true)) {
            throw new InvalidArgumentException('Invalid assessor recommendation.');
        }

        $scores = TwgScore::with('criterion')
            ->where('applicant_id', $applicant->id)
            ->whereHas('criterion', fn ($q) => $q->where('is_active', true))
            ->get();

        foreach ($scores as $score) {
            if ($score->assessor_value === null) {
                throw new RuntimeException("The \"{$score->criterion->criterion_name}\" criterion has not been scored yet.");
            }
            if ((float) $score->assessor_value > (float) $score->criterion->point_value || (float) $score->assessor_value < 0) {
                throw new RuntimeException("The \"{$score->criterion->criterion_name}\" score exceeds its maximum allowable points.");
            }
        }

        $totals = $this->computeTotals($applicant);

        $submission = TwgScoreSubmission::updateOrCreate(
            ['applicant_id' => $applicant->id],
            [
                'total_auto' => $totals['total_auto'],
                'total_assessor' => $totals['total_assessor'],
                'percentage' => $totals['percentage'],
                'adjectival_classification' => $totals['adjectival_classification'],
                'recommendation' => $recommendation,
                'overall_notes' => $overallNotes,
                'certification_accepted' => true,
                'status' => 'submitted',
                'submitted_by' => $user->id,
                'submitted_at' => now(),
                'unlocked_by' => null,
                'unlocked_at' => null,
            ]
        );

        TwgScoreHistory::create([
            'applicant_id' => $applicant->id,
            'action' => 'submitted',
            'total_score' => $totals['total_assessor'],
            'performed_by' => $user->id,
        ]);

        return $submission;
    }

    /** Module 6.4 — only an HRMPSB Chairperson-equivalent (RBAC-gated in the controller) may unlock. */
    public function unlock(Applicant $applicant, User $user): TwgScoreSubmission
    {
        $submission = TwgScoreSubmission::where('applicant_id', $applicant->id)->first();
        if (!$submission || !$submission->isLocked()) {
            throw new RuntimeException('This evaluation is not currently locked.');
        }

        $submission->update([
            'status' => 'draft',
            'unlocked_by' => $user->id,
            'unlocked_at' => now(),
        ]);

        TwgScoreHistory::create([
            'applicant_id' => $applicant->id,
            'action' => 'unlocked',
            'total_score' => $submission->total_assessor,
            'performed_by' => $user->id,
        ]);

        return $submission->fresh();
    }
}
