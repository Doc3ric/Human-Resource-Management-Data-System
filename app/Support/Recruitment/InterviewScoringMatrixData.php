<?php

namespace App\Support\Recruitment;

use App\Models\Applicant;
use App\Models\InterviewEvaluation;
use App\Models\Setting;

/**
 * Shared data-prep for the HRMPSB Interview Scoring Matrix — used by both
 * the standalone matrix page (InterviewEvaluationController::matrix()) and
 * the Deliberation Workspace's embedded matrix panel, so the weights and
 * criteria labels can't drift apart between the two surfaces.
 */
class InterviewScoringMatrixData
{
    public static function forApplicant(Applicant $applicant): array
    {
        $evaluations = InterviewEvaluation::with(['rater.roles', 'panelMember'])
            ->where('applicant_id', $applicant->id)
            ->orderBy('created_at')
            ->get();

        $averageScore = $evaluations->count() > 0 ? $evaluations->avg('total_score') : 0;

        $weights = [
            'appearance'    => Setting::getVal('hrmpsb_weight_appearance', 5),
            'knowledge'     => Setting::getVal('hrmpsb_weight_knowledge', 50),
            'communication' => Setting::getVal('hrmpsb_weight_communication', 10),
            'other'         => Setting::getVal('hrmpsb_weight_other', 35),
        ];

        $criteriaLabels = [
            'appearance' => [
                'appearance_1' => 'The candidate presents himself/herself in a good grooming and tidy appearance.',
            ],
            'knowledge' => [
                'knowledge_1' => 'Knowledgeable of the functions of the vacant position.',
                'knowledge_2' => 'Knowledgeable of the organizational structure of the department/division.',
                'knowledge_3' => 'Knowledgeable of the mission and vision of the department/office.',
                'knowledge_4' => 'Can explain how the functions of the vacant position translate to the mission and vision.',
            ],
            'communication' => [
                'comm_1' => 'Listens to questions attentively and actively.',
                'comm_2' => 'Answers questions or expresses ideas clearly, concisely and logically.',
                'comm_3' => 'Demonstrates confidence by displaying positive body language and maintaining eye contact.',
            ],
            'other' => [
                'other_1'  => 'JOB COMMITMENT – Responsibility towards the mission and goals.',
                'other_2'  => 'COMMITMENT – Psychological attachment to the organization.',
                'other_3'  => 'POTENTIAL – Capability to perform duties of the position and higher ones.',
                'other_4'  => 'SINCERITY – Assessment of honesty, expression of valid/useful opinion.',
                'other_5'  => 'PROFESSIONALISM – Consideration, respect, loyalty, exceeds expectations.',
                'other_6'  => 'INITIATIVE – Eagerness to start actions without being told.',
                'other_7'  => 'TEAMWORK – Active involvement in a team resulting in goal achievement.',
                'other_8'  => 'TIME MANAGEMENT – Act of planning time spent to increase productivity.',
                'other_9'  => 'CUSTOMER SERVICE – Taking care of customers needs professionally.',
                'other_10' => 'JOB SATISFACTION – Contentment of current job and sense of accomplishment.',
            ],
        ];

        return compact('applicant', 'evaluations', 'averageScore', 'weights', 'criteriaLabels');
    }
}
