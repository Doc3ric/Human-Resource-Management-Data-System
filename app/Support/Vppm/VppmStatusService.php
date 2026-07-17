<?php

namespace App\Support\Vppm;

use App\Models\PublicationRequest;
use App\Models\User;
use App\Support\Notifications\HrmoNotifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * VPPM status machine — RA 7041 / 2025 ORAOHRA Sec. 26/30/31. Same
 * "computed days-remaining -> status flip -> notify" shape as
 * DetailOrderStatusService (the spec explicitly calls out that pattern).
 *
 * PUBLICATION_COMPLIANT and VALID are collapsed into a single transition:
 * the moment the posting period is satisfied with all 3 sites confirmed,
 * the request goes straight to VALID (nothing meaningful happens in the
 * gap between "compliant" and "valid" — they're the same real-world
 * moment). Compliance itself is still recorded via posting_actual_end_date
 * and the Spatie activity log entry, so nothing is lost, it's just not a
 * separate resting DB status.
 */
class VppmStatusService
{
    /** Spec Sec.6/dashboard: "configurable lead time e.g. 30 days out" — same shape as DetailOrderStatusService's hardcoded 30. */
    private const NEAR_EXPIRY_LEAD_DAYS = 30;

    public function __construct(private readonly HrmoNotifier $notifier)
    {
    }

    // ── Step 3: Dept Head signs, hard-blocks submission without it ─────────

    public function moveToPendingSignature(PublicationRequest $request): void
    {
        if ($request->status !== 'DRAFT') {
            throw ValidationException::withMessages(['status' => 'Only a DRAFT request can move to Pending Signature.']);
        }

        $request->update(['status' => 'PENDING_SIGNATURE']);
    }

    /**
     * Spec Sec.5 step 3 — "status SUBMITTED_TO_CSC_FO is only reachable
     * after signed_by_id and signed_date are populated — hard-block
     * submission without a captured signature." Signature capture itself
     * happens via ESignatureService against this request (signable_type =
     * PublicationRequest::class); this method only enforces the gate.
     */
    public function submitToCscFo(PublicationRequest $request, string $submittedDate, ?string $receivingCopyPath): void
    {
        if ($request->status !== 'PENDING_SIGNATURE') {
            throw ValidationException::withMessages(['status' => 'Request must be Pending Signature before submission.']);
        }

        if (!$request->is_signed) {
            throw ValidationException::withMessages(['signature' => 'Cannot submit to CSC FO — CS Form No. 9 has not been signed yet.']);
        }

        $request->update([
            'status' => 'SUBMITTED_TO_CSC_FO',
            'submitted_to_csc_fo_date' => $submittedDate,
            'csc_fo_receiving_copy_path' => $receivingCopyPath,
        ]);
    }

    /**
     * Spec Sec.5 step 5 — called after a posting_site_log row is created.
     * Once all 3 sites are logged, auto-advances SUBMITTED_TO_CSC_FO/POSTED
     * straight to PUBLICATION_ACTIVE, using the earliest of the 3 posted
     * dates as the Sec.26 reckoning date.
     */
    public function recordSitePosting(PublicationRequest $request): void
    {
        $request->refresh();
        $siteCount = $request->postingSiteLogs()->count();

        if (!in_array($request->status, ['SUBMITTED_TO_CSC_FO', 'POSTED'], true)) {
            return;
        }

        if ($siteCount >= 3) {
            $earliest = $request->postingSiteLogs()->min('posted_date');
            $request->update([
                'status' => 'PUBLICATION_ACTIVE',
                'posting_start_date' => $request->posting_start_date ?? $earliest,
            ]);
            return;
        }

        if ($siteCount > 0 && $request->status === 'SUBMITTED_TO_CSC_FO') {
            $request->update(['status' => 'POSTED']);
        }
    }

    /** The daily sweep — called by vppm:check-publication-status. */
    public function checkStatus(PublicationRequest $request): void
    {
        match ($request->status) {
            'PUBLICATION_ACTIVE', 'PUBLICATION_DEFICIENT' => $this->checkPostingCompliance($request),
            'VALID' => $this->checkExpiry($request),
            'NEAR_EXPIRY' => $this->checkExpiry($request, alreadyWarned: true),
            default => null, // DRAFT/PENDING_SIGNATURE/SUBMITTED_TO_CSC_FO/POSTED/EXPIRED/FILLED/CANCELLED — nothing to auto-sweep
        };
    }

    private function checkPostingCompliance(PublicationRequest $request): void
    {
        $target = $request->posting_compliance_date;
        if (!$target || now()->startOfDay()->lt($target)) {
            return; // still within the required posting window
        }

        if ($request->all_sites_confirmed) {
            $validityStart = now()->startOfDay();
            $request->update([
                'status' => 'VALID',
                'posting_actual_end_date' => $validityStart->toDateString(),
                'validity_start_date' => $validityStart,
                'validity_end_date' => $validityStart->copy()->addMonths($request->validity_months),
            ]);
            return;
        }

        if ($request->status !== 'PUBLICATION_DEFICIENT') {
            $request->update(['status' => 'PUBLICATION_DEFICIENT']);
            $this->notifier->notifyDivisionHead(
                'Publication Deficient — Missing Posting Sites',
                "Vacancy publication #{$request->id} ({$request->vacantPosition->position_title}) reached its minimum posting period without all 3 conspicuous-place postings confirmed. This blocks downstream HRMPSB deliberation."
            );
        }
    }

    private function checkExpiry(PublicationRequest $request, bool $alreadyWarned = false): void
    {
        $days = $request->days_to_expiry;
        if ($days === null) {
            return;
        }

        if ($days <= 0) {
            $request->update(['status' => 'EXPIRED']);
            $this->notifier->notifyDivisionHead(
                'Vacancy Publication Expired',
                "Publication #{$request->id} ({$request->vacantPosition->position_title}) has passed its validity end date ({$request->validity_end_date->format('F d, Y')}) unfilled. Republication is required to resume."
            );
            return;
        }

        if (!$alreadyWarned && $days <= self::NEAR_EXPIRY_LEAD_DAYS) {
            $request->update(['status' => 'NEAR_EXPIRY']);
            $this->notifier->notifyDivisionHead(
                'Vacancy Publication Nearing Expiry',
                "Publication #{$request->id} ({$request->vacantPosition->position_title}) expires in {$days} day(s) ({$request->validity_end_date->format('F d, Y')})."
            );
        }
    }

    public function markFilled(PublicationRequest $request): void
    {
        $request->update(['status' => 'FILLED']);
    }

    public function cancel(PublicationRequest $request, string $reason): void
    {
        $request->update(['status' => 'CANCELLED', 'edit_reason' => $reason]);
    }

    /**
     * Sec. 26's express carve-out — spelling/missing-parenthetical-title
     * corrections stay on the SAME row (no new version, no status reset),
     * but the flag + Spatie's automatic dirty-diff keep it fully audited.
     */
    public function applyExemptEdit(PublicationRequest $request, array $changes, ?User $actor = null): void
    {
        $request->fill($changes);
        $request->is_section26_exempt_edit = true;
        $request->save();
    }

    /**
     * Sec. 26 — any other edit to a published field after submission
     * spawns a new version rather than overwriting. The old row is left
     * exactly as-is (an immutable history record of what was actually
     * published); the new row starts at DRAFT and becomes the vacancy's
     * "current" request via version_no ordering.
     */
    public function republish(PublicationRequest $request, array $changes, string $editReason, ?User $actor = null): PublicationRequest
    {
        if ($request->can_edit_in_place) {
            throw ValidationException::withMessages(['status' => 'This request has not been submitted yet — edit it directly instead of republishing.']);
        }

        $actor ??= Auth::user();

        $carryOver = $request->only([
            'agency_name', 'agency_contact_person', 'agency_contact_number', 'agency_contact_email',
            'submission_mode', 'posting_min_required_days', 'validity_months',
        ]);

        return PublicationRequest::create(array_merge($carryOver, $changes, [
            'vacant_position_id' => $request->vacant_position_id,
            'version_no' => $request->version_no + 1,
            'supersedes_request_id' => $request->id,
            'prepared_by_id' => $actor?->id,
            'status' => 'DRAFT',
            'edit_reason' => $editReason,
            'is_section26_exempt_edit' => false,
            // Fresh cycle — posting clock resets per spec Sec.5 step 9.
            'submitted_to_csc_fo_date' => null,
            'csc_fo_receiving_copy_path' => null,
            'posting_start_date' => null,
            'posting_actual_end_date' => null,
            'validity_start_date' => null,
            'validity_end_date' => null,
            'validity_extended_reason' => null,
        ]));
    }
}
