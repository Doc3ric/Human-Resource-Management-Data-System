<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantEvaluation;
use App\Models\PlantillaRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class RecruitmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Applicant::query();

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('position_applied')) {
            $query->where('position_applied', 'like', '%' . $request->position_applied . '%');
        }

        if ($request->filled('highest_educational_attainment')) {
            $query->where('highest_educational_attainment', 'like', '%' . $request->highest_educational_attainment . '%');
        }

        if ($request->filled('degree')) {
            $query->where('degree', 'like', '%' . $request->degree . '%');
        }

        if ($request->filled('eligibility')) {
            $query->where('eligibility', 'like', '%' . $request->eligibility . '%');
        }

        if ($request->filled('office')) {
            $query->where('office', 'like', '%' . $request->office . '%');
        }

        if ($request->filled('sex')) {
            $query->where('sex', $request->sex);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'male' => (clone $query)->where('sex', 'Male')->count(),
            'female' => (clone $query)->where('sex', 'Female')->count(),
            'qualified' => (clone $query)->whereHas('evaluation', function($q) { $q->where('final_rating', 'Qualified'); })->count(),
            'disqualified' => (clone $query)->whereHas('evaluation', function($q) { $q->where('final_rating', 'Disqualified'); })->count(),
            'pending' => (clone $query)->where(function($q) {
                $q->whereDoesntHave('evaluation')
                  ->orWhereHas('evaluation', function($q2) {
                      $q2->whereNull('final_rating')->orWhere('final_rating', '');
                  });
            })->count(),
        ];

        $applicants = $query->with('evaluation')->orderBy('created_at', 'desc')->paginate(15);

        // For filter dropdown options
        $positions = Applicant::select('position_applied')->distinct()->pluck('position_applied')->filter()->sort();
        $offices = Applicant::select('office')->distinct()->pluck('office')->filter()->sort();
        $educations = Applicant::select('highest_educational_attainment')->distinct()->pluck('highest_educational_attainment')->filter()->sort();
        
        return view('recruitment.index', compact('applicants', 'positions', 'offices', 'educations', 'stats'));
    }

    public function create()
    {
        // Module 3.1 — vacant positions dropdown pulls from the live,
        // actually-populated plantilla_records (the Position/OrganizationalUnit
        // tables are legacy/empty in this app), DISTINCT by title, most
        // recently added first. Selecting a title auto-populates read-only
        // Office Allocation / Item Number / Salary Grade / Monthly Salary Rate.
        $vacantRecords = \App\Models\PlantillaRecord::where('is_vacant', true)
            ->where('abolished', false)
            ->whereNotNull('position_title')
            ->orderByDesc('created_at')
            ->get(['position_title', 'office_department', 'item_no_new', 'salary_grade', 'base_salary_amount']);

        $vacantPositions = $vacantRecords->pluck('position_title')->unique()->values();

        // One representative (most recent) row per title, for JS auto-fill.
        $vacantPositionDetails = $vacantRecords->unique('position_title')->values()->mapWithKeys(function ($r) {
            return [$r->position_title => [
                'office' => $r->office_department,
                'item_no' => $r->item_no_new,
                'salary_grade' => $r->salary_grade,
                'monthly_rate' => $r->base_salary_amount,
            ]];
        });

        // Get all active offices
        $vacantOffices = \App\Models\OrganizationalUnit::where('status', 'active')
            ->select('name')
            ->distinct()
            ->pluck('name')
            ->filter()
            ->sort();

        // Get distinct values for autocomplete
        $degrees = \App\Models\Applicant::select('degree')->whereNotNull('degree')->where('degree', '!=', '')->distinct()->pluck('degree')->sort();
        $eligibilities = \App\Models\Applicant::select('eligibility')->whereNotNull('eligibility')->where('eligibility', '!=', '')->distinct()->pluck('eligibility')->sort();
        $addresses = \App\Models\Applicant::select('address')->whereNotNull('address')->where('address', '!=', '')->distinct()->pluck('address')->sort();

        return view('recruitment.create', compact('vacantPositions', 'vacantOffices', 'vacantPositionDetails', 'degrees', 'eligibilities', 'addresses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'name_extension' => 'nullable|string|max:255',
            'sex' => 'required|string|in:Male,Female',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'phone_number' => 'required|string|max:50',
            'email_address' => 'required|email|max:255',
            'position_applied' => 'required|string|max:255',
            'item_no' => 'nullable|string|max:255',
            'office' => 'required|string|max:255',
            'highest_educational_attainment' => 'required|string|max:255',
            'degree' => 'nullable|string|max:255',
            'eligibility' => 'nullable|string|max:255',
            'application_letter' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
            'application_letter_override' => 'nullable|boolean',
            'application_letter_override_reason' => 'nullable|string|max:500',
        ]);

        // Module 4.2 — cannot save without an application letter, unless the
        // override toggle is used AND a reason is given (blank reason = rejected write).
        $hasFile = $request->hasFile('application_letter');
        $override = $request->boolean('application_letter_override');
        $overrideReason = trim((string) $request->input('application_letter_override_reason'));

        if (!$hasFile && !$override) {
            return back()->withErrors(['application_letter' => 'An application letter attachment is required. Use "Override Attachment" only if one is genuinely unavailable.'])->withInput();
        }
        if (!$hasFile && $override && $overrideReason === '') {
            return back()->withErrors(['application_letter_override_reason' => 'A reason is required to override the missing application letter attachment.'])->withInput();
        }

        $applicationLetterDocumentId = null;
        if ($hasFile) {
            $document = app(\App\Support\Idcc\IdccPipeline::class)->ingest($request->file('application_letter'), [
                'attachment_field' => 'applicant_application_letter',
                'ingested_by' => $request->user()->id,
            ]);
            $applicationLetterDocumentId = $document->id;
        }

        $data = $request->except(['application_letter']);
        $data['application_letter_document_id'] = $applicationLetterDocumentId;
        $data['application_letter_override'] = $hasFile ? false : $override;
        $data['application_letter_override_reason'] = $hasFile ? null : $overrideReason;
        // Checkboxes
        $data['is_pgb_employee']        = $request->has('is_pgb_employee');
        $data['is_pwd']                = $request->has('is_pwd');
        $data['has_non_pgb_employment'] = $request->has('has_non_pgb_employment');
        $data['acknowledged']           = $request->has('acknowledged');

        // Clear PGB sub-fields when not a PGB employee
        if (!$data['is_pgb_employee']) {
            foreach (['pgb_status','length_of_service','current_position',
                      'years_in_present_position','years_permanent','years_coterminous',
                      'years_casual','years_job_order'] as $f) {
                $data[$f] = null;
            }
        }
        // Clear non-PGB sub-fields when not applicable
        if (!$data['has_non_pgb_employment']) {
            foreach (['np_employment_status','np_employer','np_designation','np_period'] as $f) {
                $data[$f] = null;
            }
        }

        // Generate Reference No
        $data['reference_no'] = date('ymdHi') . rand(10, 99);

        $applicant = Applicant::create($data);

        // Module 3.2 — snapshot the Master Plantilla descriptors at creation
        // time so later actions can detect drift.
        $validator = new \App\Support\PlantillaSyncValidator();
        $validator->snapshot($applicant);
        $applicant->save();

        return redirect()->route('recruitment.index')->with('success', 'Application submitted successfully! Reference No: ' . $applicant->reference_no);
    }

    public function show($id)
    {
        $applicant    = Applicant::with(['evaluation', 'hrmpsbScore'])->findOrFail($id);
        $score        = $applicant->hrmpsbScore ?? new \App\Models\ApplicantHrmpsbScore();

        $evals        = \App\Models\InterviewEvaluation::where('applicant_id', $id)->get();
        $panelCount   = $evals->count();
        $psbFromPanel = $panelCount > 0 ? round($evals->avg('total_score') * 0.50, 2) : null;

        return view('recruitment.show', compact('applicant', 'score', 'psbFromPanel', 'panelCount'));
    }

    public function edit($id)
    {
        $applicant = Applicant::findOrFail($id);

        $vacantRecords = \App\Models\PlantillaRecord::where('is_vacant', true)
            ->where('abolished', false)
            ->whereNotNull('position_title')
            ->orderByDesc('created_at')
            ->get(['position_title', 'office_department', 'item_no_new', 'salary_grade', 'base_salary_amount']);

        $vacantPositions = $vacantRecords->pluck('position_title')->unique()->values();

        // Get all active offices
        $vacantOffices = \App\Models\OrganizationalUnit::where('status', 'active')
            ->select('name')
            ->distinct()
            ->pluck('name')
            ->filter()
            ->sort();

        // Get distinct values for autocomplete
        $degrees = \App\Models\Applicant::select('degree')->whereNotNull('degree')->where('degree', '!=', '')->distinct()->pluck('degree')->sort();
        $eligibilities = \App\Models\Applicant::select('eligibility')->whereNotNull('eligibility')->where('eligibility', '!=', '')->distinct()->pluck('eligibility')->sort();
        $addresses = \App\Models\Applicant::select('address')->whereNotNull('address')->where('address', '!=', '')->distinct()->pluck('address')->sort();

        return view('recruitment.edit', compact('applicant', 'vacantPositions', 'vacantOffices', 'degrees', 'eligibilities', 'addresses'));
    }

    public function update(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);

        // Module 3.2 — Master Plantilla validation lock: abort before saving
        // if the linked position's structural specs have drifted.
        $mismatch = (new \App\Support\PlantillaSyncValidator())->checkSync($applicant);
        if ($mismatch) {
            return back()->withErrors(['plantilla_sync' => $mismatch])->withInput();
        }

        $validated = $request->validate([
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'name_extension' => 'nullable|string|max:10',
            'date_of_birth' => 'required|date',
            'sex' => 'required|in:Male,Female',
            'phone_number' => 'required|string|max:50',
            'email_address' => 'required|email|max:100',
            'address' => 'required|string|max:255',
            'position_applied' => 'required|string|max:150',
            'office' => 'required|string|max:150',
            'highest_educational_attainment' => 'required|string',
            'degree' => 'nullable|string',
            'eligibility' => 'required|string',
        ]);

        $applicant->update($validated);
        
        return redirect()->route('recruitment.index')->with('success', 'Applicant updated successfully.');
    }

    public function destroy($id)
    {
        $applicant = Applicant::findOrFail($id);
        $applicant->delete();
        
        return redirect()->route('recruitment.index')->with('success', 'Applicant archived/deleted successfully.');
    }

    public function saveEvaluation(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);

        $validated = $request->validate([
            'qs_requirement' => ['nullable', 'in:Met,Unmet'],
            'exam_status'    => ['nullable', 'in:Passed,Failed,Absent'],
            'docs_complete'  => ['nullable', 'in:Yes,No'],
            'final_rating'   => ['nullable', 'in:Qualified,Disqualified'],
            'remarks'        => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['evaluated_by'] = auth()->id();
        $validated['evaluated_at'] = now();

        ApplicantEvaluation::updateOrCreate(
            ['applicant_id' => $applicant->id],
            $validated
        );

        return back()->with('success', 'Pre-evaluation saved for ' . $applicant->full_name . '.');
    }

    public function syncIpcr($id)
    {
        $applicant = Applicant::findOrFail($id);
        
        // Find matching PlantillaRecord
        $plantilla = \App\Models\PlantillaRecord::where('last_name', 'like', $applicant->last_name)
            ->where('first_name', 'like', $applicant->first_name)
            ->first();
            
        if (!$plantilla) {
            return response()->json(['success' => false, 'message' => 'No PGB employee record found for this applicant name.']);
        }
        
        // Get latest IPCR rating
        $latestIpcr = \App\Models\IpcrRating::where('plantilla_record_id', $plantilla->id)
            ->whereNotNull('final_rating')
            ->orderBy('year', 'desc')
            ->orderBy('period_type', 'desc')
            ->first();
            
        if (!$latestIpcr) {
            return response()->json(['success' => false, 'message' => 'No finalized IPCR rating found for this PGB employee.']);
        }
        
        $applicant->update(['performance_rating' => $latestIpcr->final_rating]);
        
        return response()->json([
            'success' => true, 
            'rating' => $latestIpcr->final_rating,
            'message' => 'Successfully fetched latest IPCR Rating: ' . $latestIpcr->final_rating
        ]);
    }

    public function printReceipt($id)
    {
        $applicant = Applicant::findOrFail($id);

        $pdf = Pdf::loadView('recruitment.receipt', compact('applicant'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Receipt_' . $applicant->reference_no . '.pdf');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:20480',
        ]);

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Could not open the uploaded file.');
        }

        $headers  = null;
        $imported = 0;
        $updated  = 0;
        $skipped  = 0;
        $errors   = [];
        $rowNum   = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            if ($headers === null) {
                $row[0]  = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
                $headers = array_map('trim', $row);
                continue;
            }

            $row  = array_pad($row, count($headers), '');
            $d    = array_combine($headers, $row);
            $get  = fn(string $col) => trim($d[$col] ?? '');
            $null = fn(string $col) => ($v = trim($d[$col] ?? '')) !== '' && strtoupper($v) !== 'N/A' ? $v : null;
            $bool = fn(string $col) => in_array(strtolower(trim($d[$col] ?? '')), ['yes', 'true', '1']);

            // ── Required fields ──────────────────────────────────────────
            $lastName  = $get('LASTNAME');
            $firstName = $get('FIRSTNAME');
            if (empty($lastName) || empty($firstName)) { $skipped++; continue; }

            // ── Dates ────────────────────────────────────────────────────
            try {
                $dob = \Carbon\Carbon::parse($get('BIRTHDAY'))->format('Y-m-d');
            } catch (\Exception) {
                $errors[] = "Row {$rowNum} ({$lastName}, {$firstName}): invalid birthday.";
                $skipped++;
                continue;
            }

            $appliedAt = null;
            try {
                if ($get('Timestamp')) $appliedAt = \Carbon\Carbon::parse($get('Timestamp'));
            } catch (\Exception) {}

            // ── Sex normalise ─────────────────────────────────────────────
            $sex = match(strtolower($get('SEX'))) {
                'male'   => 'Male',
                'female' => 'Female',
                default  => $get('SEX'),
            };

            // ── Suffix / N/A cleanup ──────────────────────────────────────
            $suffix = $null('SUFFIX');
            if ($suffix && in_array(strtoupper($suffix), ['N/A', 'NONE', '-'])) $suffix = null;

            // ── VACANCY → keep full text in position_applied, extract item_no ──
            // Formats: "BPH-KAL-34" (3-part) or "BPMC-251" (2-part, 3+ letters)
            // Excludes: "SG-14" (only 2 letters before dash = salary grade, not item no)
            $vacancy  = $get('VACANCY');
            $itemNo   = null;
            if (preg_match('/([A-Z]{2,6}-[A-Z]{2,6}-\d+|[A-Z]{3,6}-\d+)/i', $vacancy, $m)) {
                $itemNo = strtoupper($m[1]);
            }
            $positionApplied = $vacancy; // keep full vacancy text as-is

            // ── Education ────────────────────────────────────────────────
            $education = $get('EDUCATION');
            $eduUpper  = strtoupper($education);
            $hea = match(true) {
                str_contains($eduUpper, 'DOCTOR') || str_contains($eduUpper, 'PHD') || str_contains($eduUpper, 'PH.D') => 'Doctorate',
                str_contains($eduUpper, 'MASTER')    => "Master's Degree",
                str_contains($eduUpper, 'BACHELOR') || str_contains($eduUpper, 'B.S.') || str_contains($eduUpper, 'A.B.') || str_contains($eduUpper, 'B.A.') => "Bachelor's Degree",
                str_contains($eduUpper, 'ASSOCIATE') => 'Associate Degree',
                str_contains($eduUpper, 'VOCATIONAL') || str_contains($eduUpper, 'TECHNICAL') || str_contains($eduUpper, 'TESDA') => 'Vocational/Technical',
                str_contains($eduUpper, 'HIGH SCHOOL') || str_contains($eduUpper, 'SENIOR HIGH') => 'Senior High School',
                default => 'Others',
            };
            $degree = $education ? (trim(explode(',', $education)[0]) ?: null) : null;

            // ── Match logic ───────────────────────────────────────────────
            // Same email + same item_no → update existing record
            // Same email + different item_no (or no item_no) → new record
            $email = strtolower($get('Email Address'));

            $existingRecord = null;
            if ($email && $itemNo) {
                $existingRecord = Applicant::where('email_address', $email)
                    ->where('item_no', $itemNo)
                    ->first();
            }

            // ── PGB service years ─────────────────────────────────────────
            $pgbEmployee = $bool('PGB EMPLOYEE');
            $pgbStatus   = $pgbEmployee ? $null('STATUS') : null;
            $los         = $pgbEmployee ? $null('LOS') : null;
            $currentPos  = $pgbEmployee ? $null('POSITION (Current)') : null;
            $yearsPresent = $pgbEmployee ? $null('PRESENT POSITION') : null;
            $yearsPerm   = $pgbEmployee ? $null('PERMANENT') : null;
            $yearsCot    = $pgbEmployee ? $null('COTERMINOUS') : null;
            $yearsCas    = $pgbEmployee ? $null('CASUAL') : null;
            $yearsJO     = $pgbEmployee ? $null('JOB ORDER') : null;

            // ── Non-PGB employment ────────────────────────────────────────
            $hasNonPgb   = $bool('AFFILIATION');
            $npStatus    = $hasNonPgb ? $null('STATUS-NP') : null;
            $npEmployer  = $hasNonPgb ? $null('EMPLOYER') : null;
            $npDesig     = $hasNonPgb ? $null('DESIGNATION') : null;
            $npPeriod    = $hasNonPgb ? $null('PERIOD') : null;

            // ── Eligibility ───────────────────────────────────────────────
            // TYPE = eligibility name text; ELIGIBILITY = document URL
            $eligibility    = $null('TYPE');
            $eligibilityUrl = $null('ELIGIBILITY');

            // ── Training hours cleanup ────────────────────────────────────
            $trainingHours = $null('HOURS');
            if ($trainingHours && strtoupper($trainingHours) === '0 HRS.') $trainingHours = '0 Hours';

            // ── Build field payload (shared by create & update) ───────────
            $payload = [
                'applied_at'            => $appliedAt,
                'photo_url'             => $null('PHOTO'),
                'jaf_url'               => $null('JAF'),

                'last_name'             => strtoupper($lastName),
                'first_name'            => ucwords(strtolower($firstName)),
                'middle_name'           => $null('MIDDLENAME') ? ucwords(strtolower($null('MIDDLENAME'))) : null,
                'name_extension'        => $suffix,
                'sex'                   => $sex,
                'date_of_birth'         => $dob,
                'religion'              => $null('RELIGION'),
                'indigenous_people'     => $null('IP'),
                'is_pwd'               => $bool('PWD'),
                'phone_number'          => $get('PHONE') ?: 'N/A',
                'address'               => $get('ADDRESS') ?: 'N/A',
                'email_address'         => $email ?: 'noemail_' . time() . "_{$rowNum}@import.local",

                'is_pgb_employee'              => $pgbEmployee,
                'pgb_status'                   => $pgbStatus,
                'length_of_service'            => $los,
                'current_position'             => $currentPos,
                'years_in_present_position'    => $yearsPresent,
                'years_permanent'              => $yearsPerm,
                'years_coterminous'            => $yearsCot,
                'years_casual'                 => $yearsCas,
                'years_job_order'              => $yearsJO,

                'has_non_pgb_employment' => $hasNonPgb,
                'np_employment_status'   => $npStatus,
                'np_employer'            => $npEmployer,
                'np_designation'         => $npDesig,
                'np_period'              => $npPeriod,

                'position_applied'       => $positionApplied ?: 'Unspecified',
                'item_no'                => $itemNo,
                'office'                 => $null('PGB OFFICE'),

                'highest_educational_attainment' => $hea,
                'degree'                 => $degree,
                'tor_url'                => $null('TOR'),

                'eligibility'            => $eligibility,
                'eligibility_url'        => $eligibilityUrl,

                'training_url'           => $null('TRAINING'),
                'training_hours'         => $trainingHours,

                'experience_url'         => $null('EXPERIENCE'),
                'responsibilities'       => $null('RESPONSIBILITES'),

                'performance_rating'     => $null('PERFORMANCE RATING'),
                'performance_form_url'   => $null('PERFORMANCE FORM'),

                'award'                  => $null('AWARD'),
                'award_certificate_url'  => $null('AWARD CERTIFICATE'),

                'competencies'           => $null('COMPETENCIES'),
                'acknowledged'           => str_contains(strtolower($get('ACKNOWLEDGEMENT AND DECLARATION')), 'certify'),
            ];

            try {
                if ($existingRecord) {
                    // Same email + same item_no → update, keep original reference_no, ain, applied_at
                    $existingRecord->update($payload);
                    $updated++;
                } else {
                    // New application (different item_no or first-time)
                    $payload['reference_no'] = date('ymdHis') . sprintf('%03d', $rowNum);
                    Applicant::create($payload);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum} ({$lastName}, {$firstName}): " . $e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        $msg = "Import complete: <strong>{$imported}</strong> new record(s) created";
        if ($updated) $msg .= ", <strong>{$updated}</strong> existing record(s) updated";
        if ($skipped) $msg .= ", <strong>{$skipped}</strong> skipped";
        $msg .= '.';
        if (!empty($errors)) {
            $msg .= '<br><small class="text-warning">Errors: ' . e(implode('; ', array_slice($errors, 0, 5))) . '</small>';
        }

        if (auth()->check()) {
            \App\Models\ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'Imported Applicants via CSV',
                'description' => "Created {$imported} new, updated {$updated}, skipped {$skipped}.",
            ]);
        }

        return back()->with('success', $msg);
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'office' => 'nullable|string',
            'position_applied' => 'nullable|string',
        ]);

        $query = Applicant::query()
            ->whereBetween('created_at', [
                \Carbon\Carbon::parse($request->start_date)->startOfDay(),
                \Carbon\Carbon::parse($request->end_date)->endOfDay()
            ]);

        if ($request->filled('office')) {
            $query->where('office', $request->office);
        }

        if ($request->filled('position_applied')) {
            $query->where('position_applied', $request->position_applied);
        }

        if ($request->filled('item_no')) {
            $query->where('item_no', $request->item_no);
        }

        if ($request->filled('degree')) {
            $query->where('degree', 'like', '%' . $request->degree . '%');
        }

        if ($request->filled('eligibility')) {
            $query->where('eligibility', 'like', '%' . $request->eligibility . '%');
        }

        $applicants = $query->with('evaluation')->orderBy('office')->orderBy('position_applied')->orderBy('last_name')->get();

        // Map Salary Grade (SG) from Plantilla Records
        // This caches positions to avoid N+1 queries if we have many
        $sgMapping = \App\Models\PlantillaRecord::select('position_title', 'office_department', 'salary_grade')
            ->distinct()
            ->get()
            ->groupBy('position_title');

        foreach ($applicants as $applicant) {
            $applicant->sg = '';
            if (isset($sgMapping[$applicant->position_applied])) {
                // Try to match office as well
                $match = $sgMapping[$applicant->position_applied]->firstWhere('office_department', $applicant->office);
                if (!$match) {
                    $match = $sgMapping[$applicant->position_applied]->first(); // Fallback to any office with that title
                }
                $applicant->sg = $match ? $match->salary_grade : '';
            }
        }

        if ($request->report_type === 'demographics') {
            // Group by office → position/item_no
            $groupedApplicants = $applicants->groupBy(function ($a) {
                return $a->office ?: 'Unspecified Office';
            })->map(function ($officeGroup) {
                return $officeGroup->groupBy(function ($a) {
                    return ($a->item_no ?: '') . '|' . $a->position_applied;
                });
            });

            $pdf = Pdf::loadView('recruitment.demographics-pdf', [
                'groupedApplicants' => $groupedApplicants,
                'filters' => [
                    'office'            => $request->office,
                    'position_applied'  => $request->position_applied,
                    'item_no'           => $request->item_no,
                    'start_date'        => $request->start_date,
                    'end_date'          => $request->end_date,
                ],
            ])->setOptions([
                'isRemoteEnabled'     => true,
                'isHtml5ParserEnabled'=> true,
                'defaultFont'         => 'Arial',
            ]);
            $pdf->setPaper('A4', 'portrait');
            return $pdf->stream('Applicant_Demographics_' . date('Ymd_His') . '.pdf');
        }

        if ($request->report_type === 'preeval') {
            // Group applicants by Office and Position
            $groupedApplicants = $applicants->groupBy(function($item) {
                return $item->office . '|' . $item->position_applied;
            });

            if ($request->format === 'excel') {
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\PreevalReportExport($groupedApplicants), 
                    'PreEvaluation_Matrix_' . date('Ymd_His') . '.xlsx'
                );
            }

            $pdf = Pdf::loadView('recruitment.preeval-pdf', [
                'groupedApplicants' => $groupedApplicants,
            ]);
            $pdf->setPaper('legal', 'landscape'); // Matrix usually requires landscape on legal/A4
            return $pdf->stream('PreEvaluation_Matrix_' . date('Ymd_His') . '.pdf');
        }

        // Default: List of Applicants
        if ($request->format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ApplicantReportExport($applicants, $request->start_date, $request->end_date), 
                'Applicant_Report_' . date('Ymd_His') . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('recruitment.report-pdf', [
            'applicants' => $applicants,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
        ]);

        // A4 landscape or portrait? The reference looks like portrait, but could be landscape if many columns.
        // It has 5 columns, Portrait is fine.
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Applicant_Report_' . date('Ymd_His') . '.pdf');
    }

    // ── EXCEL IMPORT WITH FIELD MAPPING ─────────────────────────────────────

    /** Readable labels for every mappable DB column */
    public static function excelMappableFields(): array
    {
        return [
            'reference_no'                => 'Reference No.',
            'applied_at'                  => 'Date Received / Applied',
            'last_name'                   => 'Last Name',
            'first_name'                  => 'First Name',
            'middle_name'                 => 'Middle Name',
            'name_extension'              => 'Name Extension (Jr./Sr./III)',
            'sex'                         => 'Sex / Gender',
            'date_of_birth'               => 'Date of Birth',
            'address'                     => 'Address',
            'phone_number'                => 'Phone Number',
            'email_address'               => 'Email Address',
            'is_pgb_employee'             => 'PGB Employee? (Yes/No/True/False)',
            'pgb_status'                  => 'PGB Employment Status / Type',
            'is_pwd'                     => 'Person with Disability? (Yes/No)',
            'religion'                    => 'Religion',
            'indigenous_people'           => 'Indigenous People Group',
            'highest_educational_attainment' => 'Highest Educational Attainment',
            'degree'                      => 'Degree / Course',
            'eligibility'                 => 'Eligibility / Civil Service',
            'position_applied'            => 'Position Applied',
            'item_no'                     => 'Item No.',
            'office'                      => 'Office / Department',
            'sg'                          => 'Salary Grade',
            'current_position'            => 'Current Position',
            'length_of_service'           => 'Length of Service',
            'photo_url'                   => 'Photo URL',
            'jaf_url'                     => 'JAF / Application URL',
            'eligibility_url'             => 'Eligibility Document URL',
            'tor_url'                     => 'TOR URL',
            'training_url'                => 'Training Certificate URL',
            'training_hours'              => 'Training Hours',
            'experience_url'              => 'Experience Document URL',
            'responsibilities'            => 'Key Responsibilities',
            'performance_rating'          => 'Performance Rating',
            'award'                       => 'Award / Recognition',
            'competencies'                => 'Competencies',
            'acknowledged'                => 'Acknowledged? (Yes/No)',
        ];
    }

    public function importExcelForm()
    {
        return view('recruitment.import-excel');
    }

    public function importExcelMap(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ]);

        $file = $request->file('excel_file');
        $filename = uniqid('import_') . '.' . $file->getClientOriginalExtension();
        $tempPath = 'temp/excel-imports/' . $filename;
        Storage::disk('local')->put($tempPath, file_get_contents($file->getRealPath()));

        // Read headers + first 5 data rows for preview
        $fullPath = Storage::disk('local')->path($tempPath);
        $reader   = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, false);

        $headers     = $rows[0] ?? [];
        $previewRows = array_slice($rows, 1, 5);
        $totalRows   = count($rows) - 1;

        // Auto-suggest mappings based on name similarity
        $dbFields    = self::excelMappableFields();
        $suggestions = [];
        $keyMap = [
            'refno'      => 'reference_no',   'daterec'     => 'applied_at',
            'pgbemp'     => 'is_pgb_employee', 'lastname'    => 'last_name',
            'firstname'  => 'first_name',      'middlename'  => 'middle_name',
            'address'    => 'address',         'phone'       => 'phone_number',
            'email'      => 'email_address',   'gender'      => 'sex',
            'sex'        => 'sex',             'dob'         => 'date_of_birth',
            'education'  => 'highest_educational_attainment',
            'eligibility'=> 'eligibility',     'course'      => 'degree',
            'staapp'     => 'pgb_status',      'position'    => 'position_applied',
            'office'     => 'office',          'receipt'     => '__skip__',
            'remarks'    => '__skip__',        'pwd'         => 'is_pwd',
            'itemno'     => 'item_no',         'sg'          => 'sg',
            'name_ext'   => 'name_extension',
        ];
        foreach ($headers as $col) {
            $norm = strtolower(preg_replace('/[^a-z0-9]/i', '', $col));
            $suggestions[$col] = $keyMap[$norm] ?? '__skip__';
        }

        session(['excel_import_path' => $tempPath]);

        return view('recruitment.import-excel-map', compact('headers', 'previewRows', 'totalRows', 'dbFields', 'suggestions'));
    }

    public function importExcelProcess(Request $request)
    {
        $tempPath = session('excel_import_path');
        if (!$tempPath) {
            return redirect()->route('recruitment.import-excel')->with('error', 'Session expired. Please re-upload the file.');
        }

        $mapping   = $request->input('mapping', []);   // ['ExcelCol' => 'db_field']
        $dateFormat = $request->input('date_format', 'excel_serial'); // excel_serial | Y-m-d | m/d/Y | d/m/Y

        $fullPath  = Storage::disk('local')->path($tempPath);
        $reader    = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, false);
        $headers = $rows[0] ?? [];

        // Date fields that need special conversion
        $dateFields = ['applied_at', 'date_of_birth'];

        $imported = 0;
        $updated  = 0;
        $skipped  = 0;
        $errors   = [];

        foreach (array_slice($rows, 1) as $rowNum => $row) {
            try {
                $payload = [];

                foreach ($headers as $colIdx => $colName) {
                    $dbField = $mapping[$colName] ?? '__skip__';
                    if ($dbField === '__skip__' || $dbField === '') continue;

                    $rawVal = $row[$colIdx] ?? null;
                    if ($rawVal === null || $rawVal === '') continue;

                    // ── Type coercion ────────────────────────────────────────
                    if (in_array($dbField, $dateFields)) {
                        if (is_numeric($rawVal)) {
                            // Excel serial date
                            try {
                                $rawVal = \Carbon\Carbon::create(1899, 12, 30)->addDays((int)$rawVal)->format('Y-m-d');
                            } catch (\Exception $e) { $rawVal = null; }
                        } elseif ($dateFormat !== 'excel_serial') {
                            try {
                                $rawVal = \Carbon\Carbon::createFromFormat($dateFormat, trim($rawVal))->format('Y-m-d');
                            } catch (\Exception $e) { $rawVal = null; }
                        }
                    } elseif (in_array($dbField, ['is_pgb_employee', 'is_pwd', 'has_non_pgb_employment', 'acknowledged'])) {
                        $rawVal = in_array(strtolower((string)$rawVal), ['true', '1', 'yes', 'y']) ? 1 : 0;
                    } else {
                        $rawVal = trim((string)$rawVal);
                    }

                    $payload[$dbField] = $rawVal;
                }

                if (empty($payload)) { $skipped++; continue; }

                // Auto-extract item_no from position_applied if not mapped separately
                if (!isset($payload['item_no']) && isset($payload['position_applied'])) {
                    if (preg_match('/([A-Z]{2,6}-[A-Z]{2,6}-\d+|[A-Z]{3,6}-\d+)/i', $payload['position_applied'], $m)) {
                        $payload['item_no'] = strtoupper($m[1]);
                    }
                }

                // Duplicate logic: same email + same item_no → update; else create
                $email  = $payload['email_address'] ?? null;
                $itemNo = $payload['item_no'] ?? null;
                $existing = null;
                if ($email && $itemNo) {
                    $existing = Applicant::where('email_address', $email)->where('item_no', $itemNo)->first();
                } elseif ($email && isset($payload['reference_no'])) {
                    $existing = Applicant::where('reference_no', $payload['reference_no'])->first();
                }

                if ($existing) {
                    unset($payload['reference_no'], $payload['applied_at']);
                    $existing->update($payload);
                    $updated++;
                } else {
                    if (empty($payload['reference_no'])) {
                        $payload['reference_no'] = date('ymdHis') . sprintf('%03d', $rowNum + 1);
                    }
                    Applicant::create($payload);
                    $imported++;
                }

            } catch (\Exception $e) {
                $errors[] = 'Row ' . ($rowNum + 2) . ': ' . $e->getMessage();
            }
        }

        // Clean up temp file
        Storage::disk('local')->delete($tempPath);
        session()->forget('excel_import_path');

        $msg = "Import complete — <strong>{$imported}</strong> new records created, <strong>{$updated}</strong> updated, <strong>{$skipped}</strong> skipped.";
        if ($errors) {
            $msg .= '<br><small class="text-danger">' . implode('<br>', array_slice($errors, 0, 5)) . (count($errors) > 5 ? ' …and ' . (count($errors) - 5) . ' more.' : '') . '</small>';
        }

        return redirect()->route('recruitment.index')->with('success', $msg);
    }
}
