<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DisciplinaryCase;
use App\Models\IncidentReport;
use App\Models\RaccsAccessLog;
use App\Support\Raccs\RaccsMfaGate;
use App\Support\Violations\ViolationLinkageService;
use Illuminate\Http\Request;

/**
 * Module 10-M3 — RACCS-Confidential case registry. Access restricted to
 * Discipline Committee (same role as the IDCC
 * RACCS wall, Module 9 Stage 5) AND a verified MFA challenge within the
 * last 15 minutes (Module 9 Stage 5's "MFA restricted to..." requirement).
 */
class DisciplinaryCaseController extends Controller
{
    private const RACCS_ROLES = ['Discipline Committee'];

    /**
     * @return \Illuminate\Http\RedirectResponse|null null means access is
     *         granted; a non-null response must be returned by the caller
     *         (either an MFA challenge redirect or this never returns for a
     *         flat role denial, which aborts directly).
     */
    private function assertRaccsAccess(Request $request, string $action): ?\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $hasRole = $isSuperAdmin || $user->hasAnyRole(self::RACCS_ROLES);
        $mfaOk = $isSuperAdmin || ($hasRole && RaccsMfaGate::isVerified());
        $allowed = $hasRole && $mfaOk;

        RaccsAccessLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'outcome' => $allowed ? 'granted' : 'denied',
            'ip_address' => $request->ip(),
            'denial_reason' => !$hasRole ? 'not a RACCS-authorized role' : (!$mfaOk ? 'MFA not verified or expired' : null),
        ]);

        abort_unless($hasRole, 403, 'RACCS-Confidential access is restricted to the Discipline Committee.');

        if (!$mfaOk) {
            return redirect()->route('mfa.challenge')->with('error', 'This RACCS-Confidential action requires a verified MFA challenge.');
        }

        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->assertRaccsAccess($request, 'list_attempt')) {
            return $redirect;
        }

        $cases = DisciplinaryCase::with('personnel')->orderByDesc('created_at')->paginate(20);

        return view('disciplinary.index', compact('cases'));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->assertRaccsAccess($request, 'create_attempt')) {
            return $redirect;
        }

        $data = $request->validate([
            'personnel_id' => 'required|exists:plantilla_records,id',
            'incident_report_id' => 'nullable|exists:incident_reports,id',
            'formal_charge' => 'required|string',
            'offense_classification' => 'required|in:light,less_grave,grave',
        ]);

        // Enhancement Spec Sec. 4 — reference-only link back to the immutable
        // 201-file ledger entry already recorded for this incident (via
        // IncidentReport::finalize()), if one exists. Never auto-spawned here —
        // this controller's own human-authored formal_charge is the case itself.
        $originatingViolationId = !empty($data['incident_report_id'])
            ? app(ViolationLinkageService::class)->findForSource('IncidentReport', $data['incident_report_id'])?->violation_id
            : null;

        $case = DisciplinaryCase::create([
            ...$data,
            'originating_violation_id' => $originatingViolationId,
            'case_no' => 'RACCS-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6)),
            'created_by' => $request->user()->id,
        ]);

        if (!empty($data['incident_report_id'])) {
            IncidentReport::where('id', $data['incident_report_id'])->update(['final_track' => 'escalated']);
        }

        if ($originatingViolationId) {
            \App\Models\ViolationStatusLog::create([
                'violation_id' => $originatingViolationId,
                'status' => 'Escalated',
                'date_logged' => now(),
                'remarks' => "Admin Case #{$case->case_no} opened.",
                'logged_by' => $request->user()->id,
            ]);
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Registered Disciplinary Case',
            'description' => "Registered a formal disciplinary case (reference redacted — RACCS-confidential).",
        ]);

        return redirect()->route('disciplinary.show', $case)->with('success', "Case {$case->case_no} registered.");
    }

    public function show(Request $request, DisciplinaryCase $disciplinary)
    {
        if ($redirect = $this->assertRaccsAccess($request, 'view_attempt')) {
            return $redirect;
        }

        return view('disciplinary.show', ['case' => $disciplinary]);
    }

    public function updateStatus(Request $request, DisciplinaryCase $disciplinary)
    {
        if ($redirect = $this->assertRaccsAccess($request, 'update_attempt')) {
            return $redirect;
        }

        $data = $request->validate([
            'status' => 'required|in:registered,under_investigation,hearing,decided,appealed,closed',
            'decision' => 'nullable|string',
            'penalty' => 'nullable|string',
            'decided_at' => 'nullable|date',
            'signature_image' => 'required_if:status,decided|nullable|string',
            'signatory_name' => 'required_if:status,decided|nullable|string|max:255',
            'signatory_position' => 'nullable|string|max:255',
        ]);

        $disciplinary->update([
            'status' => $data['status'],
            'decision' => $data['decision'] ?? null,
            'penalty' => $data['penalty'] ?? null,
            'decided_at' => $data['decided_at'] ?? null,
            'closed_at' => $data['status'] === 'closed' ? now() : $disciplinary->closed_at,
        ]);

        // Module 10-M4 — a decision is e-signed (CS Form No. 11 s.2025).
        // PNPKI verification is a non-blocking hook (stays 'not_submitted').
        if ($data['status'] === 'decided' && !empty($data['signature_image'])) {
            app(\App\Support\Raccs\ESignatureService::class)->capture(
                $disciplinary,
                $data['signatory_name'],
                $data['signatory_position'] ?? null,
                $data['signature_image'],
                $request->user(),
                $request->ip(),
            );
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Updated Disciplinary Case Status',
            'description' => "Updated a disciplinary case status to {$data['status']} (reference redacted — RACCS-confidential).",
        ]);

        return back()->with('success', 'Case updated.');
    }
}
