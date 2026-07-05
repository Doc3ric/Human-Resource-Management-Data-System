<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CounselingRecord;
use App\Models\Document;
use App\Models\IncidentReport;
use App\Support\Idcc\IdccPipeline;
use App\Support\Incident\RulesAdvisoryEngine;
use Illuminate\Http\Request;

class IncidentReportController extends Controller
{
    public function __construct(
        private readonly RulesAdvisoryEngine $advisoryEngine,
        private readonly IdccPipeline $idcc,
    ) {
    }

    public function index(Request $request)
    {
        $query = IncidentReport::orderByDesc('reported_at');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        $reports = $query->paginate(20)->withQueryString();

        return view('incidents.index', compact('reports'));
    }

    /** Module 3A.1 — structured intake, facts only. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'incident_datetime' => 'required|date',
            'location' => 'nullable|string|max:255',
            'personnel_id' => 'nullable|exists:plantilla_records,id',
            'witnesses' => 'nullable|string',
            'category' => 'required|in:attendance,conduct,performance,safety,property,interpersonal,other',
            'narrative' => 'required|string',
            'immediate_action_taken' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $documentId = null;
        if ($request->hasFile('attachment')) {
            $document = $this->idcc->ingest($request->file('attachment'), [
                'attachment_field' => 'incident_report',
                'personnel_id' => $data['personnel_id'] ?? null,
                'personnel_type' => 'plantilla_record',
                'ingested_by' => $request->user()->id,
            ]);
            $documentId = $document->id;
        }

        $report = IncidentReport::create([
            'reference_no' => 'INC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
            'incident_datetime' => $data['incident_datetime'],
            'reported_at' => now()->toDateString(),
            'location' => $data['location'] ?? null,
            'reported_by' => $request->user()->id,
            'personnel_id' => $data['personnel_id'] ?? null,
            'witnesses' => $data['witnesses'] ?? null,
            'category' => $data['category'],
            'narrative' => $data['narrative'],
            'immediate_action_taken' => $data['immediate_action_taken'] ?? null,
            'document_id' => $documentId,
            'status' => 'draft',
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Filed Incident Report',
            'description' => "Filed incident report {$report->reference_no} ({$report->category}).",
        ]);

        return redirect()->route('incidents.show', $report)->with('success', "Incident {$report->reference_no} recorded.");
    }

    public function show(IncidentReport $incident)
    {
        return view('incidents.show', ['incident' => $incident]);
    }

    /** Module 3A.2 — generates the labeled advisory draft. Never a finding. */
    public function generateAdvisory(Request $request, IncidentReport $incident)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('add Incident Reports'), 403);

        $draft = $this->advisoryEngine->draft($incident->narrative, $incident->category);

        $incident->update([
            'advisory_citations' => $draft['citations'],
            'advisory_penalty_range' => $draft['penalty_range'],
            'advisory_recommendation' => $draft['recommendation'],
            'advisory_generated_at' => now(),
            'status' => 'advisory_ready',
        ]);

        return back()->with('success', 'Advisory draft generated. ' . RulesAdvisoryEngine::ADVISORY_LABEL);
    }

    /**
     * Module 3A.3/3A.4/3A.7 — mandatory human review + finalize. The
     * reviewing officer sets the track; a user cannot finalize an incident
     * they reported (conflict-of-interest guard).
     */
    public function finalize(Request $request, IncidentReport $incident)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Incident Reports'), 403);

        if ($incident->involvesUser($request->user())) {
            abort(403, 'You cannot finalize an incident report in which you are an involved party.');
        }

        $data = $request->validate([
            'final_citations' => 'nullable|array',
            'review_notes' => 'nullable|string',
            'final_track' => 'required|in:counseling,escalated',
            'counseling_recommendation' => 'required_if:final_track,counseling|nullable|string',
            'counseling_action_plan' => 'nullable|string',
            'counseling_target_date' => 'nullable|date',
        ]);

        if ($data['final_track'] === 'escalated') {
            abort_unless($request->user()->isSuperAdmin() || $request->user()->can('delete Incident Reports'), 403, 'Escalation to the formal RACCS track requires the incident_escalate permission.');
        }

        $incident->update([
            'final_citations' => $data['final_citations'] ?? $incident->advisory_citations,
            'review_notes' => $data['review_notes'] ?? null,
            'final_track' => $data['final_track'],
            'reviewed_by' => $request->user()->id,
            'finalized_by' => $request->user()->id,
            'finalized_at' => now(),
            'status' => 'finalized',
        ]);

        if ($data['final_track'] === 'counseling') {
            CounselingRecord::create([
                'incident_report_id' => $incident->id,
                'recommendation' => $data['counseling_recommendation'],
                'action_plan' => $data['counseling_action_plan'] ?? null,
                'target_date' => $data['counseling_target_date'] ?? null,
                'created_by' => $request->user()->id,
            ]);
        }

        // Module 3A.6 — reclassify the linked IDCC document (if any) so it
        // inherits the correct doc_type_code/privacy handling. Escalated
        // incidents are treated as RACCS-confidential.
        if ($incident->document_id) {
            $document = Document::find($incident->document_id);
            if ($document) {
                $document->update([
                    'doc_type_code' => $data['final_track'] === 'escalated' ? 'DISC-RACCS' : 'INCIDENT-COUNSELING',
                    'is_raccs' => $data['final_track'] === 'escalated',
                    'is_spi' => true,
                    'privacy_tier' => 1,
                    'personnel_id' => $incident->personnel_id,
                    'personnel_type' => 'plantilla_record',
                ]);
            }
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Finalized Incident Report',
            'description' => "Finalized incident {$incident->reference_no} — track: {$data['final_track']}.",
        ]);

        return back()->with('success', "Incident {$incident->reference_no} finalized ({$data['final_track']} track).");
    }
}
