<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BatchRenewalController;
use App\Models\ActivityLog;
use App\Models\LeaveViolation;
use App\Models\PlantillaRecord;
use App\Support\Leave\LeaveViolationDetectionService;
use App\Support\Leave\LeaveViolationLetterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/** Module 2B — Leave Violation Monitoring, Notice Generation & Payroll Coordination. */
class LeaveViolationController extends Controller
{
    public function __construct(
        private readonly LeaveViolationDetectionService $detector,
        private readonly LeaveViolationLetterService $letters,
    ) {
    }

    /** Module 2B.3 — dynamic violation watchlist. */
    public function recordedEntries(Request $request)
    {
        $query = LeaveViolation::with('plantillaRecord')->where('violation_type', 'LWOP_TARDINESS')->orderByDesc('created_at');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        $entries = $query->paginate(20)->withQueryString();

        $employees = PlantillaRecord::select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();

        $moduleUsers = \App\Models\User::where('status', 'Active')->get();

        $totalEmployees = PlantillaRecord::count();
        $absentEmployees = LeaveViolation::where('violation_type', 'LWOP_TARDINESS')->distinct('plantilla_record_id')->count('plantilla_record_id');
        $totalEntries = LeaveViolation::where('violation_type', 'LWOP_TARDINESS')->count();
        $thisMonth = LeaveViolation::where('violation_type', 'LWOP_TARDINESS')->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count();

        $metrics = [
            'total' => $totalEntries,
            'employees' => $absentEmployees,
            'employees_pct' => $totalEmployees > 0 ? round(($absentEmployees / $totalEmployees) * 100, 1) : 0,
            'this_month' => $thisMonth,
            'this_month_pct' => $totalEntries > 0 ? round(($thisMonth / $totalEntries) * 100, 1) : 0,
        ];

        return view('leave-violations.recorded-entries', compact('entries', 'employees', 'moduleUsers', 'metrics'));
    }

    public function createRecord()
    {
        $employees = PlantillaRecord::select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();
        return view('leave-violations.create-record', compact('employees'));
    }

    public function storeRecord(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('add Leave Violations'), 403);

        $validated = $request->validate([
            'plantilla_record_id' => 'required|exists:plantilla_records,id',
            'earned_as_of_date' => 'nullable|date',
            'earned_vl' => 'nullable|numeric',
            'earned_sl' => 'nullable|numeric',
            'days_without_pay_vl' => 'nullable|numeric',
            'days_without_pay_sl' => 'nullable|numeric',
            'days_without_pay_total' => 'nullable|numeric',
            'days_without_pay_inclusive_dates' => 'nullable|string',
            'days_without_pay_remarks' => 'nullable|string',
            'undertime_tardy_hours' => 'nullable|numeric',
            'undertime_tardy_mins' => 'nullable|numeric',
            'undertime_tardy_inclusive_dates' => 'nullable|string',
        ]);

        $violation = LeaveViolation::create([
            'plantilla_record_id' => $validated['plantilla_record_id'],
            'violation_type' => 'LWOP_TARDINESS',
            'rating_period' => BatchRenewalController::currentRatingPeriod(),
            'offense_tier' => 1,
            'status' => 'detected',
            'details' => $validated,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Added Leave Record',
            'description' => "Added Leave Record for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
        ]);

        return redirect()->route('leave-violations.recorded-entries')->with('success', 'Leave record saved successfully.');
    }

    public function generateReportPdf(Request $request)
    {
        $request->validate([
            'record_ids' => 'required|string', // JSON stringified array from frontend
            'leave_types' => 'nullable|array',
            'noted_by_name' => 'nullable|string',
            'noted_by_position' => 'nullable|string',
        ]);

        $recordIds = json_decode($request->record_ids, true);

        $entries = LeaveViolation::with('plantillaRecord')
            ->whereIn('id', $recordIds)
            ->get();

        $pdf = Pdf::loadView('exports.absence-without-pay-report', [
            'entries' => $entries,
            'leaveTypes' => $request->leave_types ?? [],
            'notedByName' => $request->noted_by_name,
            'notedByPosition' => $request->noted_by_position,
        ])->setPaper('legal', 'landscape');

        return $pdf->stream('Absence_Without_Pay_Report.pdf');
    }

    public function generatedLetters(Request $request)
    {
        $letters = LeaveViolation::with('plantillaRecord')
            ->whereIn('violation_type', ['HABITUAL_TARDINESS', 'UNDERTIME', 'REPRIMAND_HABITUAL_TARDINESS', 'REPRIMAND_UNDERTIME'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $total = LeaveViolation::whereIn('violation_type', ['HABITUAL_TARDINESS', 'UNDERTIME', 'REPRIMAND_HABITUAL_TARDINESS', 'REPRIMAND_UNDERTIME'])->count();
        $tardyUndertime = LeaveViolation::whereIn('violation_type', ['HABITUAL_TARDINESS', 'UNDERTIME'])->count();
        $reprimand = LeaveViolation::whereIn('violation_type', ['REPRIMAND_HABITUAL_TARDINESS', 'REPRIMAND_UNDERTIME'])->count();

        $metrics = [
            'total' => $total,
            'tardy_undertime' => $tardyUndertime,
            'tardy_undertime_pct' => $total > 0 ? round(($tardyUndertime / $total) * 100, 1) : 0,
            'reprimand' => $reprimand,
            'reprimand_pct' => $total > 0 ? round(($reprimand / $total) * 100, 1) : 0,
        ];

        return view('leave-violations.generated-letters', compact('letters', 'metrics'));
    }

    public function createTardy()
    {
        $employees = PlantillaRecord::select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();
        return view('leave-violations.create-tardy', compact('employees'));
    }

    public function createUndertime()
    {
        $employees = PlantillaRecord::select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();
        return view('leave-violations.create-undertime', compact('employees'));
    }

    public function createReprimand()
    {
        // Get employees who have at least one prior warning (HABITUAL_TARDINESS or UNDERTIME)
        $employeeIdsWithWarnings = LeaveViolation::whereIn('violation_type', ['HABITUAL_TARDINESS', 'UNDERTIME'])
            ->pluck('plantilla_record_id')
            ->unique();

        $employees = PlantillaRecord::select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();

        // Get prior warnings for JS lookup
        $priorWarnings = LeaveViolation::whereIn('violation_type', ['HABITUAL_TARDINESS', 'UNDERTIME'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'plantilla_record_id' => $v->plantilla_record_id,
                    'violation_type' => $v->violation_type,
                    'type_label' => $v->violation_type === 'HABITUAL_TARDINESS' ? 'Tardiness' : 'Undertime',
                    'month' => $v->details['month'] ?? '',
                    'year' => $v->details['year'] ?? '',
                    'issued_at' => $v->issued_at ? $v->issued_at->format('F d, Y') : $v->created_at->format('F d, Y'),
                ];
            });

        return view('leave-violations.create-reprimand', compact('employees', 'employeeIdsWithWarnings', 'priorWarnings'));
    }

    public function storeLetter(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('add Leave Violations'), 403);

        $validated = $request->validate([
            'plantilla_record_id' => 'required|exists:plantilla_records,id',
            'letter_type' => 'required|in:HABITUAL_TARDINESS,UNDERTIME',
            'month' => 'required|string',
            'year' => 'required|numeric',
            'occurrences' => 'required|string',
            'prefix' => 'nullable|string',
            'signatory_name' => 'required|string',
            'signatory_position' => 'required|string',
        ]);

        $offenseTier = LeaveViolation::where('plantilla_record_id', $validated['plantilla_record_id'])
            ->where('violation_type', $validated['letter_type'])
            ->count() + 1;

        $violation = LeaveViolation::create([
            'plantilla_record_id' => $validated['plantilla_record_id'],
            'violation_type' => $validated['letter_type'],
            'rating_period' => BatchRenewalController::currentRatingPeriod(),
            'offense_tier' => $offenseTier,
            'status' => 'detected',
            'details' => [
                'month' => $validated['month'],
                'year' => $validated['year'],
                'occurrences' => $validated['occurrences'],
                'prefix' => $validated['prefix'] ?? '',
                'signatory_name' => $validated['signatory_name'],
                'signatory_position' => $validated['signatory_position'],
                'manual_entry' => true,
            ],
        ]);

        // Automatically generate PDF and store to IDCC
        $this->letters->issue($violation, $request->user());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Generated Letter',
            'description' => "Generated {$validated['letter_type']} notice for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
        ]);

        return redirect()->route('leave-violations.generated-letters')->with('success', 'Letter generated successfully and saved to records.');
    }

    public function storeReprimand(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('add Leave Violations'), 403);

        $validated = $request->validate([
            'plantilla_record_id' => 'required|exists:plantilla_records,id',
            'base_violation_type' => 'required|in:HABITUAL_TARDINESS,UNDERTIME',
            'prior_warning_id' => 'required|exists:leave_violations,id',
            'month' => 'required|string',
            'year' => 'required|numeric',
            'occurrences' => 'required|string',
            'prefix' => 'nullable|string',
            'signatory_name' => 'required|string',
            'signatory_position' => 'required|string',
        ]);

        $reprimandType = 'REPRIMAND_' . $validated['base_violation_type'];

        // Count existing violations of same base type for offense tier
        $offenseTier = LeaveViolation::where('plantilla_record_id', $validated['plantilla_record_id'])
            ->where(function ($q) use ($validated) {
                $q->where('violation_type', $validated['base_violation_type'])
                  ->orWhere('violation_type', 'REPRIMAND_' . $validated['base_violation_type']);
            })
            ->count() + 1;

        $violation = LeaveViolation::create([
            'plantilla_record_id' => $validated['plantilla_record_id'],
            'violation_type' => $reprimandType,
            'rating_period' => BatchRenewalController::currentRatingPeriod(),
            'offense_tier' => $offenseTier,
            'status' => 'detected',
            'details' => [
                'month' => $validated['month'],
                'year' => $validated['year'],
                'occurrences' => $validated['occurrences'],
                'prefix' => $validated['prefix'] ?? '',
                'signatory_name' => $validated['signatory_name'],
                'signatory_position' => $validated['signatory_position'],
                'prior_warning_id' => $validated['prior_warning_id'],
                'manual_entry' => true,
            ],
        ]);

        $priorWarning = LeaveViolation::find($validated['prior_warning_id']);

        // Generate reprimand PDF
        $this->letters->issueReprimand($violation, $request->user(), $priorWarning);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Generated Reprimand Letter',
            'description' => "Generated Reprimand ({$validated['base_violation_type']}) for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
        ]);

        return redirect()->route('leave-violations.generated-letters')->with('success', 'Reprimand letter generated successfully and saved to records.');
    }

    public function editLetter(LeaveViolation $violation)
    {
        $employees = PlantillaRecord::select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();
        return view('leave-violations.edit-letter', compact('violation', 'employees'));
    }

    public function updateLetter(Request $request, LeaveViolation $violation)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Leave Violations'), 403);

        $validated = $request->validate([
            'plantilla_record_id' => 'required|exists:plantilla_records,id',
            'letter_type' => 'required|in:HABITUAL_TARDINESS,UNDERTIME,REPRIMAND_HABITUAL_TARDINESS,REPRIMAND_UNDERTIME',
            'month' => 'required|string',
            'year' => 'required|numeric',
            'occurrences' => 'required|string',
            'prefix' => 'nullable|string',
            'signatory_name' => 'required|string',
            'signatory_position' => 'required|string',
        ]);

        $violation->update([
            'plantilla_record_id' => $validated['plantilla_record_id'],
            'violation_type' => $validated['letter_type'],
            'details' => array_merge($violation->details, [
                'month' => $validated['month'],
                'year' => $validated['year'],
                'occurrences' => $validated['occurrences'],
                'prefix' => $validated['prefix'] ?? '',
                'signatory_name' => $validated['signatory_name'],
                'signatory_position' => $validated['signatory_position'],
            ]),
        ]);

        // Regenerate PDF — use reprimand method if applicable
        if (str_starts_with($validated['letter_type'], 'REPRIMAND_')) {
            $priorWarningId = $violation->details['prior_warning_id'] ?? null;
            $priorWarning = $priorWarningId ? LeaveViolation::find($priorWarningId) : null;
            $this->letters->issueReprimand($violation, $request->user(), $priorWarning);
        } else {
            $this->letters->issue($violation, $request->user());
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Updated Letter',
            'description' => "Updated {$validated['letter_type']} notice for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
        ]);

        return redirect()->route('leave-violations.generated-letters')->with('success', 'Letter updated successfully.');
    }

    public function destroyLetter(Request $request, LeaveViolation $violation)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Leave Violations'), 403);

        $type = $violation->violation_type;
        $name = $violation->plantillaRecord->first_name . ' ' . $violation->plantillaRecord->last_name;

        $violation->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Deleted Letter',
            'description' => "Deleted {$type} notice for {$name}.",
        ]);

        return redirect()->route('leave-violations.generated-letters')->with('success', 'Letter deleted successfully.');
    }

    /** Scan one personnel record's leave history for violations. */
    public function scan(Request $request, PlantillaRecord $plantilla)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('add Leave Violations'), 403);

        $flags = $this->detector->scan($plantilla, BatchRenewalController::currentRatingPeriod());

        return back()->with('success', count($flags) . ' violation(s) detected for ' . $plantilla->last_name . ', ' . $plantilla->first_name . '.');
    }

    public function markResolved(Request $request, LeaveViolation $violation)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Leave Violations'), 403);
        $violation->update(['status' => 'resolved']);

        return back()->with('success', 'Violation marked resolved.');
    }

    /**
     * Issue the generic notice (AWOL / Habitual Absenteeism / LWOP) for a
     * detected violation — the counterpart to storeLetter()/storeReprimand()
     * for violation types that don't go through the manual create-tardy/
     * create-undertime/create-reprimand forms, since they're detected
     * automatically by scan() rather than entered by hand.
     */
    public function issueNotice(Request $request, LeaveViolation $violation)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('add Leave Violations'), 403);

        $this->letters->issue($violation, $request->user());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Issued Notice',
            'description' => "Issued a {$violation->violation_type} notice for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
        ]);

        return back()->with('success', 'Notice generated and saved to records.');
    }

    /** M2B.6 — Issue Office/Payroll Coordination Letters. */
    public function issuePayrollCoordination(Request $request, LeaveViolation $violation)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Leave Violations'), 403);

        $validated = $request->validate([
            'action_type' => 'required|in:DROP,DEDUCT',
        ]);

        $this->letters->issuePayrollCoordination($violation, $request->user(), $validated['action_type']);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Issued Payroll Coordination',
            'description' => "Issued a {$validated['action_type']} payroll coordination letter for violation #{$violation->id}.",
        ]);

        return back()->with('success', 'Payroll Coordination letter generated and transmitted to IDCC.');
    }

    public function exportRecordedEntriesPdf(Request $request)
    {
        $query = LeaveViolation::with('plantillaRecord')->where('violation_type', 'LWOP_TARDINESS')->orderByDesc('created_at');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        $entries = $query->get();

        $pdf = Pdf::loadView('exports.recorded-entries-pdf', compact('entries'))
            ->setPaper('legal', 'landscape');

        return $pdf->stream('Leave_Violations_Recorded_Entries.pdf');
    }

    public function downloadPdf(LeaveViolation $violation)
    {
        // Check if there is an associated document stored in IDCC
        if ($violation->document_id) {
            return redirect()->route('idcc.preview', $violation->document_id);
        }

        // Use the shared service methods to get title, body, and legal basis
        $letterTitle = $this->letters->getLetterTitle($violation->violation_type);

        $monthYear = ($violation->details['month'] ?? date('F')) . ' ' . ($violation->details['year'] ?? date('Y'));
        $occurrences = $violation->details['occurrences'] ?? '0';

        $bodyText = $this->letters->getBodyText($violation->violation_type, $monthYear, $occurrences, $violation->offense_tier);
        $legalBasis = $this->letters->getLegalBasis($violation->violation_type);

        $referenceNo = 'LV-' . $violation->created_at->format('Ymd') . '-' . strtoupper(substr(md5($violation->id), -6));
        $prefix = $violation->details['prefix'] ?? '';
        $signatoryName = $violation->details['signatory_name'] ?? 'HR Management Officer';
        $signatoryPosition = $violation->details['signatory_position'] ?? 'PHRMO';

        // Flatten facts
        $facts = [];
        foreach ($violation->details as $key => $value) {
            $facts[str_replace('_', ' ', ucfirst($key))] = is_array($value) ? json_encode($value) : $value;
        }

        // Determine CC recipients for reprimand letters
        $ccRecipients = null;
        if (str_starts_with($violation->violation_type, 'REPRIMAND_')) {
            $ccRecipients = 'Provincial Discipline Committee<br>Provincial Government of Bukidnon';

            // For reprimand letters, build the body with prior warning reference
            $baseType = str_replace('REPRIMAND_', '', $violation->violation_type);
            $priorWarningId = $violation->details['prior_warning_id'] ?? null;
            $priorWarning = $priorWarningId ? LeaveViolation::find($priorWarningId) : null;

            if ($priorWarning) {
                $priorDate = $priorWarning->issued_at
                    ? $priorWarning->issued_at->format('F d, Y')
                    : $priorWarning->created_at->format('F d, Y');
                $priorMonth = $priorWarning->details['month'] ?? '';
                $priorYear = $priorWarning->details['year'] ?? '';
                $priorRefNo = 'LV-' . $priorWarning->created_at->format('Ymd') . '-' . strtoupper(substr(md5($priorWarning->id), -6));
                $violationLabel = $baseType === 'HABITUAL_TARDINESS' ? 'Habitual Tardiness' : 'Habitual Undertime';

                $priorWarningText = "<p>Records of this Office show that on <strong>{$priorDate}</strong>, a formal warning (Reference No. <strong>{$priorRefNo}</strong>) was issued to you regarding {$violationLabel} for the month of {$priorMonth} {$priorYear}. Despite said warning, records further show that you have continued to incur the same violation.</p>";

                // Rebuild body with prior warning context
                if ($baseType === 'HABITUAL_TARDINESS') {
                    $bodyText = "
{$priorWarningText}
<p>As evidenced by your Daily Time Record for the month of {$monthYear}, you have again incurred <strong>{$occurrences}</strong> times of tardiness.</p>
<p>You are hereby reminded that pursuant to Rule XVII, Section 8 on Government Office Hours of the Omnibus Rules Implementing Book V of Executive Order No. 292, as amended by CSC Memorandum Circular No. 34, s. 1998:</p>
<div class=\"quote\">\"Officers and employees who have incurred tardiness and undertime, regardless of the number of minutes per day, ten (10) times a month for at least two (2) consecutive months during the year or for at least two (2) months in a semester, shall be subject to disciplinary action.\"</div>
<p>Under Section 63(C)(10) of the 2025 Revised Rules on Administrative Cases in the Civil Service (RACCS), promulgated under CSC Resolution No. 2500357, Habitual Tardiness is classified as a <strong>Light Offense</strong> with the following penalties:</p>
<div class=\"penalties\">
    <table>
        <tr><td>a.</td><td>1st Offense &mdash;</td><td>Reprimand</td></tr>
        <tr><td>b.</td><td>2nd Offense &mdash;</td><td>Suspension of one (1) to thirty (30) days</td></tr>
        <tr><td>c.</td><td>3rd Offense &mdash;</td><td>Dismissal from the service</td></tr>
    </table>
</div>
<p>In view of the foregoing, you are hereby <strong>formally reprimanded</strong> for Habitual Tardiness as the prescribed penalty for the 1st Offense under the above-cited rules.</p>
<p>You are sternly warned that a repetition of the same or similar offense shall warrant the imposition of a more severe penalty, including suspension or dismissal from the service.</p>
<p>This letter shall form part of your 201 file.</p>
";
                } else {
                    $bodyText = "
{$priorWarningText}
<p>As evidenced by your Daily Time Record for the month of {$monthYear}, you have again incurred <strong>{$occurrences}</strong> instances of undertime.</p>
<p>You are hereby reminded that under the Civil Service Commission (CSC) Memorandum Circular No. 16, s. 2010 on the Policy on Undertime:</p>
<div class=\"quote\">\"Any officer or employee who incurs undertime, regardless of the number of minutes/hours, ten (10) times a month for at least two (2) months in a semester or at least two (2) consecutive months during the year, shall be liable for Simple Misconduct and/or Conduct Prejudicial to the Best Interest of the Service, as the case may be.\"</div>
<p>Under the 2025 Revised Rules on Administrative Cases in the Civil Service (RACCS), promulgated under CSC Resolution No. 2500357, the corresponding penalties are:</p>
<div class=\"penalties\">
    <p style=\"margin-bottom: 4px;\"><strong>For Simple Misconduct (Less Grave Offense):</strong></p>
    <table>
        <tr><td style=\"width: 20px;\">a.</td><td style=\"width: 100px;\">1st Offense &mdash;</td><td>Suspension of one (1) month and one (1) day to six (6) months</td></tr>
        <tr><td>b.</td><td>2nd Offense &mdash;</td><td>Suspension of six (6) months and one (1) day to one (1) year</td></tr>
        <tr><td>c.</td><td>3rd Offense &mdash;</td><td>Dismissal from the service</td></tr>
    </table>
    <br>
    <p style=\"margin-bottom: 4px;\"><strong>For Conduct Prejudicial to the Best Interest of the Service (Grave Offense):</strong></p>
    <table>
        <tr><td style=\"width: 20px;\">a.</td><td style=\"width: 100px;\">1st Offense &mdash;</td><td>Suspension of six (6) months and one (1) day to one (1) year</td></tr>
        <tr><td>b.</td><td>2nd Offense &mdash;</td><td>Dismissal from the service</td></tr>
    </table>
</div>
<p>In view of the foregoing, you are hereby <strong>formally reprimanded</strong> for Habitual Undertime. You are sternly warned that a repetition of the same or similar offense shall warrant the imposition of a more severe penalty.</p>
<p>This letter shall form part of your 201 file.</p>
";
                }
            }
        }

        $pdf = Pdf::loadView('exports.leave-violation-notice-pdf', [
            'letterTitle' => $letterTitle,
            'employeeName' => $violation->plantillaRecord->first_name . ' ' . $violation->plantillaRecord->last_name,
            'lastName' => $violation->plantillaRecord->last_name,
            'employeePosition' => $violation->plantillaRecord->position_title,
            'employeeOffice' => $violation->plantillaRecord->office_department,
            'prefix' => $prefix,
            'bodyText' => $bodyText,
            'facts' => $facts,
            'legalBasis' => $legalBasis,
            'duProcessNote' => 'This notice affords you the opportunity to explain before any penalty is recommended, consistent with civil-service due process.',
            'signatoryName' => $signatoryName,
            'signatoryPosition' => $signatoryPosition,
            'referenceNo' => $referenceNo,
            'ccRecipients' => $ccRecipients,
        ]);

        return $pdf->stream($referenceNo . '.pdf');
    }
}
