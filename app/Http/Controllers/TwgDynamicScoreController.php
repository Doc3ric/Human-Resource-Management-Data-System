<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Applicant;
use App\Models\TwgScore;
use App\Models\TwgScoreHistory;
use App\Models\TwgScoreSubmission;
use App\Support\BlindScoringId;
use App\Support\TwgDynamicScoringService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

/** Module 6.1-6.4 — dynamic, criterion-driven TWG scoring sheet. */
class TwgDynamicScoreController extends Controller
{
    public function __construct(private readonly TwgDynamicScoringService $engine)
    {
    }

    public function create(Request $request)
    {
        $applicants = Applicant::orderBy('last_name')->get(['id', 'last_name', 'first_name', 'item_no', 'date_of_birth']);

        $applicant = null;
        $scores = collect();
        $bracketOptions = [];
        $submission = null;
        $history = collect();
        $totals = null;

        if ($request->filled('applicant_id')) {
            $applicant = Applicant::findOrFail($request->integer('applicant_id'));
            $scores = $this->engine->initialize($applicant);
            foreach ($scores as $score) {
                $bracketOptions[$score->id] = $this->engine->bracketOptionsFor($score, $applicant);
            }
            $submission = TwgScoreSubmission::where('applicant_id', $applicant->id)->first();
            $history = TwgScoreHistory::with('performedBy')
                ->where('applicant_id', $applicant->id)
                ->orderByDesc('created_at')
                ->get();
            $totals = $this->engine->computeTotals($applicant);
        }

        return view('hrmpsb.twg-dynamic.create', compact('applicants', 'applicant', 'scores', 'bracketOptions', 'submission', 'history', 'totals'));
    }

    public function store(Request $request, Applicant $applicant)
    {
        $submission = TwgScoreSubmission::where('applicant_id', $applicant->id)->first();
        abort_if($submission?->isLocked(), 403, 'This evaluation is locked. An HRMPSB Chairperson must unlock it before it can be edited.');

        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*.assessor_value' => 'nullable|numeric|min:0',
            'scores.*.bracket_points' => 'nullable|numeric|min:0',
            'scores.*.notes' => 'nullable|string',
        ]);

        foreach ($validated['scores'] as $scoreId => $row) {
            $score = TwgScore::with('criterion')->where('applicant_id', $applicant->id)->findOrFail($scoreId);

            try {
                if (array_key_exists('bracket_points', $row) && $row['bracket_points'] !== null) {
                    $score = $this->engine->setBracketSelection($score, (float) $row['bracket_points']);
                }
                if (array_key_exists('assessor_value', $row) && $row['assessor_value'] !== null) {
                    $this->engine->updateAssessorValue($score, (float) $row['assessor_value'], $row['notes'] ?? null, $request->user());
                }
            } catch (InvalidArgumentException $e) {
                return back()->withErrors(['scores' => $e->getMessage()])->withInput();
            }
        }

        return back()->with('success', 'Scores saved as draft.');
    }

    public function submit(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'recommendation' => 'required|in:' . implode(',', TwgDynamicScoringService::RECOMMENDATIONS),
            'overall_notes' => 'nullable|string',
            'certification_accepted' => 'required|accepted',
        ]);

        try {
            $this->engine->submit(
                $applicant,
                $request->user(),
                $validated['recommendation'],
                $validated['overall_notes'] ?? null,
                true
            );
        } catch (RuntimeException|InvalidArgumentException $e) {
            return back()->withErrors(['submit' => $e->getMessage()])->withInput();
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Submitted TWG Dynamic Score',
            'description' => "Submitted dynamic TWG evaluation for {$applicant->last_name}, {$applicant->first_name} (masked: " . BlindScoringId::forApplicant($applicant) . ').',
        ]);

        return back()->with('success', 'Evaluation submitted and locked.');
    }

    public function unlock(Request $request, Applicant $applicant)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit TWG Dynamic Scoring'), 403);

        try {
            $this->engine->unlock($applicant, $request->user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['unlock' => $e->getMessage()]);
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Unlocked TWG Dynamic Score',
            'description' => "Unlocked dynamic TWG evaluation for {$applicant->last_name}, {$applicant->first_name} (masked: " . BlindScoringId::forApplicant($applicant) . ').',
        ]);

        return back()->with('success', 'Evaluation unlocked for editing.');
    }
}
