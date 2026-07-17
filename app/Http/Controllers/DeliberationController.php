<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Position;
use App\Models\TwgScoreHistory;
use App\Models\TwgScoreSubmission;
use App\Support\Recruitment\InterviewScoringMatrixData;
use App\Support\Recruitment\OfficeCanonicalizer;
use App\Support\Recruitment\VacancyIdentifier;
use App\Support\TwgDynamicScoringService;

/** Module 5 — split-screen deliberation workspace (locked header, 45/55 grid). */
class DeliberationController extends Controller
{
    public const PHASES = ['screening', 'twg_evaluation', 'hrmpsb_deliberation', 'completed'];

    public function __construct(private readonly TwgDynamicScoringService $engine)
    {
    }

    public function show(\Illuminate\Http\Request $request, ?Applicant $applicant = null)
    {
        // Get unique combinations of office and vacancy for the dropdowns. Office is canonicalized
        // so office-name variants/abbreviations don't split one real vacancy into several entries,
        // and the vacancy itself is identified by item_no (falling back to cleaned position text)
        // so position-text variants of the same item collapse, while genuinely different items
        // that happen to share a title stay distinct.
        $officePositions = Applicant::select('office', 'position_applied', 'item_no')
            ->whereNotNull('office')
            ->where('office', '!=', '')
            ->whereNotNull('position_applied')
            ->where('position_applied', '!=', '')
            ->get()
            ->map(function ($row) {
                $row->office = OfficeCanonicalizer::canonicalize($row->office);
                $row->position_key = VacancyIdentifier::key($row->item_no, $row->position_applied);
                return $row;
            })
            ->groupBy(fn ($row) => $row->office . '|' . $row->position_key)
            ->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'office' => $first->office,
                    'position_key' => $first->position_key,
                    'item_no' => $first->item_no,
                    'position_label' => VacancyIdentifier::labelFor($group->pluck('position_applied')),
                ];
            })
            ->sortBy(fn ($row) => $row->office . '|' . $row->position_label)
            ->values();

        $office = $applicant ? OfficeCanonicalizer::canonicalize($applicant->office) : $request->office;
        $position_applied = $applicant ? VacancyIdentifier::key($applicant->item_no, $applicant->position_applied) : $request->position_applied;
        $search = $request->input('search');

        $applicants = collect();
        if ($search) {
            // Name search takes priority over the office/position cascade — lets a rater
            // jump straight to a candidate without knowing which office/position they're filed under.
            $applicants = Applicant::where(function ($q) use ($search) {
                    $q->where('last_name', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('reference_no', 'like', "%{$search}%")
                      ->orWhere('item_no', 'like', "%{$search}%")
                      ->orWhere('position_applied', 'like', "%{$search}%");
                })
                ->orderByDesc('id')
                ->get(['id', 'last_name', 'first_name', 'item_no', 'photo_url', 'position_applied', 'office'])
                ->unique(function ($app) {
                    return strtolower(trim($app->first_name) . '|' . trim($app->last_name));
                })
                ->sortBy('last_name')
                ->values();
        } elseif ($office && $position_applied) {
            $rawOffices = Applicant::select('office')->distinct()->pluck('office')->filter()->values();
            $matchingRawOffices = OfficeCanonicalizer::matchingRawValues($rawOffices, [$office]);

            $positionRows = Applicant::whereIn('office', $matchingRawOffices)
                ->whereNotNull('position_applied')
                ->get(['position_applied', 'item_no']);
            $conditions = VacancyIdentifier::matchingConditions($positionRows, [$position_applied]);

            $query = Applicant::whereIn('office', $matchingRawOffices);
            VacancyIdentifier::applyMatch($query, $conditions);

            $applicants = $query
                ->orderByDesc('id') // Order by latest ID first to get the most recent profile
                ->get(['id', 'last_name', 'first_name', 'item_no', 'photo_url', 'position_applied', 'office'])
                ->unique(function ($app) {
                    return strtolower(trim($app->first_name) . '|' . trim($app->last_name));
                })
                ->sortBy('last_name')
                ->values();
        }

        if ($applicant) {
            $position = Position::whereRaw('UPPER(title) = UPPER(?)', [$applicant->position_applied])->first();

            $scores = $this->engine->initialize($applicant);
            $bracketOptions = [];
            foreach ($scores as $score) {
                $bracketOptions[$score->id] = $this->engine->bracketOptionsFor($score, $applicant);
            }
            $submission = TwgScoreSubmission::where('applicant_id', $applicant->id)->first();
            $history = TwgScoreHistory::with('performedBy')->where('applicant_id', $applicant->id)->orderByDesc('created_at')->get();
            $totals = $this->engine->computeTotals($applicant);

            // Feeds the embedded "Scoring Matrix" panel — the same consolidated,
            // all-raters view available as its own page under HRMPSB Interview,
            // now selectable from the workspace's own panel toggle alongside Profile.
            $matrixData = InterviewScoringMatrixData::forApplicant($applicant);

            // VPPM Sec.5 step 6 — flag (never block) deliberation start when this
            // vacancy's publication is PUBLICATION_DEFICIENT.
            $vppmDeficientRequest = \App\Models\VacantPosition::deficientRequestForItemNo($applicant->item_no);

            return view('recruitment.deliberation.show', array_merge(compact(
                'applicant', 'applicants', 'position', 'scores', 'bracketOptions', 'submission', 'history', 'totals',
                'officePositions', 'office', 'position_applied', 'vppmDeficientRequest', 'search'
            ), $matrixData));
        }

        return view('recruitment.deliberation.show', [
            'applicant' => null,
            'applicants' => $applicants,
            'officePositions' => $officePositions,
            'office' => $office,
            'position_applied' => $position_applied,
            'search' => $search,
        ]);
    }

    public function updatePhase(\Illuminate\Http\Request $request, Applicant $applicant)
    {
        $validated = $request->validate(['deliberation_phase' => 'required|in:' . implode(',', self::PHASES)]);
        $applicant->update(['deliberation_phase' => $validated['deliberation_phase']]);

        return back()->with('success', 'Phase updated.');
    }
}
