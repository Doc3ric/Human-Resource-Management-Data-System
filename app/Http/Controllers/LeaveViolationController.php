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
    /**
     * Letter types a Tardiness/Undertime reprimand can cite as a prior action — the base
     * warnings AND any earlier reprimand, so a 3rd+ offense can reference "warned on X,
     * reprimanded on Y" rather than only ever citing the original warning. Matches the set
     * offense_tier already counts in storeReprimand()/updateLetter().
     */
    private const REPRIMAND_RELATED_TYPES = [
        'HABITUAL_TARDINESS', 'UNDERTIME', 'REPRIMAND_HABITUAL_TARDINESS', 'REPRIMAND_UNDERTIME',
    ];

    private static function reprimandRelatedTypeLabel(string $violationType): string
    {
        return match ($violationType) {
            'HABITUAL_TARDINESS' => 'Tardiness Warning',
            'UNDERTIME' => 'Undertime Warning',
            'REPRIMAND_HABITUAL_TARDINESS' => 'Tardiness Reprimand',
            'REPRIMAND_UNDERTIME' => 'Undertime Reprimand',
            default => $violationType,
        };
    }

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

        $employees = PlantillaRecord::filled()->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
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
        $employees = PlantillaRecord::filled()->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
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
            // Who actually created this entry — the recorded-entries table's "Created By"
            // column reads this via issuedBy(); it was never being set here, so every row
            // fell back to displaying "System" regardless of who added it.
            'issued_by' => $request->user()->id,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Added Leave Record',
            'description' => "Added Leave Record for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
        ]);

        return redirect()->route('leave-violations.recorded-entries')->with('success', 'Leave record saved successfully.');
    }

    public function updateRecord(Request $request, LeaveViolation $violation)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Leave Violations'), 403);
        abort_unless($violation->violation_type === 'LWOP_TARDINESS', 404);

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
            'reference_no' => 'nullable|string',
            'creator_name' => 'nullable|string',
        ]);

        $violation->update([
            'plantilla_record_id' => $validated['plantilla_record_id'],
            'details' => array_merge($violation->details ?? [], $validated),
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Updated Leave Record',
            'description' => "Updated Leave Record for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
        ]);

        return redirect()->route('leave-violations.recorded-entries')->with('success', 'Leave record updated successfully.');
    }

    public function destroyRecord(Request $request, LeaveViolation $violation)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Leave Violations'), 403);
        abort_unless($violation->violation_type === 'LWOP_TARDINESS', 404);

        $name = $violation->plantillaRecord->first_name . ' ' . $violation->plantillaRecord->last_name;
        $violation->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Deleted Leave Record',
            'description' => "Deleted Leave Record for {$name}.",
        ]);

        return redirect()->route('leave-violations.recorded-entries')->with('success', 'Leave record deleted successfully.');
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

    /**
     * Module 2B.4/2B.6 — every issued due-process letter in one place: the
     * manual Tardy/Undertime/Reprimand/Show-Cause notices plus any violation
     * that has since been coordinated with payroll (status 'escalated' is
     * only ever set by LeaveViolationLetterService::issuePayrollCoordination,
     * so it's a safe stand-in for "has a payroll coordination letter").
     */
    public function generatedLetters(Request $request)
    {
        $letterTypes = ['HABITUAL_TARDINESS', 'UNDERTIME', 'REPRIMAND_HABITUAL_TARDINESS', 'REPRIMAND_UNDERTIME', 'SHOW_CAUSE'];

        $baseQuery = fn () => LeaveViolation::query()->where(function ($q) use ($letterTypes) {
            $q->whereIn('violation_type', $letterTypes)->orWhere('status', 'escalated');
        });

        $letters = $baseQuery()->with('plantillaRecord')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $total = $baseQuery()->count();
        $tardyUndertime = LeaveViolation::whereIn('violation_type', ['HABITUAL_TARDINESS', 'UNDERTIME'])->count();
        $reprimand = LeaveViolation::whereIn('violation_type', ['REPRIMAND_HABITUAL_TARDINESS', 'REPRIMAND_UNDERTIME'])->count();
        $showCause = LeaveViolation::where('violation_type', 'SHOW_CAUSE')->count();
        $payrollCoordination = LeaveViolation::where('status', 'escalated')->count();

        $metrics = [
            'total' => $total,
            'tardy_undertime' => $tardyUndertime,
            'tardy_undertime_pct' => $total > 0 ? round(($tardyUndertime / $total) * 100, 1) : 0,
            'reprimand' => $reprimand,
            'reprimand_pct' => $total > 0 ? round(($reprimand / $total) * 100, 1) : 0,
            'show_cause' => $showCause,
            'show_cause_pct' => $total > 0 ? round(($showCause / $total) * 100, 1) : 0,
            'payroll_coordination' => $payrollCoordination,
            'payroll_coordination_pct' => $total > 0 ? round(($payrollCoordination / $total) * 100, 1) : 0,
        ];

        // Data for the "New Letter" popup forms (Tardy/Undertime, Reprimand, Show-Cause, Payroll Coordination).
        $employees = PlantillaRecord::filled()->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();

        $employeeIdsWithWarnings = LeaveViolation::whereIn('violation_type', self::REPRIMAND_RELATED_TYPES)
            ->pluck('plantilla_record_id')
            ->unique();

        // Reprimand is only issuable for a repeat offense against an existing warning, so its
        // "New Reprimand Letter" modal offers only these employees, not the full roster above.
        $employeesWithPriorWarnings = PlantillaRecord::filled()
            ->whereIn('id', $employeeIdsWithWarnings)
            ->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();

        // Not just the original warning — a 3rd+ offense reprimand can also cite an earlier
        // reprimand as a prior action, so this includes both warning and reprimand letters.
        $priorWarnings = LeaveViolation::whereIn('violation_type', self::REPRIMAND_RELATED_TYPES)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'plantilla_record_id' => $v->plantilla_record_id,
                    'violation_type' => $v->violation_type,
                    'type_label' => self::reprimandRelatedTypeLabel($v->violation_type),
                    'month' => $v->details['month'] ?? '',
                    'year' => $v->details['year'] ?? '',
                    'issued_at' => $v->issued_at ? $v->issued_at->format('F d, Y') : $v->created_at->format('F d, Y'),
                ];
            });

        $eligibleViolations = LeaveViolation::whereIn('violation_type', ['AWOL', 'HABITUAL_ABSENTEEISM', 'LWOP', 'LWOP_TARDINESS'])
            ->where('status', '!=', 'escalated')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'plantilla_record_id' => $v->plantilla_record_id,
                    'violation_type' => $v->violation_type,
                    'type_label' => ucwords(strtolower(str_replace('_', ' ', $v->violation_type))),
                    'created_at' => $v->created_at->format('F d, Y'),
                ];
            });
        $employeeIdsEligible = $eligibleViolations->pluck('plantilla_record_id')->unique()->values();

        return view('leave-violations.generated-letters', compact(
            'letters', 'metrics', 'employees', 'employeesWithPriorWarnings', 'employeeIdsWithWarnings', 'priorWarnings',
            'eligibleViolations', 'employeeIdsEligible'
        ));
    }

    public function createTardy()
    {
        $employees = PlantillaRecord::filled()->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();
        return view('leave-violations.create-tardy', compact('employees'));
    }

    public function createUndertime()
    {
        $employees = PlantillaRecord::filled()->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();
        return view('leave-violations.create-undertime', compact('employees'));
    }

    public function createReprimand()
    {
        // Get employees who have at least one prior warning or reprimand on record
        $employeeIdsWithWarnings = LeaveViolation::whereIn('violation_type', self::REPRIMAND_RELATED_TYPES)
            ->pluck('plantilla_record_id')
            ->unique();

        // A reprimand is only issuable for a repeat offense against an existing warning,
        // so only offer employees who actually have one on record — not the full roster.
        $employees = PlantillaRecord::filled()
            ->whereIn('id', $employeeIdsWithWarnings)
            ->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();

        // Get prior warnings/reprimands for JS lookup — not just the original warning, so a
        // 3rd+ offense reprimand can also cite an earlier reprimand as a prior action.
        $priorWarnings = LeaveViolation::whereIn('violation_type', self::REPRIMAND_RELATED_TYPES)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'plantilla_record_id' => $v->plantilla_record_id,
                    'violation_type' => $v->violation_type,
                    'type_label' => self::reprimandRelatedTypeLabel($v->violation_type),
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
            'prior_warning_ids' => 'required|array',
            'prior_warning_ids.*' => 'exists:leave_violations,id',
            'month' => 'required|string',
            'year' => 'required|numeric',
            'occurrences' => 'required|string',
            'prefix' => 'nullable|string',
            'position_title' => 'nullable|string',
            'office_department' => 'nullable|string',
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
                'position_title' => $validated['position_title'] ?? null,
                'office_department' => $validated['office_department'] ?? null,
                'signatory_name' => $validated['signatory_name'],
                'signatory_position' => $validated['signatory_position'],
                'prior_warning_ids' => $validated['prior_warning_ids'],
                'manual_entry' => true,
            ],
        ]);

        $priorWarnings = LeaveViolation::whereIn('id', $validated['prior_warning_ids'])->orderBy('issued_at')->get();

        // Generate reprimand PDF
        $this->letters->issueReprimand($violation, $request->user(), $priorWarnings);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Generated Reprimand Letter',
            'description' => "Generated Reprimand ({$validated['base_violation_type']}) for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
        ]);

        return redirect()->route('leave-violations.generated-letters')->with('success', 'Reprimand letter generated successfully and saved to records.');
    }

    /** Module 2B.4(e) — Show-Cause / notice to explain, before any penalty recommendation. */
    public function createShowCause(Request $request)
    {
        $employees = PlantillaRecord::filled()->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();

        // If launched from an existing detected violation ("Issue Show-Cause" on the watchlist),
        // pre-fill the employee and let the grounds reference that violation's own facts.
        $sourceViolation = $request->filled('violation_id') ? LeaveViolation::find($request->integer('violation_id')) : null;

        return view('leave-violations.create-show-cause', compact('employees', 'sourceViolation'));
    }

    public function storeShowCause(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('add Leave Violations'), 403);

        $validated = $request->validate([
            'plantilla_record_id' => 'required|exists:plantilla_records,id',
            'grounds' => 'required|string',
            'source_violation_id' => 'nullable|exists:leave_violations,id',
            'signatory_name' => 'required|string',
            'signatory_position' => 'required|string',
        ]);

        $offenseTier = LeaveViolation::where('plantilla_record_id', $validated['plantilla_record_id'])
            ->where('violation_type', 'SHOW_CAUSE')
            ->count() + 1;

        $violation = LeaveViolation::create([
            'plantilla_record_id' => $validated['plantilla_record_id'],
            'violation_type' => 'SHOW_CAUSE',
            'rating_period' => BatchRenewalController::currentRatingPeriod(),
            'offense_tier' => $offenseTier,
            'status' => 'detected',
            'details' => [
                'grounds' => $validated['grounds'],
                'source_violation_id' => $validated['source_violation_id'] ?? null,
                'signatory_name' => $validated['signatory_name'],
                'signatory_position' => $validated['signatory_position'],
                'manual_entry' => true,
            ],
        ]);

        $this->letters->issue($violation, $request->user());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Issued Show-Cause Order',
            'description' => "Issued a Show-Cause order for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
        ]);

        return redirect()->route('leave-violations.generated-letters')->with('success', 'Show-Cause order generated and saved to records.');
    }

    public function editLetter(Request $request, LeaveViolation $violation)
    {
        $employees = PlantillaRecord::filled()->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();

        // Reprimand letters reference a prior warning's date/reference no. —
        $priorWarning = null;
        $priorWarnings = collect();
        $availablePriorWarnings = collect();

        if (str_starts_with($violation->violation_type, 'REPRIMAND_')) {
            $priorWarningIds = $violation->details['prior_warning_ids'] ?? [];
            if (empty($priorWarningIds) && isset($violation->details['prior_warning_id'])) {
                $priorWarningIds = [$violation->details['prior_warning_id']];
            }
            if (!empty($priorWarningIds)) {
                $priorWarnings = LeaveViolation::whereIn('id', $priorWarningIds)->orderBy('issued_at')->get();
                $priorWarning = $priorWarnings->first();
            }

            $baseType = str_replace('REPRIMAND_', '', $violation->violation_type);
            $availablePriorWarnings = LeaveViolation::where('plantilla_record_id', $violation->plantilla_record_id)
                ->where('violation_type', $baseType)
                ->where('id', '!=', $violation->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if ($request->ajax() || $request->wantsJson() || $request->boolean('modal')) {
            return view('leave-violations.edit-letter-modal-content', compact('violation', 'employees', 'priorWarning', 'priorWarnings', 'availablePriorWarnings'));
        }

        return view('leave-violations.edit-letter', compact('violation', 'employees', 'priorWarning', 'priorWarnings', 'availablePriorWarnings'));
    }

    public function updateLetter(Request $request, LeaveViolation $violation)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Leave Violations'), 403);

        // Payroll coordination reuses the original AWOL/LWOP/Habitual-Absenteeism
        // record (only 'status' flips to 'escalated'), so the employee and
        // violation_type it's tied to are fixed — only the DROP/DEDUCT choice
        // can be edited here.
        if ($violation->status === 'escalated') {
            $validated = $request->validate([
                'action_type' => 'required|in:DROP,DEDUCT',
            ]);

            $this->letters->issuePayrollCoordination($violation, $request->user(), $validated['action_type']);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Updated Letter',
                'description' => "Updated Payroll Coordination ({$validated['action_type']}) letter for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
            ]);

            return redirect()->route('leave-violations.generated-letters')->with('success', 'Letter updated successfully.');
        }

        if ($violation->violation_type === 'SHOW_CAUSE') {
            $validated = $request->validate([
                'plantilla_record_id' => 'required|exists:plantilla_records,id',
                'grounds' => 'required|string',
                'signatory_name' => 'required|string',
                'signatory_position' => 'required|string',
            ]);

            $violation->update([
                'plantilla_record_id' => $validated['plantilla_record_id'],
                'details' => array_merge($violation->details, [
                    'grounds' => $validated['grounds'],
                    'signatory_name' => $validated['signatory_name'],
                    'signatory_position' => $validated['signatory_position'],
                ]),
            ]);

            $this->letters->issue($violation, $request->user());

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Updated Letter',
                'description' => "Updated Show-Cause order for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
            ]);

            return redirect()->route('leave-violations.generated-letters')->with('success', 'Letter updated successfully.');
        }

        $isReprimand = str_starts_with($request->input('letter_type', $violation->violation_type), 'REPRIMAND_');

        $validated = $request->validate([
            'plantilla_record_id' => 'required|exists:plantilla_records,id',
            'letter_type' => 'required|in:HABITUAL_TARDINESS,UNDERTIME,REPRIMAND_HABITUAL_TARDINESS,REPRIMAND_UNDERTIME',
            'month' => 'required|string',
            'year' => 'required|numeric',
            'occurrences' => 'required|string',
            'prefix' => 'nullable|string',
            'position_title' => 'nullable|string',
            'office_department' => 'nullable|string',
            'reference_no' => 'nullable|string',
            'creator_name' => 'nullable|string',
            'signatory_name' => 'required|string',
            'signatory_position' => 'required|string',
            'prior_warning_ids' => 'nullable|array',
            'prior_warning_ids.*' => 'exists:leave_violations,id',
            'prior_warning_date_override' => 'nullable|date',
            'prior_warning_ref_override' => 'nullable|string|max:100',
            'prior_warning_month_override' => 'nullable|string',
            'prior_warning_year_override' => 'nullable|numeric',
        ]);

        $overrides = $isReprimand ? [
            'prior_warning_ids' => $validated['prior_warning_ids'] ?? [],
            'prior_warning_date_override' => $validated['prior_warning_date_override'] ?? null,
            'prior_warning_ref_override' => $validated['prior_warning_ref_override'] ?? null,
            'prior_warning_month_override' => $validated['prior_warning_month_override'] ?? null,
            'prior_warning_year_override' => $validated['prior_warning_year_override'] ?? null,
        ] : [];

        $violation->update([
            'plantilla_record_id' => $validated['plantilla_record_id'],
            'violation_type' => $validated['letter_type'],
            'details' => array_merge($violation->details, [
                'month' => $validated['month'],
                'year' => $validated['year'],
                'occurrences' => $validated['occurrences'],
                'prefix' => $validated['prefix'] ?? '',
                'position_title' => $validated['position_title'] ?? null,
                'office_department' => $validated['office_department'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
                'creator_name' => $validated['creator_name'] ?? null,
                'signatory_name' => $validated['signatory_name'],
                'signatory_position' => $validated['signatory_position'],
            ], $overrides),
        ]);

        // Regenerate PDF — use reprimand method if applicable
        if (str_starts_with($validated['letter_type'], 'REPRIMAND_')) {
            $priorWarningIds = $violation->details['prior_warning_ids'] ?? [];
            if (empty($priorWarningIds) && isset($violation->details['prior_warning_id'])) {
                $priorWarningIds = [$violation->details['prior_warning_id']];
            }
            $priorWarnings = !empty($priorWarningIds) ? LeaveViolation::whereIn('id', $priorWarningIds)->orderBy('issued_at')->get() : collect();
            $this->letters->issueReprimand($violation, $request->user(), $priorWarnings);
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

    /**
     * Module 2B.7 due-process guard: resolution/escalation may only happen
     * after a notice has actually gone out (notice_pending/issued) or the
     * employee has responded — never directly from a bare "detected" flag,
     * so the response window can't be silently skipped.
     */
    public function markResolved(Request $request, LeaveViolation $violation)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Leave Violations'), 403);
        abort_unless(
            in_array($violation->status, ['notice_pending', 'issued', 'responded'], true),
            422,
            'A notice must be issued (and, where due process requires it, a response window observed) before this violation can be resolved.'
        );
        $violation->update(['status' => 'resolved']);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Resolved Leave Violation',
            'description' => "Marked violation #{$violation->id} ({$violation->violation_type}) resolved.",
        ]);

        return back()->with('success', 'Violation marked resolved.');
    }

    /**
     * Module 2B.7 — records that the employee responded within the notice
     * window, before any resolution/escalation. The response text itself
     * (if any) is stored in details, not a new column, matching the existing
     * flexible-JSON pattern for violation-specific facts.
     */
    public function markResponded(Request $request, LeaveViolation $violation)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Leave Violations'), 403);
        abort_unless(
            in_array($violation->status, ['notice_pending', 'issued'], true),
            422,
            'Only a violation with an issued notice can be marked as responded.'
        );

        $validated = $request->validate(['response_notes' => 'nullable|string']);

        $violation->update([
            'status' => 'responded',
            'details' => array_merge($violation->details, [
                'response_notes' => $validated['response_notes'] ?? null,
                'responded_at' => now()->toDateTimeString(),
                'response_recorded_by' => $request->user()->id,
            ]),
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Recorded Leave Violation Response',
            'description' => "Recorded employee response for violation #{$violation->id} ({$violation->violation_type}).",
        ]);

        return back()->with('success', 'Employee response recorded.');
    }

    /** Module 2B.3 — the shared severity ranking used by both the watchlist and its CSV/PDF export. */
    private function severityCaseSql(): string
    {
        return "CASE violation_type
            WHEN 'AWOL' THEN 1
            WHEN 'HABITUAL_ABSENTEEISM' THEN 2
            WHEN 'LWOP' THEN 3
            WHEN 'HABITUAL_TARDINESS' THEN 4
            WHEN 'REPRIMAND_HABITUAL_TARDINESS' THEN 4
            WHEN 'UNDERTIME' THEN 5
            WHEN 'REPRIMAND_UNDERTIME' THEN 5
            WHEN 'LEAVE_DISAPPROVED' THEN 6
            WHEN 'LATE_FILING_SICK_LEAVE' THEN 7
            WHEN 'LATE_FILING_VACATION_LEAVE' THEN 7
            WHEN 'LATE_FILING_OTHER_LEAVE' THEN 7
            WHEN 'MISSING_SUPPORTING_DOCS' THEN 8
            WHEN 'LWOP_TARDINESS' THEN 9
            ELSE 99
        END";
    }

    /** Module 2B.3 — the dynamic, filterable violation watchlist covering every violation type (not just the manual LWOP ledger). */
    public function watchlist(Request $request)
    {
        $query = LeaveViolation::with('plantillaRecord')
            ->when($request->filled('office'), fn ($q) => $q->whereHas('plantillaRecord', fn ($q2) => $q2->where('office_department', $request->string('office'))))
            ->when($request->filled('violation_type'), fn ($q) => $q->where('violation_type', $request->string('violation_type')))
            ->when($request->filled('offense_tier'), fn ($q) => $q->where('offense_tier', $request->integer('offense_tier')))
            ->when($request->filled('rating_period'), fn ($q) => $q->where('rating_period', $request->string('rating_period')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));

        $entries = $query->orderByRaw($this->severityCaseSql())->orderByDesc('created_at')->paginate(20)->withQueryString();

        $offices = PlantillaRecord::whereNotNull('office_department')->distinct()->orderBy('office_department')->pluck('office_department');
        $ratingPeriods = LeaveViolation::whereNotNull('rating_period')->distinct()->orderByDesc('rating_period')->pluck('rating_period');
        $violationTypes = LeaveViolation::distinct()->orderBy('violation_type')->pluck('violation_type');

        $byType = (clone $query)->reorder()->selectRaw('violation_type, count(*) as c')->groupBy('violation_type')->pluck('c', 'violation_type');
        $byOffice = LeaveViolation::join('plantilla_records', 'plantilla_records.id', '=', 'leave_violations.plantilla_record_id')
            ->selectRaw('plantilla_records.office_department as office, count(*) as c')
            ->groupBy('plantilla_records.office_department')
            ->pluck('c', 'office');

        return view('leave-violations.watchlist', compact('entries', 'offices', 'ratingPeriods', 'violationTypes', 'byType', 'byOffice'));
    }

    /** Module 2B.3 — CSV export of the watchlist, same filters as the on-screen view. */
    public function exportWatchlistCsv(Request $request)
    {
        $query = LeaveViolation::with('plantillaRecord')
            ->when($request->filled('office'), fn ($q) => $q->whereHas('plantillaRecord', fn ($q2) => $q2->where('office_department', $request->string('office'))))
            ->when($request->filled('violation_type'), fn ($q) => $q->where('violation_type', $request->string('violation_type')))
            ->when($request->filled('offense_tier'), fn ($q) => $q->where('offense_tier', $request->integer('offense_tier')))
            ->when($request->filled('rating_period'), fn ($q) => $q->where('rating_period', $request->string('rating_period')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByRaw($this->severityCaseSql())->orderByDesc('created_at');

        $entries = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Leave_Violation_Watchlist.csv"',
        ];

        return response()->streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Provincial Government of Bukidnon — PHRMO — Leave Violation Watchlist']);
            fputcsv($handle, ['Generated: ' . now()->format('F d, Y h:i A')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Name', 'Office', 'Violation Type', 'Offense Tier', 'Rating Period', 'Status', 'Details']);

            foreach ($entries as $entry) {
                $record = $entry->plantillaRecord;
                fputcsv($handle, [
                    $record ? "{$record->last_name}, {$record->first_name}" : 'Unknown',
                    $record->office_department ?? 'N/A',
                    $entry->violation_type,
                    $entry->offense_tier,
                    $entry->rating_period,
                    $entry->status,
                    json_encode($entry->details),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Legal basis: Omnibus Rules on Leave (CSC MC No. 41 s.1998) Secs. 34, 51, 53, 62, 63; 2025 RACCS Rule 10 §63 (habitual tardiness/absenteeism); R.A. 6713.']);

            fclose($handle);
        }, 'Leave_Violation_Watchlist.csv', $headers);
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

    /** M2B.6 — Generate Letters landing form: pick an employee's existing AWOL/LWOP/Habitual-Absenteeism entry and coordinate it with payroll. */
    public function createPayrollCoordination()
    {
        $employees = PlantillaRecord::filled()->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();

        // Only violations that haven't already been coordinated with payroll
        // ('escalated' is set exclusively by issuePayrollCoordination()) are eligible.
        $eligibleViolations = LeaveViolation::whereIn('violation_type', ['AWOL', 'HABITUAL_ABSENTEEISM', 'LWOP', 'LWOP_TARDINESS'])
            ->where('status', '!=', 'escalated')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'plantilla_record_id' => $v->plantilla_record_id,
                    'violation_type' => $v->violation_type,
                    'type_label' => ucwords(strtolower(str_replace('_', ' ', $v->violation_type))),
                    'created_at' => $v->created_at->format('F d, Y'),
                ];
            });

        $employeeIdsEligible = $eligibleViolations->pluck('plantilla_record_id')->unique()->values();

        return view('leave-violations.create-payroll-coordination', compact('employees', 'eligibleViolations', 'employeeIdsEligible'));
    }

    /** M2B.6 — Issue Office/Payroll Coordination Letters. */
    public function issuePayrollCoordination(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('add Leave Violations'), 403);

        $validated = $request->validate([
            'violation_id' => 'required|exists:leave_violations,id',
            'action_type' => 'required|in:DROP,DEDUCT',
        ]);

        $violation = LeaveViolation::findOrFail($validated['violation_id']);

        $this->letters->issuePayrollCoordination($violation, $request->user(), $validated['action_type']);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Issued Payroll Coordination',
            'description' => "Issued a {$validated['action_type']} payroll coordination letter for {$violation->plantillaRecord->first_name} {$violation->plantillaRecord->last_name}.",
        ]);

        return redirect()->route('leave-violations.generated-letters')->with('success', 'Payroll Coordination letter generated successfully and saved to records.');
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
            $hasOverrides = !empty(array_filter([
                $violation->details['prior_warning_date_override'] ?? null,
                $violation->details['prior_warning_ref_override'] ?? null,
                $violation->details['prior_warning_month_override'] ?? null,
                $violation->details['prior_warning_year_override'] ?? null,
            ]));

            if ($priorWarning || $hasOverrides) {
                // Editable overrides (from Edit Letter) take precedence over
                // the linked prior-warning record's own values.
                $dateOverride = $violation->details['prior_warning_date_override'] ?? null;
                $priorDate = $dateOverride
                    ? \Illuminate\Support\Carbon::parse($dateOverride)->format('F d, Y')
                    : ($priorWarning
                        ? ($priorWarning->issued_at ? $priorWarning->issued_at->format('F d, Y') : $priorWarning->created_at->format('F d, Y'))
                        : '');
                $priorMonth = $violation->details['prior_warning_month_override'] ?? $priorWarning?->details['month'] ?? '';
                $priorYear = $violation->details['prior_warning_year_override'] ?? $priorWarning?->details['year'] ?? '';
                $priorRefNo = $violation->details['prior_warning_ref_override']
                    ?? ($priorWarning ? 'LV-' . $priorWarning->created_at->format('Ymd') . '-' . strtoupper(substr(md5($priorWarning->id), -6)) : '');
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
