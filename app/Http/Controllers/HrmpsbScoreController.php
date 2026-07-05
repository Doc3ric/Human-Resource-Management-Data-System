<?php

namespace App\Http\Controllers;

use App\Models\HrmpsbRatingScale;
use App\Models\ApplicantHrmpsbScore;
use App\Models\Applicant;
use App\Models\InterviewEvaluation;
use App\Models\Position;
use Illuminate\Http\Request;

class HrmpsbScoreController extends Controller
{
    /**
     * Display the settings for HRMPSB Rating Scales and all configurable weights.
     */
    public function settings()
    {
        $scales = HrmpsbRatingScale::orderByRaw("FIELD(criterion,'ipcr','awards','education','experience','training','length_of_service','demerits_suspension','demerits_reprimand','demerits_stern_warning','demerits_warning_memo')")
            ->orderBy('points', 'desc')
            ->get()
            ->groupBy('criterion');

        $panelWeights = [
            'appearance'    => \App\Models\Setting::getVal('hrmpsb_weight_appearance', 5),
            'knowledge'     => \App\Models\Setting::getVal('hrmpsb_weight_knowledge', 50),
            'communication' => \App\Models\Setting::getVal('hrmpsb_weight_communication', 10),
            'other'         => \App\Models\Setting::getVal('hrmpsb_weight_other', 35),
        ];

        $twgWeights = [
            'psb_interview'               => \App\Models\Setting::getVal('twg_max_psb_interview', 50),
            'ipcr'                        => \App\Models\Setting::getVal('twg_max_ipcr', 10),
            'awards'                      => \App\Models\Setting::getVal('twg_max_awards', 5),
            'education_eligibility'       => \App\Models\Setting::getVal('twg_max_education_eligibility', 15),
            'education_no_eligibility'    => \App\Models\Setting::getVal('twg_max_education_no_eligibility', 20),
            'experience'                  => \App\Models\Setting::getVal('twg_max_experience', 10),
            'training'                    => \App\Models\Setting::getVal('twg_max_training', 10),
            'length_of_service'           => \App\Models\Setting::getVal('twg_max_length_of_service', 15),
        ];

        $legislative = \App\Models\HrmpsbSignatory::where('team', 'legislative')->get();
        $management = \App\Models\HrmpsbSignatory::where('team', 'management')->get();

        return view('hrmpsb.settings', compact('scales', 'panelWeights', 'twgWeights', 'legislative', 'management'));
    }

    /**
     * Save TWG criterion max points.
     */
    public function storeTwgWeights(Request $request)
    {
        $request->validate([
            'twg_max_psb_interview'            => 'required|numeric|min:0|max:100',
            'twg_max_ipcr'                     => 'required|numeric|min:0|max:100',
            'twg_max_awards'                   => 'required|numeric|min:0|max:100',
            'twg_max_education_eligibility'    => 'required|numeric|min:0|max:100',
            'twg_max_education_no_eligibility' => 'required|numeric|min:0|max:100',
            'twg_max_experience'               => 'required|numeric|min:0|max:100',
            'twg_max_training'                 => 'required|numeric|min:0|max:100',
            'twg_max_length_of_service'        => 'required|numeric|min:0|max:100',
        ]);

        foreach ($request->only([
            'twg_max_psb_interview','twg_max_ipcr','twg_max_awards',
            'twg_max_education_eligibility','twg_max_education_no_eligibility',
            'twg_max_experience','twg_max_training','twg_max_length_of_service',
        ]) as $key => $value) {
            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'TWG criterion weights updated.');
    }

    /**
     * Store or update a rating scale.
     */
    public function storeScale(Request $request)
    {
        $validated = $request->validate([
            'position_category' => 'required|in:eligibility,no_eligibility,all',
            'criterion' => 'required|string',
            'level' => 'nullable|string',
            'condition_name' => 'nullable|string',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'points' => 'required|numeric'
        ]);

        HrmpsbRatingScale::create($validated);

        return back()->with('success', 'Rating scale added successfully.');
    }

    /**
     * Delete a rating scale.
     */
    public function destroyScale(HrmpsbRatingScale $scale)
    {
        $scale->delete();
        return back()->with('success', 'Rating scale removed.');
    }

    /**
     * Return the PSB interview score (panel avg × 0.50) for a given applicant as JSON.
     */
    public function psbScoreJson($applicantId)
    {
        $evals = InterviewEvaluation::where('applicant_id', $applicantId)->get();
        $count = $evals->count();
        $psb   = $count > 0 ? round($evals->avg('total_score') * 0.50, 2) : null;
        return response()->json(['psb_score' => $psb, 'panel_count' => $count]);
    }

    /**
     * Copy credential scores from one applicant record to selected sibling records.
     * PSB score is NOT copied — it is re-fetched from each target's panel evaluations.
     */
    public function copyScore(Request $request)
    {
        $request->validate([
            'source_applicant_id'   => 'required|exists:applicants,id',
            'target_applicant_ids'  => 'required|array|min:1',
            'target_applicant_ids.*'=> 'exists:applicants,id',
        ]);

        $source         = ApplicantHrmpsbScore::where('applicant_id', $request->source_applicant_id)->firstOrFail();
        $sourceApplicant = Applicant::findOrFail($request->source_applicant_id);

        $copied = 0;
        foreach ($request->target_applicant_ids as $targetId) {
            $target = Applicant::find($targetId);
            if (!$target || $target->ain !== $sourceApplicant->ain) continue;

            $targetPanelAvg = InterviewEvaluation::where('applicant_id', $targetId)->avg('total_score');
            $targetPsb      = $targetPanelAvg !== null ? round($targetPanelAvg * 0.50, 2) : null;

            $data = [
                'position_category'       => $source->position_category,
                'education_level'         => $source->education_level,
                'psb_interview_score'     => $targetPsb,
                'ipcr_score'              => $source->ipcr_score,
                'awards_score'            => $source->awards_score,
                'education_score'         => $source->education_score,
                'experience_score'        => $source->experience_score,
                'training_score'          => $source->training_score,
                'length_of_service_score' => $source->length_of_service_score,
                'demerits_deduction'      => $source->demerits_deduction,
                'remarks'                 => $source->remarks,
                'evaluated_by'            => auth()->id(),
            ];

            $total = collect([
                $data['psb_interview_score']     ?? 0,
                $data['ipcr_score']              ?? 0,
                $data['awards_score']            ?? 0,
                $data['education_score']         ?? 0,
                $data['experience_score']        ?? 0,
                $data['training_score']          ?? 0,
                $data['length_of_service_score'] ?? 0,
            ])->sum() - ($data['demerits_deduction'] ?? 0);

            $data['total_score'] = max(0, $total);

            ApplicantHrmpsbScore::updateOrCreate(['applicant_id' => $targetId], $data);
            $copied++;
        }

        return back()->with('success', "Credential scores copied to {$copied} other application(s) successfully.");
    }

    /**
     * Save the applicant's HRMPSB score.
     */
    public function saveScore(Request $request, $applicantId)
    {
        $applicant = Applicant::findOrFail($applicantId);

        // Module 7 — scoring is blocked without a valid attached photo.
        if (!app(\App\Support\PhotoEnforcementService::class)->hasValidPhoto($applicant)) {
            return back()->withErrors(['photo' => \App\Support\PhotoEnforcementService::BLOCK_MESSAGE])->withInput();
        }

        $eduMax = $request->position_category === 'no_eligibility'
            ? \App\Models\Setting::getVal('twg_max_education_no_eligibility', 20)
            : \App\Models\Setting::getVal('twg_max_education_eligibility', 15);

        $validated = $request->validate([
            'position_category'       => 'required|in:eligibility,no_eligibility',
            'education_level'         => 'nullable|in:first_level,second_level',
            'psb_interview_score'     => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_psb_interview', 50),
            'ipcr_score'              => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_ipcr', 10),
            'awards_score'            => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_awards', 5),
            'education_score'         => 'nullable|numeric|min:0|max:' . $eduMax,
            'experience_score'        => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_experience', 10),
            'training_score'          => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_training', 10),
            'length_of_service_score' => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_length_of_service', 15),
            'demerits_deduction'      => 'nullable|numeric|min:0',
            'remarks'                 => 'nullable|string',
        ]);

        // Always compute PSB from panel evaluations (not from submitted value)
        $panelAvg = InterviewEvaluation::where('applicant_id', $applicantId)->avg('total_score');
        $validated['psb_interview_score'] = $panelAvg !== null ? round($panelAvg * 0.50, 2) : null;

        // Calculate total
        $total = collect([
            $validated['psb_interview_score'] ?? 0,
            $validated['ipcr_score'] ?? 0,
            $validated['awards_score'] ?? 0,
            $validated['education_score'] ?? 0,
            $validated['experience_score'] ?? 0,
            $validated['training_score'] ?? 0,
            $validated['length_of_service_score'] ?? 0
        ])->sum() - ($validated['demerits_deduction'] ?? 0);

        $validated['total_score'] = max(0, $total);
        $validated['evaluated_by'] = auth()->id();

        ApplicantHrmpsbScore::updateOrCreate(
            ['applicant_id' => $applicantId],
            $validated
        );

        return back()->with('success', 'HRMPSB Scores saved successfully.');
    }
    /**
     * Show the TWG standalone create form.
     */
    public function create()
    {
        $applicants = Applicant::all(['id', 'first_name', 'last_name', 'reference_no', 'position_applied', 'office'])
            ->map(function ($app) {
                $app->office = preg_replace('/\s+/', ' ', trim(strtoupper(str_replace([',', '.00'], '', $app->office))));
                $app->position_applied = preg_replace('/\s+/', ' ', trim(strtoupper(str_replace([',', '.00'], '', $app->position_applied))));
                return $app;
            })
            ->unique(function ($app) {
                return strtoupper($app->first_name . '|' . $app->last_name . '|' . $app->position_applied);
            })
            ->sortBy('last_name')
            ->values();

        $offices = $applicants->pluck('office')->filter()->unique()->sort()->values();
        $positions = $applicants->pluck('position_applied')->filter()->unique()->sort()->values();
        
        $applicant    = null;
        $score        = null;
        $psbFromPanel = null;
        $panelCount   = 0;
        $autoCategory = 'eligibility';
        $autoEduLevel = 'second_level';
        $copyTargets  = collect();

        if (request()->has('applicant_id') && request()->applicant_id) {
            $applicant  = \App\Models\Applicant::find(request()->applicant_id);
            $score      = $applicant->hrmpsbScore ?? new \App\Models\ApplicantHrmpsbScore();

            // PSB auto-fetch from panel evaluations
            $evals      = InterviewEvaluation::where('applicant_id', $applicant->id)->get();
            $panelCount = $evals->count();
            if ($panelCount > 0) {
                $psbFromPanel = round($evals->avg('total_score') * 0.50, 2);
            }

            // Auto-detect position category (With/Without Eligibility)
            $hasEligibility = !empty($applicant->eligibility)
                && !in_array(strtolower(trim($applicant->eligibility)), ['n/a', 'none', 'not applicable', 'no', 'na']);
            $hasItemNo  = !empty($applicant->item_no);
            $autoCategory = ($hasEligibility || $hasItemNo) ? 'eligibility' : 'no_eligibility';

            // Auto-detect education level from position salary grade (CSC 2025 QS)
            // SG 1-10 = First Level; SG 11+ = Second Level
            $posRec = Position::whereRaw('UPPER(title) = UPPER(?)', [$applicant->position_applied])->first();
            if ($posRec && is_numeric($posRec->salary_grade)) {
                $autoEduLevel = ((int)$posRec->salary_grade <= 10) ? 'first_level' : 'second_level';
            }

            // Sibling applications by same AIN (same person, different position)
            $copyTargets = \App\Models\Applicant::where('ain', $applicant->ain)
                ->where('id', '!=', $applicant->id)
                ->with('hrmpsbScore')
                ->get();
        }

        // Calculate Statistics
        $totalApplicants = $applicants->count();
        $evaluatedApplicantsIds = \App\Models\ApplicantHrmpsbScore::pluck('applicant_id')->toArray();
        $evaluatedCount = $applicants->whereIn('id', $evaluatedApplicantsIds)->count();
        $pendingCount = $totalApplicants - $evaluatedCount;

        $stats = [
            'total' => $totalApplicants,
            'evaluated' => $evaluatedCount,
            'pending' => $pendingCount
        ];

        return view('hrmpsb.twg.create', compact(
            'applicants', 'offices', 'positions', 'applicant', 'score', 'stats',
            'psbFromPanel', 'panelCount', 'autoCategory', 'autoEduLevel', 'copyTargets'
        ));
    }

    /**
     * Store the TWG score from the standalone form.
     */
    public function store(Request $request)
    {
        $applicantId = $request->input('applicant_id');
        if (!$applicantId) {
            return back()->with('error', 'Please select an applicant.');
        }

        $applicant = Applicant::findOrFail($applicantId);

        $eduMax = $request->position_category === 'no_eligibility'
            ? \App\Models\Setting::getVal('twg_max_education_no_eligibility', 20)
            : \App\Models\Setting::getVal('twg_max_education_eligibility', 15);

        $validated = $request->validate([
            'position_category'       => 'required|in:eligibility,no_eligibility',
            'education_level'         => 'nullable|in:first_level,second_level',
            'psb_interview_score'     => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_psb_interview', 50),
            'ipcr_score'              => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_ipcr', 10),
            'awards_score'            => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_awards', 5),
            'education_score'         => 'nullable|numeric|min:0|max:' . $eduMax,
            'experience_score'        => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_experience', 10),
            'training_score'          => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_training', 10),
            'length_of_service_score' => 'nullable|numeric|min:0|max:' . \App\Models\Setting::getVal('twg_max_length_of_service', 15),
            'demerits_deduction'      => 'nullable|numeric|min:0',
            'remarks'                 => 'nullable|string',
        ]);

        // Always compute PSB from panel evaluations (override submitted value)
        $panelAvg = InterviewEvaluation::where('applicant_id', $applicantId)->avg('total_score');
        $validated['psb_interview_score'] = $panelAvg !== null ? round($panelAvg * 0.50, 2) : null;

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
        $validated['evaluated_by'] = auth()->id();

        ApplicantHrmpsbScore::updateOrCreate(
            ['applicant_id' => $applicantId],
            $validated
        );

        return redirect()->route('recruitment.hrmpsb.twg.create', ['applicant_id' => $applicantId])
            ->with('success', 'HRMPSB Scores saved for ' . $applicant->full_name . '.');
    }
}
