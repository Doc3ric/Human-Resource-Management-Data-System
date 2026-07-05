<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantHrmpsbScore;
use App\Models\HrmpsbSignatory;
use App\Models\InterviewEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterviewEvaluationController extends Controller
{
    /**
     * Show the main interview evaluation selection or dashboard.
     */
    public function index()
    {
        $evaluations = InterviewEvaluation::with(['applicant', 'rater'])->latest()->paginate(15);
        return view('hrmpsb.interview.index', compact('evaluations'));
    }

    /**
     * Show the Interview Evaluation Form (RATING).
     */
    public function create(Request $request)
    {
        // For cascading dropdowns, we can either pass all applicants grouped by office/position, 
        // or just load them all if the list isn't massive.
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

        $weights = [
            'appearance' => \App\Models\Setting::getVal('hrmpsb_weight_appearance', 5),
            'knowledge' => \App\Models\Setting::getVal('hrmpsb_weight_knowledge', 50),
            'communication' => \App\Models\Setting::getVal('hrmpsb_weight_communication', 10),
            'other' => \App\Models\Setting::getVal('hrmpsb_weight_other', 35),
        ];

        $applicant = null;
        if ($request->has('applicant_id') && $request->applicant_id) {
            $applicant = \App\Models\Applicant::find($request->applicant_id);
        }

        // Calculate Statistics
        $totalApplicants = $applicants->count();
        $evaluatedApplicantsIds = \App\Models\InterviewEvaluation::where('rater_id', auth()->id())
            ->pluck('applicant_id')
            ->toArray();
        $evaluatedCount = $applicants->whereIn('id', $evaluatedApplicantsIds)->count();
        $pendingCount = $totalApplicants - $evaluatedCount;

        $stats = [
            'total' => $totalApplicants,
            'evaluated' => $evaluatedCount,
            'pending' => $pendingCount
        ];

        return view('hrmpsb.interview.create', compact('applicants', 'offices', 'positions', 'weights', 'applicant', 'stats'));
    }

    /**
     * Store the individual panel member's evaluation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'applicant_id' => 'required|exists:applicants,id',
            'ratings' => 'required|array',
            'remarks' => 'nullable|string',
        ]);

        $applicant = Applicant::findOrFail($validated['applicant_id']);

        // Module 7 — scoring is blocked without a valid attached photo.
        if (!app(\App\Support\PhotoEnforcementService::class)->hasValidPhoto($applicant)) {
            return back()->withErrors(['photo' => \App\Support\PhotoEnforcementService::BLOCK_MESSAGE])->withInput();
        }

        // Fetch dynamic weights
        $wApp = \App\Models\Setting::getVal('hrmpsb_weight_appearance', 5);
        $wKno = \App\Models\Setting::getVal('hrmpsb_weight_knowledge', 50);
        $wCom = \App\Models\Setting::getVal('hrmpsb_weight_communication', 10);
        $wOth = \App\Models\Setting::getVal('hrmpsb_weight_other', 35);

        // Calculate weighted score
        $r = $validated['ratings'];
        
        // 1. Appearance (1 item) - max 4
        $appSum = (int)($r['appearance_1'] ?? 0);
        $appScore = ($appSum / 4) * $wApp;

        // 2. Knowledge (4 items) - max 16
        $knowSum = (int)($r['knowledge_1'] ?? 0) + (int)($r['knowledge_2'] ?? 0) + (int)($r['knowledge_3'] ?? 0) + (int)($r['knowledge_4'] ?? 0);
        $knowScore = ($knowSum / 16) * $wKno;

        // 3. Communication (3 items) - max 12
        $commSum = (int)($r['comm_1'] ?? 0) + (int)($r['comm_2'] ?? 0) + (int)($r['comm_3'] ?? 0);
        $commScore = ($commSum / 12) * $wCom;

        // 4. Other (10 items) - max 40
        $othSum = 0;
        for ($i = 1; $i <= 10; $i++) {
            $othSum += (int)($r['other_'.$i] ?? 0);
        }
        $othScore = ($othSum / 40) * $wOth;

        $totalScore = $appScore + $knowScore + $commScore + $othScore;

        InterviewEvaluation::updateOrCreate(
            [
                'applicant_id' => $applicant->id,
                'rater_id' => Auth::id(),
            ],
            [
                'office' => $applicant->office,
                'position' => $applicant->position_applied,
                'ratings' => $r,
                'remarks' => $validated['remarks'] ?? null,
                'total_score' => $totalScore,
                'rater_name' => Auth::user()?->name,
            ]
        );

        return redirect()->route('recruitment.hrmpsb.interview.matrix', $applicant->id)
            ->with('success', 'Your interview evaluation has been saved successfully.');
    }

    /**
     * Show the Scoring Matrix for a specific applicant.
     * This consolidates scores from all panel members.
     */
    public function matrix($applicant_id)
    {
        $applicant   = Applicant::findOrFail($applicant_id);
        $evaluations = InterviewEvaluation::with(['rater.roles', 'panelMember'])
            ->where('applicant_id', $applicant->id)
            ->orderBy('created_at')
            ->get();

        $averageScore = $evaluations->count() > 0 ? $evaluations->avg('total_score') : 0;

        $weights = [
            'appearance'    => \App\Models\Setting::getVal('hrmpsb_weight_appearance', 5),
            'knowledge'     => \App\Models\Setting::getVal('hrmpsb_weight_knowledge', 50),
            'communication' => \App\Models\Setting::getVal('hrmpsb_weight_communication', 10),
            'other'         => \App\Models\Setting::getVal('hrmpsb_weight_other', 35),
        ];

        // Pre-compute per-evaluator breakdown for the detailed criteria matrix
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

        return view('hrmpsb.interview.matrix', compact('applicant', 'evaluations', 'averageScore', 'weights', 'criteriaLabels'));
    }

    /**
     * Formal per-applicant Panel Interview Evaluation Report (printable).
     */
    public function detailReport($applicant_id)
    {
        $applicant   = Applicant::findOrFail($applicant_id);
        $evaluations = InterviewEvaluation::with(['rater.roles', 'panelMember'])
            ->where('applicant_id', $applicant->id)
            ->orderBy('created_at')
            ->get();

        $averageScore    = $evaluations->count() > 0 ? $evaluations->avg('total_score') : 0;
        $psbRating       = round($averageScore * 0.50, 2); // converts 100% → 50-pt PSB score

        $weights = [
            'appearance'    => \App\Models\Setting::getVal('hrmpsb_weight_appearance', 5),
            'knowledge'     => \App\Models\Setting::getVal('hrmpsb_weight_knowledge', 50),
            'communication' => \App\Models\Setting::getVal('hrmpsb_weight_communication', 10),
            'other'         => \App\Models\Setting::getVal('hrmpsb_weight_other', 35),
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
                'other_1'  => 'JOB COMMITMENT – Responsibility towards the mission and goals of the organization.',
                'other_2'  => 'COMMITMENT – Psychological attachment to the organization.',
                'other_3'  => 'POTENTIAL – Capability to perform duties of the position and higher ones.',
                'other_4'  => 'SINCERITY – Assessment of honesty, expression of valid/useful opinion backed by evidence.',
                'other_5'  => 'PROFESSIONALISM – Demonstrates consideration, respect, loyalty, and exceeds expectations.',
                'other_6'  => 'INITIATIVE – Eagerness to start actions without being told.',
                'other_7'  => 'TEAMWORK – Active involvement in a team resulting in goal achievement.',
                'other_8'  => 'TIME MANAGEMENT – Act of planning time spent on activities to increase productivity.',
                'other_9'  => 'CUSTOMER SERVICE – Act of taking care of customers needs professionally.',
                'other_10' => 'JOB SATISFACTION – Contentment of current job and the sense of accomplishment.',
            ],
        ];

        $categories = [
            'appearance'    => ['label' => 'I. PERSONAL APPEARANCE',           'weight' => $weights['appearance'],    'items' => array_keys($criteriaLabels['appearance'])],
            'knowledge'     => ['label' => 'II. KNOWLEDGE ON THE JOB & ORG.',  'weight' => $weights['knowledge'],     'items' => array_keys($criteriaLabels['knowledge'])],
            'communication' => ['label' => 'III. COMMUNICATION / INTERPERSONAL','weight' => $weights['communication'], 'items' => array_keys($criteriaLabels['communication'])],
            'other'         => ['label' => 'IV. OTHER EVALUATION CRITERIA',     'weight' => $weights['other'],         'items' => array_keys($criteriaLabels['other'])],
        ];

        $signatories = HrmpsbSignatory::where('team', 'management')->get();

        return view('hrmpsb.interview.detail-report', compact(
            'applicant', 'evaluations', 'averageScore', 'psbRating',
            'weights', 'criteriaLabels', 'categories', 'signatories'
        ));
    }

    /**
     * Copy all of an applicant's panel evaluations to sibling applicant records (same person, other positions).
     * Each rater's full set of criterion scores is duplicated for the target applicant_id.
     */
    public function copyEvaluation(Request $request)
    {
        $request->validate([
            'source_applicant_id'   => 'required|exists:applicants,id',
            'target_applicant_ids'  => 'required|array|min:1',
            'target_applicant_ids.*'=> 'exists:applicants,id',
        ]);

        $sourceApplicant = Applicant::findOrFail($request->source_applicant_id);
        $sourceEvals     = InterviewEvaluation::where('applicant_id', $request->source_applicant_id)->get();

        if ($sourceEvals->isEmpty()) {
            return back()->with('error', 'No panel evaluations found for the source applicant.');
        }

        $copied = 0;
        foreach ($request->target_applicant_ids as $targetId) {
            $target = Applicant::find($targetId);
            if (!$target || $target->ain !== $sourceApplicant->ain) continue;

            foreach ($sourceEvals as $eval) {
                $data = $eval->only([
                    'rater_id', 'panel_member_id', 'rater_name',
                    'office', 'position', 'ratings', 'total_score', 'remarks',
                ]);
                $data['applicant_id'] = $targetId;

                InterviewEvaluation::updateOrCreate(
                    [
                        'applicant_id'    => $targetId,
                        'rater_id'        => $eval->rater_id,
                        'panel_member_id' => $eval->panel_member_id,
                    ],
                    $data
                );
            }
            $copied++;
        }

        return back()->with('success', "Panel evaluations copied to {$copied} other application(s) successfully.");
    }

    /**
     * Show the final Comparative Assessment Report for a specific position.
     */
    public function comparativeReport(Request $request)
    {
        $position = $request->get('position');
        $office = $request->get('office');
        $teamType = $request->get('team', 'management'); // default to management

        $query = Applicant::query();
        if ($position) {
            $query->where('position_applied', $position);
        }
        if ($office) {
            $query->where('office', $office);
        }

        $applicants = $query->with('hrmpsbScore')->get();

        // Calculate consolidated scores
        foreach ($applicants as $app) {
            // Get average interview score from all raters
            $evals = InterviewEvaluation::where('applicant_id', $app->id)->get();
            $avgInterview = $evals->count() > 0 ? $evals->avg('total_score') : 0;
            
            // The template shows "INTERVIEW" column holding the full 100% score (e.g. 99.25),
            // and the next column is the 50% weighted portion (e.g. 49.63).
            $app->raw_interview_avg = $avgInterview;
            $app->weighted_interview = $avgInterview * 0.50; // 50% weight

            $twg = $app->hrmpsbScore;
            $app->twg_ipcr = $twg ? $twg->ipcr_score : 0;
            $app->twg_awards = $twg ? $twg->awards_score : 0;
            $app->twg_education = $twg ? $twg->education_score : 0;
            $app->twg_experience = $twg ? $twg->experience_score : 0;
            $app->twg_training = $twg ? $twg->training_score : 0;
            $app->demerits = $twg ? $twg->demerits_deduction : 0;

            $app->grand_total = (
                $app->weighted_interview + 
                $app->twg_ipcr + 
                $app->twg_awards + 
                $app->twg_education + 
                $app->twg_experience + 
                $app->twg_training
            ) - $app->demerits;
        }

        // Sort by grand total descending to rank them
        $applicants = $applicants->sortByDesc('grand_total')->values();

        $signatories = HrmpsbSignatory::where('team', $teamType)->get();

        // Dropdown data
        $positions = Applicant::select('position_applied')->distinct()->pluck('position_applied')->filter();
        $offices = Applicant::select('office')->distinct()->pluck('office')->filter();

        return view('hrmpsb.interview.comparative-report', compact(
            'applicants', 'signatories', 'positions', 'offices', 'position', 'office', 'teamType'
        ));
    }

    /**
     * Settings page for managing signatories.
     */
    public function signatories()
    {
        $legislative = HrmpsbSignatory::where('team', 'legislative')->get();
        $management = HrmpsbSignatory::where('team', 'management')->get();

        $weights = [
            'appearance' => \App\Models\Setting::getVal('hrmpsb_weight_appearance', 5),
            'knowledge' => \App\Models\Setting::getVal('hrmpsb_weight_knowledge', 50),
            'communication' => \App\Models\Setting::getVal('hrmpsb_weight_communication', 10),
            'other' => \App\Models\Setting::getVal('hrmpsb_weight_other', 35),
        ];

        return view('hrmpsb.interview.signatories', compact('legislative', 'management', 'weights'));
    }

    public function storeWeights(Request $request)
    {
        $validated = $request->validate([
            'appearance' => 'required|numeric',
            'knowledge' => 'required|numeric',
            'communication' => 'required|numeric',
            'other' => 'required|numeric',
        ]);

        \App\Models\Setting::updateOrCreate(['key' => 'hrmpsb_weight_appearance'], ['value' => $validated['appearance']]);
        \App\Models\Setting::updateOrCreate(['key' => 'hrmpsb_weight_knowledge'], ['value' => $validated['knowledge']]);
        \App\Models\Setting::updateOrCreate(['key' => 'hrmpsb_weight_communication'], ['value' => $validated['communication']]);
        \App\Models\Setting::updateOrCreate(['key' => 'hrmpsb_weight_other'], ['value' => $validated['other']]);

        return back()->with('success', 'Score weightings updated successfully.');
    }

    public function storeSignatories(Request $request)
    {
        $validated = $request->validate([
            'team' => 'required|in:legislative,management',
            'signatories' => 'required|array',
            'signatories.*.id' => 'nullable|exists:hrmpsb_signatories,id',
            'signatories.*.role' => 'required|string',
            'signatories.*.name' => 'nullable|string',
            'signatories.*.title' => 'nullable|string',
        ]);

        $team = $validated['team'];
        $keptIds = [];

        foreach ($validated['signatories'] as $sig) {
            if (isset($sig['id']) && $sig['id']) {
                $record = HrmpsbSignatory::find($sig['id']);
                $record->update([
                    'role' => $sig['role'],
                    'name' => $sig['name'],
                    'title' => $sig['title'],
                ]);
                $keptIds[] = $record->id;
            } else {
                $new = HrmpsbSignatory::create([
                    'team' => $team,
                    'role' => $sig['role'],
                    'name' => $sig['name'],
                    'title' => $sig['title'],
                ]);
                $keptIds[] = $new->id;
            }
        }

        // Delete removed
        HrmpsbSignatory::where('team', $team)->whereNotIn('id', $keptIds)->delete();

        return back()->with('success', ucfirst($team) . ' signatories updated successfully.');
    }
}
