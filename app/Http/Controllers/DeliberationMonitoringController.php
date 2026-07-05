<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\HrmpsbPanelMember;
use App\Models\InterviewEvaluation;
use App\Models\TwgScore;
use App\Models\TwgScoreSubmission;
use App\Support\BlindScoringId;
use Illuminate\Http\Request;

class DeliberationMonitoringController extends Controller
{
    public function status(Request $request, Applicant $applicant)
    {
        $position = $applicant->position_applied;

        // 1. Get all assigned panel members for this position
        $assignedMembers = HrmpsbPanelMember::with(['panelMember', 'user'])
            ->where('position_applied', $position)
            ->where('is_active', true)
            ->get();

        // 2. Get all applicants for this position
        // date_of_birth + item_no are required by BlindScoringId::forApplicant()
        // below — without them every applicant would mask to the same fallback ID.
        $applicants = Applicant::where('position_applied', $position)
            ->orderBy('last_name')
            ->get(['id', 'last_name', 'first_name', 'date_of_birth', 'item_no']);

        // Pre-fetch scores to avoid N+1
        $applicantIds = $applicants->pluck('id');
        
        $twgScores = TwgScore::whereIn('applicant_id', $applicantIds)->get();
        $twgSubmissions = TwgScoreSubmission::whereIn('applicant_id', $applicantIds)->get()->keyBy('applicant_id');
        $hrmpsbEvaluations = InterviewEvaluation::whereIn('applicant_id', $applicantIds)->get();

        $matrix = [];
        $membersData = [];

        foreach ($assignedMembers as $memberLink) {
            // Determine name and type
            $name = $memberLink->panelMember ? $memberLink->panelMember->name : ($memberLink->user ? $memberLink->user->name : 'Unknown');
            // If they are a PanelMember, their type is 'hrmpsb' or 'twg'. If user, assume 'twg' for fallback.
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
                    }
                } else {
                    // TWG evaluation via user_id
                    if ($memberLink->user_id) {
                        $hasAssessed = $twgScores->where('applicant_id', $appId)
                                                 ->where('assessed_by', $memberLink->user_id)
                                                 ->isNotEmpty();
                        
                        $isLocked = $twgSubmissions->has($appId) && $twgSubmissions[$appId]->status === 'submitted';

                        if ($hasAssessed && $isLocked) {
                            $state = 'Scored';
                        } elseif ($hasAssessed) {
                            $state = 'In Progress';
                        }
                    }
                }

                $matrix[$memberKey][$appId] = $state;
            }
        }

        // Module 6A.5 — never raw names, states only. The masked ID is the
        // ONLY identifier this endpoint may return; a raw name here would
        // leak identity into a board every panel member can see mid-deliberation.
        $applicantsData = $applicants->map(function ($app) {
            return [
                'id' => $app->id,
                'masked_id' => BlindScoringId::forApplicant($app),
            ];
        });

        return response()->json([
            'position' => $position,
            'members' => $membersData,
            'applicants' => $applicantsData,
            'matrix' => $matrix,
        ]);
    }
}
