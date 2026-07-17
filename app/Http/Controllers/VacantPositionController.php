<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\PostingSiteLog;
use App\Models\PublicationRequest;
use App\Models\VacantPosition;
use App\Support\Raccs\ESignatureService;
use App\Support\Vppm\VppmStatusService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * VPPM — Vacant Position Publication & Monitoring (RA 7041 / 2025 ORAOHRA
 * Sec.26/30/31). Route-level middleware ('role:...,viewer') is intentionally
 * broad; the real write-authority gate is authorizeEdit() below, because the
 * Dept Head signer in this system holds the Viewer role and was granted a
 * direct 'edit Vacancy Publication' permission override rather than a role
 * change (see 2026_07_10_000005 migration).
 */
class VacantPositionController extends Controller
{
    public function __construct(
        private readonly VppmStatusService $statusService,
        private readonly ESignatureService $signatureService,
    ) {
    }

    private function authorizeEdit(): void
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->isAppointmentAdmin() || $user->can('edit Vacancy Publication'), 403);
    }

    // ── Monitoring Dashboard (spec Sec.7) ───────────────────────────────────

    public function index(Request $request)
    {
        $query = PublicationRequest::with('vacantPosition', 'postingSiteLogs');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('office')) {
            $query->whereHas('vacantPosition', fn ($q) => $q->where('office_division', 'like', "%{$request->office}%"));
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $flagged = [
            'deficient' => PublicationRequest::where('status', 'PUBLICATION_DEFICIENT')->count(),
            'near_expiry' => PublicationRequest::where('status', 'NEAR_EXPIRY')->count(),
            'expired' => PublicationRequest::where('status', 'EXPIRED')->count(),
        ];

        $anticipated = VacantPosition::where('is_anticipated', true)
            ->whereNotNull('anticipated_incumbent_separation_date')
            ->orderBy('anticipated_incumbent_separation_date')
            ->get();

        return view('vppm.index', compact('requests', 'flagged', 'anticipated'));
    }

    // ── Vacancy CRUD (spec Sec.2.1 — from Plantilla or manual entry) ───────

    public function vacancyCreate()
    {
        $vacantPlantillaRecords = PlantillaRecord::vacant()->orderBy('position_title')->get();

        return view('vppm.vacancies.create', compact('vacantPlantillaRecords'));
    }

    public function vacancyStore(Request $request)
    {
        $this->authorizeEdit();

        $data = $this->validateVacancy($request);

        if (!empty($data['plantilla_record_id'])) {
            $record = PlantillaRecord::find($data['plantilla_record_id']);
            $data['item_no_snapshot'] = $record?->item_no_new;
        }

        $data['created_by'] = auth()->id();
        $data['status'] = 'DRAFT';

        $vacancy = VacantPosition::create($data);

        return redirect()->route('vppm.vacancies.show', $vacancy)->with('success', 'Vacancy captured. Prepare a CS Form No. 9 request when ready to publish.');
    }

    public function vacancyShow(VacantPosition $vacancy)
    {
        $vacancy->load('plantillaRecord', 'createdBy', 'publicationRequests.postingSiteLogs', 'publicationRequests.signatures');

        return view('vppm.vacancies.show', compact('vacancy'));
    }

    public function vacancyEdit(VacantPosition $vacancy)
    {
        $this->authorizeEdit();

        $vacantPlantillaRecords = PlantillaRecord::vacant()->orderBy('position_title')->get();

        return view('vppm.vacancies.edit', compact('vacancy', 'vacantPlantillaRecords'));
    }

    public function vacancyUpdate(Request $request, VacantPosition $vacancy)
    {
        $this->authorizeEdit();

        $data = $this->validateVacancy($request);
        $vacancy->update($data);

        return redirect()->route('vppm.vacancies.show', $vacancy)->with('success', 'Vacancy updated.');
    }

    public function apiGetPlantillaDetails($id)
    {
        $plantilla = PlantillaRecord::find($id);
        
        if (!$plantilla) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Prioritize the dedicated Qualification Standards table
        $qs = \App\Models\QualificationStandard::where('position_title', $plantilla->position_title)->first();

        // Try to fetch historical Qualification Standards from previous VacantPosition entries as fallback
        $historical = null;
        if (!$qs) {
            $historical = VacantPosition::where('position_title', $plantilla->position_title)
                ->whereNotNull('qs_education')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return response()->json([
            'position_title' => $plantilla->position_title,
            'salary_grade' => $plantilla->salary_grade,
            'monthly_salary' => $plantilla->getMonthlySalaryAttribute(), // fallback if needed
            'office_division' => $plantilla->office_department,
            'appointment_status' => 'Permanent', // default
            'qs_education' => $qs?->education ?? $historical?->qs_education ?? '',
            'qs_training' => $qs?->training ?? $historical?->qs_training ?? '',
            'qs_experience' => $qs?->experience ?? $historical?->qs_experience ?? '',
            'qs_eligibility' => $qs?->eligibility ?? $historical?->qs_eligibility ?? '',
        ]);
    }

    private function validateVacancy(Request $request): array
    {
        return $request->validate([
            'plantilla_record_id' => 'nullable|exists:plantilla_records,id',
            'position_title' => 'required|string|max:150',
            'parenthetical_title' => 'nullable|string|max:150',
            'salary_grade' => 'required|string|max:10',
            'monthly_salary' => 'required|numeric|min:0',
            'place_of_assignment' => 'required|string|max:150',
            'office_division' => 'nullable|string|max:150',
            'appointment_status' => 'required|in:Permanent,Temporary,Casual,Coterminous',
            'vacancy_type' => 'required|in:Original,Vice,Reclassified,Created',
            'vice_whom' => 'nullable|string|max:150',
            'vacated_date' => 'nullable|date',
            'is_anticipated' => 'nullable|boolean',
            'anticipated_incumbent_separation_date' => 'nullable|date|required_if:is_anticipated,1',
            'qs_education' => 'required|string',
            'qs_training' => 'required|string',
            'qs_experience' => 'required|string',
            'qs_eligibility' => 'required|string',
        ]);
    }

    // ── Publication Request lifecycle (spec Sec.3/5/6) ──────────────────────

    public function requestStore(Request $request, VacantPosition $vacancy)
    {
        $this->authorizeEdit();

        $data = $request->validate([
            'agency_contact_person' => 'required|string|max:150',
            'agency_contact_number' => 'required|string|max:50',
            'agency_contact_email' => 'required|email|max:150',
            'submission_mode' => 'required|in:CSC_FO,Agency_Website,Newspaper,Job_Site,Multiple',
            'posting_min_required_days' => 'nullable|integer|min:1',
            'validity_months' => 'nullable|integer|min:1',
        ]);

        $publicationRequest = PublicationRequest::create(array_merge($data, [
            'vacant_position_id' => $vacancy->id,
            'version_no' => 1,
            'prepared_by_id' => auth()->id(),
            'status' => 'DRAFT',
            'posting_min_required_days' => $data['posting_min_required_days'] ?? 15,
            'validity_months' => $data['validity_months'] ?? 9,
        ]));

        $vacancy->update(['status' => 'FOR_PUBLICATION']);

        return redirect()->route('vppm.requests.show', $publicationRequest)->with('success', 'CS Form No. 9 request drafted.');
    }

    public function requestShow(PublicationRequest $publicationRequest)
    {
        $publicationRequest->load('vacantPosition', 'postingSiteLogs.postedBy', 'signatures.signer', 'preparedBy', 'supersedes');

        return view('vppm.requests.show', compact('publicationRequest'));
    }

    public function requestPendingSignature(PublicationRequest $publicationRequest)
    {
        $this->authorizeEdit();

        $this->statusService->moveToPendingSignature($publicationRequest);

        return back()->with('success', 'Routed for Department Head signature.');
    }

    public function requestSign(Request $request, PublicationRequest $publicationRequest)
    {
        $this->authorizeEdit();

        $data = $request->validate([
            'signatory_name' => 'required|string|max:150',
            'signatory_position' => 'nullable|string|max:150',
            'signature_image' => 'required|string',
        ]);

        $this->signatureService->capture(
            $publicationRequest,
            $data['signatory_name'],
            $data['signatory_position'] ?? null,
            $data['signature_image'],
            $request->user(),
            $request->ip(),
        );

        return back()->with('success', 'CS Form No. 9 signed.');
    }

    public function requestSubmitCscFo(Request $request, PublicationRequest $publicationRequest)
    {
        $this->authorizeEdit();

        $data = $request->validate([
            'submitted_to_csc_fo_date' => 'required|date',
            'csc_fo_receiving_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('csc_fo_receiving_copy')) {
            $path = $request->file('csc_fo_receiving_copy')->store('vppm/receiving-copies', 'public');
        }

        $this->statusService->submitToCscFo($publicationRequest, $data['submitted_to_csc_fo_date'], $path);

        return back()->with('success', 'Submission to CSC Field Office recorded.');
    }

    public function postingSiteStore(Request $request, PublicationRequest $publicationRequest)
    {
        $this->authorizeEdit();

        $data = $request->validate([
            'site_label' => 'required|string|max:100',
            'posted_date' => 'required|date',
            'proof_photo' => 'nullable|image|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('proof_photo')) {
            $path = $request->file('proof_photo')->store('vppm/posting-proof', 'public');
        }

        PostingSiteLog::create([
            'publication_request_id' => $publicationRequest->id,
            'site_label' => $data['site_label'],
            'posted_date' => $data['posted_date'],
            'proof_photo_path' => $path,
            'posted_by_id' => auth()->id(),
        ]);

        $this->statusService->recordSitePosting($publicationRequest);

        return back()->with('success', 'Posting site logged.');
    }

    /** Sec.26 — spelling/missing-parenthetical-title only; stays on this row, no republication cycle. */
    public function requestExemptEdit(Request $request, PublicationRequest $publicationRequest)
    {
        $this->authorizeEdit();

        $data = $request->validate([
            'position_title' => 'nullable|string|max:150',
            'parenthetical_title' => 'nullable|string|max:150',
            'note' => 'required|string|max:255',
        ]);

        $changes = collect($data)->only(['position_title', 'parenthetical_title'])->filter()->all();
        if (empty($changes)) {
            return back()->withErrors(['note' => 'No spelling/parenthetical-title change was provided.']);
        }

        // These fields live on VacantPosition, not the request itself.
        $publicationRequest->vacantPosition->fill($changes)->save();
        $publicationRequest->update(['is_section26_exempt_edit' => true, 'edit_reason' => 'Sec.26 exempt: ' . $data['note']]);

        return back()->with('success', 'Correction applied (Sec.26 exempt — no republication required).');
    }

    /** Sec.26 — any other post-submission edit spawns a new version instead of overwriting. */
    public function requestRepublish(Request $request, PublicationRequest $publicationRequest)
    {
        $this->authorizeEdit();

        $data = $request->validate([
            'agency_contact_person' => 'required|string|max:150',
            'agency_contact_number' => 'required|string|max:50',
            'agency_contact_email' => 'required|email|max:150',
            'edit_reason' => 'required|string|max:255',
        ]);

        $editReason = $data['edit_reason'];
        unset($data['edit_reason']);

        $newRequest = $this->statusService->republish($publicationRequest, $data, $editReason);

        return redirect()->route('vppm.requests.show', $newRequest)->with('success', "Republished as version {$newRequest->version_no}.");
    }

    public function requestMarkFilled(PublicationRequest $publicationRequest)
    {
        $this->authorizeEdit();

        $this->statusService->markFilled($publicationRequest);
        $publicationRequest->vacantPosition->update(['status' => 'FILLED']);

        return back()->with('success', 'Marked as filled.');
    }

    public function requestCancel(Request $request, PublicationRequest $publicationRequest)
    {
        $this->authorizeEdit();

        $data = $request->validate(['reason' => 'required|string|max:255']);
        $this->statusService->cancel($publicationRequest, $data['reason']);

        return back()->with('success', 'Publication request cancelled.');
    }

    public function csForm9Pdf(PublicationRequest $publicationRequest)
    {
        $publicationRequest->load('vacantPosition', 'signatures.signer');

        $pdf = Pdf::loadView('exports.cs-form-9-pdf', [
            'publicationRequest' => $publicationRequest,
            'vacancy' => $publicationRequest->vacantPosition,
        ]);

        return $pdf->stream("CS-Form-9-{$publicationRequest->id}.pdf");
    }

    // ── Reports (spec Sec.8) ─────────────────────────────────────────────────

    public function reportBatch()
    {
        $requests = PublicationRequest::with('vacantPosition')
            ->whereIn('status', ['PUBLICATION_ACTIVE', 'VALID'])
            ->orderBy('vacant_position_id')
            ->get();

        return view('vppm.reports.batch', compact('requests'));
    }

    public function reportCompliance()
    {
        $requests = PublicationRequest::with('vacantPosition', 'postingSiteLogs', 'signatures')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('vppm.reports.compliance', compact('requests'));
    }

    public function reportExpiryForecast()
    {
        $requests = PublicationRequest::with('vacantPosition')
            ->whereIn('status', ['VALID', 'NEAR_EXPIRY'])
            ->orderBy('validity_end_date')
            ->get();

        return view('vppm.reports.expiry-forecast', compact('requests'));
    }

    public function reportAnticipatedPipeline()
    {
        $vacancies = VacantPosition::where('is_anticipated', true)
            ->whereNotNull('anticipated_incumbent_separation_date')
            ->orderBy('anticipated_incumbent_separation_date')
            ->get();

        return view('vppm.reports.anticipated-pipeline', compact('vacancies'));
    }
}
