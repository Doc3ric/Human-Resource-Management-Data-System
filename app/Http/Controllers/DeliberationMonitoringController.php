<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\HrmpsbPanelMember;
use App\Models\InterviewEvaluation;
use App\Models\TwgScore;
use App\Models\TwgScoreSubmission;
use App\Models\PreEvaluationResult;
use App\Support\BlindScoringId;
use Illuminate\Http\Request;

class DeliberationMonitoringController extends Controller
{
    public function status(Request $request, Applicant $applicant)
    {
        $position = $applicant->position_applied;

        $assignedMembers = HrmpsbPanelMember::with(['panelMember', 'user'])
            ->where('position_applied', $position)
            ->where('is_active', true)
            ->get();

        $applicants = Applicant::where('position_applied', $position)
            ->orderBy('last_name')
            ->get(['id', 'last_name', 'first_name', 'date_of_birth', 'item_no', 'deliberation_phase']);

        $applicantIds = $applicants->pluck('id');
        
        $twgScores = TwgScore::whereIn('applicant_id', $applicantIds)->get();
        $twgSubmissions = TwgScoreSubmission::whereIn('applicant_id', $applicantIds)->get()->keyBy('applicant_id');
        $hrmpsbEvaluations = InterviewEvaluation::whereIn('applicant_id', $applicantIds)->get();
        $preEvaluations = PreEvaluationResult::whereIn('applicant_id', $applicantIds)->get()->keyBy('applicant_id');

        $matrix = [];
        $membersData = [];

        // Also compile stage-level progress for the Dashboard Strip
        $stageProgress = [
            'screening' => ['completed' => 0, 'in_progress' => 0, 'not_started' => 0],
            'twg_evaluation' => ['completed' => 0, 'in_progress' => 0, 'not_started' => 0],
            'hrmpsb_deliberation' => ['completed' => 0, 'in_progress' => 0, 'not_started' => 0],
        ];

        // 1. Screening Progress
        foreach ($applicants as $app) {
            if ($preEvaluations->has($app->id)) {
                $stageProgress['screening']['completed']++;
            } else {
                $stageProgress['screening']['not_started']++;
            }
        }

        // 2. Member Matrix (TWG & HRMPSB)
        foreach ($assignedMembers as $memberLink) {
            $name = $memberLink->panelMember ? $memberLink->panelMember->name : ($memberLink->user ? $memberLink->user->name : 'Unknown');
            $type = $memberLink->panelMember ? $memberLink->panelMember->type : 'twg';
            $memberKey = 'member_' . $memberLink->id;

            $membersData[] = [
                'id' => $memberKey,
                'name' => $name,
                'type' => strtoupper($type),
                'role' => $memberLink->member_role
            ];

            $matrix[$memberKey] = [];

            foreach ($applicants as $app) {
                $appId = $app->id;
                $state = 'Not Started';

                if ($type === 'hrmpsb' && $memberLink->panel_member_id) {
                    $hasEvaluated = $hrmpsbEvaluations->where('applicant_id', $appId)
                                                      ->where('panel_member_id', $memberLink->panel_member_id)
                                                      ->first();
                    if ($hasEvaluated) {
                        $state = 'Scored';
                        $stageProgress['hrmpsb_deliberation']['completed']++;
                    } else {
                        $stageProgress['hrmpsb_deliberation']['not_started']++;
                    }
                } else {
                    if ($memberLink->user_id) {
                        $hasAssessed = $twgScores->where('applicant_id', $appId)
                                                 ->where('assessed_by', $memberLink->user_id)
                                                 ->isNotEmpty();
                        
                        $isLocked = $twgSubmissions->has($appId) && $twgSubmissions[$appId]->status === 'submitted';

                        if ($hasAssessed && $isLocked) {
                            $state = 'Scored';
                            $stageProgress['twg_evaluation']['completed']++;
                        } elseif ($hasAssessed) {
                            $state = 'In Progress';
                            $stageProgress['twg_evaluation']['in_progress']++;
                        } else {
                            $stageProgress['twg_evaluation']['not_started']++;
                        }
                    }
                }

                $matrix[$memberKey][$appId] = $state;
            }
        }

        $applicantsData = $applicants->map(function ($app) {
            return [
                'id' => $app->id,
                'masked_id' => BlindScoringId::forApplicant($app),
                'phase' => $app->deliberation_phase
            ];
        });

        return response()->json([
            'position' => $position,
            'members' => $membersData,
            'applicants' => $applicantsData,
            'matrix' => $matrix,
            'progress' => $stageProgress,
        ]);
    }
}
