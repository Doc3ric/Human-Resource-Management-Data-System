<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Support\Recruitment\RecruitmentLifecycleService;
use App\Support\Renewal\DisposalAuthorizationService;
use Illuminate\Http\Request;

/**
 * Module 2 — Recruitment View & NAP Retention. Read-side only: this
 * controller never edits an Applicant's application data (that stays
 * Track B's RecruitmentController) — it only ever reads/re-stages the
 * lifecycle fields (is_filled/date_filled/lifecycle_stage) added for this
 * module and records disposal authorizations.
 *
 * Applicant has no SoftDeletes and Track B's TWG/evaluation tables carry
 * foreign keys to it — actually hard-deleting a disposal-eligible
 * Applicant row here would risk orphaning Track B's data with no
 * coordination. So this stops at "compute eligibility, surface it,
 * require a NAP Form 3 authorization on file" and does not implement the
 * final hard-delete step; that's flagged as Track B/joint-integration
 * follow-up, not silently skipped.
 */
class RecruitmentLifecycleController extends Controller
{
    public function __construct(
        private readonly RecruitmentLifecycleService $lifecycle,
        private readonly DisposalAuthorizationService $disposalAuthorizations,
    ) {
    }

    public function index(Request $request)
    {
        $stage = $request->query('stage', RecruitmentLifecycleService::STAGE_ACTIVE);
        $search = $request->query('search');

        $applicants = Applicant::query()
            ->where('lifecycle_stage', $stage)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('last_name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('item_no', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('applied_at')
            ->paginate(25)
            ->withQueryString();

        $counts = [
            RecruitmentLifecycleService::STAGE_ACTIVE => Applicant::where('lifecycle_stage', RecruitmentLifecycleService::STAGE_ACTIVE)->count(),
            RecruitmentLifecycleService::STAGE_ARCHIVED => Applicant::where('lifecycle_stage', RecruitmentLifecycleService::STAGE_ARCHIVED)->count(),
            RecruitmentLifecycleService::STAGE_VALUELESS_QUEUE => Applicant::where('lifecycle_stage', RecruitmentLifecycleService::STAGE_VALUELESS_QUEUE)->count(),
        ];

        return view('recruitment-lifecycle.index', [
            'applicants' => $applicants,
            'stage' => $stage,
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    /**
     * Module 2.3 — record a disposal authorization (NAP Form No. 3
     * reference + optional scan) for a Valueless-Holding-Queue record.
     * Does not delete the record; see class docblock.
     */
    public function authorizeDisposal(Request $request, Applicant $applicant)
    {
        abort_unless($applicant->lifecycle_stage === RecruitmentLifecycleService::STAGE_VALUELESS_QUEUE, 422, 'Only Valueless Holding Queue records may be authorized for disposal.');

        $request->validate([
            'nap_form_reference' => 'required|string|max:100',
            'nap_form_file' => 'nullable|file|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('nap_form_file')) {
            $filePath = $request->file('nap_form_file')->store('disposal-authorizations', 'local');
        }

        $this->disposalAuthorizations->authorize(
            $applicant,
            $request->input('nap_form_reference'),
            $filePath,
            $request->user(),
        );

        return back()->with('success', 'Disposal authorization recorded for this record (NAP Form No. 3 on file). The record itself is retained pending the formal disposal process.');
    }
}
