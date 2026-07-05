<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PlantillaRecord;
use App\Models\Training;
use App\Models\TrainingParticipant;
use App\Support\Idcc\IdccPipeline;
use App\Support\Training\TrainingCertificateService;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function __construct(private readonly TrainingCertificateService $certificates)
    {
    }

    public function index()
    {
        $trainings = Training::withCount('participants')->orderByDesc('date_from')->paginate(15);

        return view('training.index', compact('trainings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:in_house,external,online,seminar,workshop,conference,scholarship,other',
            'competency_area' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'venue' => 'nullable|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'total_hours' => 'required|numeric|min:0',
            'organizer' => 'nullable|string|max:255',
            'fund_source' => 'nullable|string|max:255',
            'reference_authority' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $documentId = null;
        if ($request->hasFile('attachment')) {
            $document = app(IdccPipeline::class)->ingest($request->file('attachment'), [
                'attachment_field' => 'training_program_document',
                'ingested_by' => $request->user()->id,
            ]);
            $documentId = $document->id;
        }

        $training = Training::create([...$data, 'document_id' => $documentId, 'created_by' => $request->user()->id]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Created Training Record',
            'description' => "Recorded training \"{$training->title}\" ({$training->total_hours} hrs).",
        ]);

        return redirect()->route('training.show', $training)->with('success', 'Training recorded.');
    }

    public function show(Training $training)
    {
        $participants = $training->participants()->with('plantillaRecord', 'certificates')->get();

        return view('training.show', compact('training', 'participants'));
    }

    /** Module 10B.2 — register a participant (PGB via personnel_id, or ad-hoc external). */
    public function addParticipant(Request $request, Training $training)
    {
        $data = $request->validate([
            'personnel_id' => 'nullable|exists:plantilla_records,id',
            'participant_name' => 'required_without:personnel_id|nullable|string|max:255',
            'participant_office' => 'nullable|string|max:255',
            'is_resource_speaker' => 'nullable|boolean',
            'topic' => 'nullable|string|max:255',
            'attendance_status' => 'required|in:present,partial,absent,excused',
            'hours_attended' => 'nullable|numeric|min:0',
        ]);

        $participant = TrainingParticipant::create([
            'training_id' => $training->id,
            'personnel_id' => $data['personnel_id'] ?? null,
            'participant_name' => $data['participant_name'] ?? null,
            'participant_office' => $data['participant_office'] ?? null,
            'is_resource_speaker' => $request->boolean('is_resource_speaker'),
            'topic' => $data['topic'] ?? null,
            'attendance_status' => $data['attendance_status'],
            'hours_attended' => $data['hours_attended'] ?? null,
        ]);

        return back()->with('success', "{$participant->displayName()} registered.");
    }

    /** Module 10B.3 — individual certificate. */
    public function issueCertificate(Request $request, TrainingParticipant $participant)
    {
        try {
            $cert = $this->certificates->issue($participant, $request->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Issued Training Certificate',
            'description' => "Issued {$cert->certificate_type} certificate {$cert->reference_no} to {$participant->displayName()}.",
        ]);

        return back()->with('success', "Certificate {$cert->reference_no} issued to {$participant->displayName()}.");
    }

    /** Module 10B.3 — batch certificate generation for the qualifying roster. */
    public function issueBatchCertificates(Request $request, Training $training)
    {
        $participants = $training->participants;
        $result = $this->certificates->issueBatch($participants, $request->user());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Batch-Issued Training Certificates',
            'description' => "Issued " . count($result['issued']) . " certificate(s) for \"{$training->title}\"; " . count($result['skipped']) . " skipped.",
        ]);

        $message = count($result['issued']) . ' certificate(s) issued.';
        if ($result['skipped']) {
            $message .= ' Skipped: ' . collect($result['skipped'])->map(fn ($s) => "{$s['participant']} ({$s['reason']})")->implode(', ');
        }

        return back()->with('success', $message);
    }

    /** Module 10B.7 — per-employee training history. */
    public function history(PlantillaRecord $plantilla)
    {
        $records = TrainingParticipant::where('personnel_id', $plantilla->id)
            ->with('training', 'certificates')
            ->get();

        $totalHours = $records->sum(fn ($r) => (float) ($r->hours_attended ?? $r->training->total_hours ?? 0));

        return view('training.history', compact('plantilla', 'records', 'totalHours'));
    }
}
