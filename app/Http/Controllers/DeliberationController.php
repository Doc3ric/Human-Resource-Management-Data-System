<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Position;
use App\Models\TwgScoreHistory;
use App\Models\TwgScoreSubmission;
use App\Support\TwgDynamicScoringService;

/** Module 5 — split-screen deliberation workspace (locked header, 45/55 grid). */
class DeliberationController extends Controller
{
    public const PHASES = ['screening', 'twg_evaluation', 'hrmpsb_deliberation', 'completed'];

    public function __construct(private readonly TwgDynamicScoringService $engine)
    {
    }

    public function show(Applicant $applicant)
    {
        // The applicant selector chip track — same office/position clustering
        // the existing TWG picker already uses, kept as one flat list here.
        $applicants = Applicant::orderBy('last_name')->get(['id', 'last_name', 'first_name', 'item_no', 'photo_url', 'position_applied']);

        $position = Position::whereRaw('UPPER(title) = UPPER(?)', [$applicant->position_applied])->first();

        $scores = $this->engine->initialize($applicant);
        $bracketOptions = [];
        foreach ($scores as $score) {
            $bracketOptions[$score->id] = $this->engine->bracketOptionsFor($score, $applicant);
        }
        $submission = TwgScoreSubmission::where('applicant_id', $applicant->id)->first();
        $history = TwgScoreHistory::with('performedBy')->where('applicant_id', $applicant->id)->orderByDesc('created_at')->get();
        $totals = $this->engine->computeTotals($applicant);

        return view('recruitment.deliberation.show', compact(
            'applicant', 'applicants', 'position', 'scores', 'bracketOptions', 'submission', 'history', 'totals'
        ));
    }

    public function updatePhase(\Illuminate\Http\Request $request, Applicant $applicant)
    {
        $validated = $request->validate(['deliberation_phase' => 'required|in:' . implode(',', self::PHASES)]);
        $applicant->update(['deliberation_phase' => $validated['deliberation_phase']]);

        return back()->with('success', 'Phase updated.');
    }
}
