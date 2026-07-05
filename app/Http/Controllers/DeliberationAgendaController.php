<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\DeliberationAgenda;
use App\Models\Position;
use App\Support\ExamRoutingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DeliberationAgendaController extends Controller
{
    public function __construct(private readonly ExamRoutingService $examRouting)
    {
    }

    /**
     * Show the form for creating a new Agenda.
     */
    public function create(Request $request)
    {
        // Only fetch applicants where final_rating == 'Qualified'
        $qualifiedApplicantsQuery = Applicant::whereHas('evaluation', function ($query) {
            $query->where('final_rating', 'Qualified');
        });

        // Get unique offices and positions from qualified applicants
        $offices = (clone $qualifiedApplicantsQuery)->whereNotNull('office')->distinct()->pluck('office');
        $positions = (clone $qualifiedApplicantsQuery)->whereNotNull('position_applied')->distinct()->pluck('position_applied');

        // Apply filters if any
        if ($request->filled('offices')) {
            $qualifiedApplicantsQuery->whereIn('office', $request->input('offices'));
        }
        if ($request->filled('positions')) {
            $qualifiedApplicantsQuery->whereIn('position_applied', $request->input('positions'));
        }

        $qualifiedApplicants = $qualifiedApplicantsQuery->get();

        // Group by Office -> Position
        $matrix = [];
        foreach ($qualifiedApplicants as $app) {
            $office = $app->office ?? 'Unassigned Office';
            $pos = $app->position_applied ?? 'Unassigned Position';
            
            if (!isset($matrix[$office])) {
                $matrix[$office] = [];
            }
            if (!isset($matrix[$office][$pos])) {
                $matrix[$office][$pos] = [];
            }

            // Tagging logic: Job Order/External/Exempted — reuses Module 5.4's
            // ExamRoutingService::classify() (the single, tested source for this
            // classification) rather than a second implementation. The original
            // code here checked a nonexistent `employer` field, so it silently
            // classified every applicant as External regardless of actual status.
            $tag = match ($this->examRouting->classify($app)) {
                'pgb_jo' => 'Job Order',
                'exempt' => 'Exempted',
                default => 'External',
            };

            $matrix[$office][$pos][] = [
                'id' => $app->id,
                'name' => $app->last_name . ', ' . $app->first_name,
                'tag' => $tag,
            ];
        }

        return view('recruitment.deliberation.agenda.create', compact('offices', 'positions', 'matrix'));
    }

    /**
     * Store and generate the Agenda PDF.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'part_1_notes' => 'nullable|string',
            'matrix_data' => 'nullable|array', // applicants and their resolutions
        ]);

        $agenda = DeliberationAgenda::create([
            'title' => $validated['title'],
            'part_1_notes' => $validated['part_1_notes'],
            'matrix_data' => $validated['matrix_data'] ?? [],
            'created_by' => auth()->id(),
        ]);

        if ($request->input('action') === 'print') {
            return $this->print($agenda);
        }

        return redirect()->route('recruitment.index')->with('success', 'Agenda saved successfully.');
    }

    /**
     * Show/Print an Agenda PDF.
     */
    public function print(DeliberationAgenda $agenda)
    {
        $pdf = Pdf::loadView('recruitment.exports.agenda-pdf', compact('agenda'))
            ->setPaper('A4', 'portrait');
            
        return $pdf->stream('Agenda_' . $agenda->id . '.pdf');
    }
}
