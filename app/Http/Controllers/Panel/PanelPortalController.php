<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\InterviewEvaluation;
use App\Models\ApplicantHrmpsbScore;
use App\Models\HrmpsbRatingScale;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelPortalController extends Controller
{
    private function member()
    {
        return Auth::guard('panel')->user();
    }

    public function home()
    {
        $member = $this->member();
        if ($member->isTwg()) {
            return redirect()->route('panel.twg.form');
        }
        return redirect()->route('panel.interview.form');
    }

    // ── HRMPSB Interview Evaluation ───────────────────────────────────────────

    public function interviewForm(Request $request)
    {
        $member = $this->member();

        $applicants = Applicant::all(['id', 'first_name', 'last_name', 'reference_no', 'position_applied', 'office'])
            ->map(function ($app) {
                $app->office           = preg_replace('/\s+/', ' ', trim(strtoupper(str_replace([',', '.00'], '', $app->office))));
                $app->position_applied = preg_replace('/\s+/', ' ', trim(strtoupper(str_replace([',', '.00'], '', $app->position_applied))));
                return $app;
            })
            ->unique(fn($a) => strtoupper($a->first_name . '|' . $a->last_name . '|' . $a->position_applied))
            ->sortBy('last_name')
            ->values();

        $offices   = $applicants->pluck('office')->filter()->unique()->sort()->values();
        $positions = $applicants->pluck('position_applied')->filter()->unique()->sort()->values();

        $weights = [
            'appearance'    => Setting::getVal('hrmpsb_weight_appearance', 5),
            'knowledge'     => Setting::getVal('hrmpsb_weight_knowledge', 50),
            'communication' => Setting::getVal('hrmpsb_weight_communication', 10),
            'other'         => Setting::getVal('hrmpsb_weight_other', 35),
        ];

        $applicant          = null;
        $existingEvaluation = null;
        if ($request->filled('applicant_id')) {
            $applicant = Applicant::find($request->applicant_id);
            if ($applicant) {
                $existingEvaluation = InterviewEvaluation::where('applicant_id', $applicant->id)
                    ->where('panel_member_id', $member->id)
                    ->first();
            }
        }

        $evaluatedIds = InterviewEvaluation::where('panel_member_id', $member->id)
            ->pluck('applicant_id')
            ->toArray();

        $totalApplicants = $applicants->count();
        $evaluatedCount  = $applicants->whereIn('id', $evaluatedIds)->count();

        $stats = [
            'total'     => $totalApplicants,
            'evaluated' => $evaluatedCount,
            'pending'   => $totalApplicants - $evaluatedCount,
        ];

        return view('panel.hrmpsb.interview', compact(
            'applicants', 'offices', 'positions', 'weights',
            'applicant', 'stats', 'existingEvaluation'
        ));
    }

    public function interviewStore(Request $request)
    {
        $member = $this->member();

        $validated = $request->validate([
            'applicant_id' => 'required|exists:applicants,id',
            'ratings'      => 'required|array',
            'remarks'      => 'nullable|string',
        ]);

        $applicant = Applicant::findOrFail($validated['applicant_id']);

        $wApp = Setting::getVal('hrmpsb_weight_appearance', 5);
        $wKno = Setting::getVal('hrmpsb_weight_knowledge', 50);
        $wCom = Setting::getVal('hrmpsb_weight_communication', 10);
        $wOth = Setting::getVal('hrmpsb_weight_other', 35);

        $r = $validated['ratings'];

        $appScore  = ((int)($r['appearance_1'] ?? 0) / 4) * $wApp;
        $knowSum   = (int)($r['knowledge_1'] ?? 0) + (int)($r['knowledge_2'] ?? 0) + (int)($r['knowledge_3'] ?? 0) + (int)($r['knowledge_4'] ?? 0);
        $knowScore = ($knowSum / 16) * $wKno;
        $commSum   = (int)($r['comm_1'] ?? 0) + (int)($r['comm_2'] ?? 0) + (int)($r['comm_3'] ?? 0);
        $commScore = ($commSum / 12) * $wCom;
        $othSum    = 0;
        for ($i = 1; $i <= 10; $i++) {
            $othSum += (int)($r['other_' . $i] ?? 0);
        }
        $othScore   = ($othSum / 40) * $wOth;
        $totalScore = $appScore + $knowScore + $commScore + $othScore;

        InterviewEvaluation::updateOrCreate(
            [
                'applicant_id'   => $applicant->id,
                'panel_member_id' => $member->id,
            ],
            [
                'rater_id'   => null,
                'rater_name' => $member->name,
                'office'     => $applicant->office,
                'position'   => $applicant->position_applied,
                'ratings'    => $r,
                'remarks'    => $validated['remarks'] ?? null,
                'total_score' => $totalScore,
            ]
        );

        return redirect()->route('panel.interview.form', ['applicant_id' => $applicant->id])
            ->with('success', 'Your interview evaluation for ' . $applicant->full_name . ' has been saved.');
    }

    // ── TWG Scoring ───────────────────────────────────────────────────────────

    public function twgForm(Request $request)
    {
        $applicants = Applicant::all(['id', 'first_name', 'last_name', 'reference_no', 'position_applied', 'office'])
            ->map(function ($app) {
                $app->office           = preg_replace('/\s+/', ' ', trim(strtoupper(str_replace([',', '.00'], '', $app->office))));
                $app->position_applied = preg_replace('/\s+/', ' ', trim(strtoupper(str_replace([',', '.00'], '', $app->position_applied))));
                return $app;
            })
            ->unique(fn($a) => strtoupper($a->first_name . '|' . $a->last_name . '|' . $a->position_applied))
            ->sortBy('last_name')
            ->values();

        $offices   = $applicants->pluck('office')->filter()->unique()->sort()->values();
        $positions = $applicants->pluck('position_applied')->filter()->unique()->sort()->values();

        $applicant = null;
        $score     = null;
        if ($request->filled('applicant_id')) {
            $applicant = Applicant::find($request->applicant_id);
            if ($applicant) {
                $score = $applicant->hrmpsbScore ?? new ApplicantHrmpsbScore();
            }
        }

        $evaluatedIds   = ApplicantHrmpsbScore::pluck('applicant_id')->toArray();
        $totalApplicants = $applicants->count();
        $evaluatedCount  = $applicants->whereIn('id', $evaluatedIds)->count();

        $stats = [
            'total'     => $totalApplicants,
            'evaluated' => $evaluatedCount,
            'pending'   => $totalApplicants - $evaluatedCount,
        ];

        return view('panel.twg.form', compact('applicants', 'offices', 'positions', 'applicant', 'score', 'stats'));
    }

    public function twgStore(Request $request)
    {
        $member = $this->member();

        $applicantId = $request->input('applicant_id');
        if (!$applicantId) {
            return back()->with('error', 'Please select an applicant.');
        }

        $applicant = Applicant::findOrFail($applicantId);

        $validated = $request->validate([
            'position_category'        => 'required|in:eligibility,no_eligibility',
            'psb_interview_score'      => 'nullable|numeric|max:50',
            'ipcr_score'               => 'nullable|numeric|max:10',
            'awards_score'             => 'nullable|numeric|max:5',
            'education_score'          => 'nullable|numeric|max:20',
            'experience_score'         => 'nullable|numeric|max:10',
            'training_score'           => 'nullable|numeric|max:10',
            'length_of_service_score'  => 'nullable|numeric|max:15',
            'demerits_deduction'       => 'nullable|numeric',
            'remarks'                  => 'nullable|string',
        ]);

        $total = collect([
            $validated['psb_interview_score']     ?? 0,
            $validated['ipcr_score']              ?? 0,
            $validated['awards_score']            ?? 0,
            $validated['education_score']         ?? 0,
            $validated['experience_score']        ?? 0,
            $validated['training_score']          ?? 0,
            $validated['length_of_service_score'] ?? 0,
        ])->sum() - ($validated['demerits_deduction'] ?? 0);

        $validated['total_score']  = max(0, $total);
        $validated['evaluated_by'] = null;

        ApplicantHrmpsbScore::updateOrCreate(
            ['applicant_id' => $applicantId],
            $validated
        );

        return redirect()->route('panel.twg.form', ['applicant_id' => $applicantId])
            ->with('success', 'HRMPSB scores saved for ' . $applicant->full_name . '.');
    }
}
