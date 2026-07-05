<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ContractRenewal;
use App\Models\PlantillaRecord;
use App\Support\Renewal\RenewalAuthority;
use App\Support\Renewal\RenewalCommitService;
use App\Support\Renewal\RenewalSignalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BatchRenewalController extends Controller
{
    public function __construct(
        private readonly RenewalCommitService $commitService,
        private readonly RenewalSignalService $signalService,
    ) {
    }

    /** Module 1A.1: current tracking semester, e.g. "2026_1st_semester". */
    public static function currentRatingPeriod(): string
    {
        return now()->year . '_' . (now()->month <= 6 ? '1st' : '2nd') . '_semester';
    }
    // ── Index: filter + selection dashboard ──────────────────────────────────

    public function index(Request $request)
    {
        $offices = PlantillaRecord::whereIn('employment_status', ['CASUAL', 'Casual', 'Cas', 'JO', 'Job Order', 'J.O.'])
            ->whereNull('nature_of_separation')
            ->where('is_vacant', false)
            ->distinct()
            ->orderBy('office_department')
            ->pluck('office_department')
            ->filter()
            ->values();

        $records = collect();
        $filtered = false;

        if ($request->anyFilled(['office', 'status', 'expiry_window', 'expiry_from', 'expiry_to', 'search'])) {
            $filtered = true;
            $query = PlantillaRecord::query()
                ->whereIn('employment_status', ['CASUAL', 'Casual', 'Cas', 'JO', 'Job Order', 'J.O.'])
                ->whereNull('nature_of_separation')
                ->where('is_vacant', false)
                ->orderBy('office_department')
                ->orderBy('last_name');

            if ($request->filled('search')) {
                $kw = '%' . trim($request->search) . '%';
                $query->where(function ($q) use ($kw) {
                    $q->where('last_name',         'LIKE', $kw)
                      ->orWhere('first_name',       'LIKE', $kw)
                      ->orWhere('middle_name',      'LIKE', $kw)
                      ->orWhere('name_extension',   'LIKE', $kw)
                      ->orWhere('position_title',   'LIKE', $kw)
                      ->orWhere('office_department','LIKE', $kw)
                      ->orWhere('detailed_unit',    'LIKE', $kw)
                      ->orWhere('tin',              'LIKE', $kw)
                      ->orWhere('item_no_new',      'LIKE', $kw);
                });
            }

            if ($request->filled('office')) {
                $query->where('office_department', $request->office);
            }

            if ($request->filled('status')) {
                $map = [
                    'Casual' => ['CASUAL', 'Casual', 'Cas'],
                    'JO'     => ['JO', 'Job Order', 'J.O.'],
                ];
                $query->whereIn('employment_status', $map[$request->status] ?? []);
            }

            // Expiry window — based on LATEST contract renewal end date
            $window = $request->expiry_window;
            if ($window === 'custom') {
                $from = $request->expiry_from;
                $to   = $request->expiry_to;
            } elseif ($window) {
                $from = now()->toDateString();
                $to   = match ($window) {
                    '7d'   => now()->addDays(7)->toDateString(),
                    '15d'  => now()->addDays(15)->toDateString(),
                    '30d'  => now()->addDays(30)->toDateString(),
                    '60d'  => now()->addDays(60)->toDateString(),
                    'this_month' => now()->endOfMonth()->toDateString(),
                    'next_month' => now()->addMonth()->endOfMonth()->toDateString(),
                    default => null,
                };
            }

            if (!empty($from) || !empty($to)) {
                // Join latest contract renewal per record
                $query->whereExists(function ($q) use ($from, $to) {
                    $q->select(DB::raw(1))
                        ->from('contract_renewals as cr')
                        ->whereColumn('cr.plantilla_record_id', 'plantilla_records.id')
                        ->whereIn('cr.id', function ($sub) {
                            $sub->select(DB::raw('MAX(id)'))
                                ->from('contract_renewals')
                                ->groupBy('plantilla_record_id');
                        })
                        ->when(!empty($from), fn($q) => $q->whereDate('cr.contract_end_date', '>=', $from))
                        ->when(!empty($to),   fn($q) => $q->whereDate('cr.contract_end_date', '<=', $to));
                });
            }

            $records = $query->with(['latestRenewal'])->get()->map(function ($r) {
                return (object) [
                    'id'             => $r->id,
                    'office'         => $r->office_department,
                    'detailed_unit'  => $r->detailed_unit,
                    'last_name'      => $r->last_name,
                    'first_name'     => $r->first_name,
                    'middle_name'    => $r->middle_name,
                    'name_extension' => $r->name_extension,
                    'position_title' => $r->position_title,
                    'employment_status' => $r->employment_status,
                    'sex'            => $r->sex,
                    'date_of_birth'  => $r->date_of_birth,
                    'item_no_new'    => $r->item_no_new,
                    'latest_start'   => $r->latestRenewal?->contract_start_date,
                    'latest_end'     => $r->latestRenewal?->contract_end_date,
                    'latest_rate'    => $r->latestRenewal?->rate,
                    'latest_rate_type' => $r->latestRenewal?->rate_type,
                    // Pre-check flags
                    'missing_position' => empty($r->position_title),
                    // Module 1.2 — unrenewed rows are blocked from batch selection.
                    'is_renewed'     => (bool) $r->is_renewed,
                    'renewal_period' => $r->renewal_period,
                ];
            });
        }

        // Summary stats for the dashboard cards
        $brStats = [
            'total_casual' => PlantillaRecord::whereIn('employment_status', ['CASUAL','Casual','Cas'])
                ->whereNull('nature_of_separation')->where('is_vacant', false)->whereNull('deleted_at')->count(),
            'total_jo'     => PlantillaRecord::whereIn('employment_status', ['JO','Job Order','J.O.'])
                ->whereNull('nature_of_separation')->where('is_vacant', false)->whereNull('deleted_at')->count(),
            'expiring_30'  => PlantillaRecord::whereIn('employment_status', ['CASUAL','Casual','Cas','JO','Job Order','J.O.'])
                ->whereNull('nature_of_separation')->where('is_vacant', false)->whereNull('deleted_at')
                ->whereHas('latestRenewal', fn($q) => $q
                    ->whereDate('contract_end_date', '>=', now()->toDateString())
                    ->whereDate('contract_end_date', '<=', now()->addDays(30)->toDateString())
                )->count(),
            'expired'      => PlantillaRecord::whereIn('employment_status', ['CASUAL','Casual','Cas','JO','Job Order','J.O.'])
                ->whereNull('nature_of_separation')->where('is_vacant', false)->whereNull('deleted_at')
                ->whereHas('latestRenewal', fn($q) => $q->whereDate('contract_end_date', '<', now()->toDateString()))
                ->count(),
        ];

        return view('batch-renewal.index', compact('offices', 'records', 'filtered', 'brStats'));
    }

    // ── Validate (AJAX): pre-renewal readiness check ─────────────────────────

    public function validate(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['error' => 'No records selected.'], 422);
        }

        $startDate = $request->input('contract_start_date');
        $endDate   = $request->input('contract_end_date');

        $records = PlantillaRecord::whereIn('id', $ids)->get();
        $results = [];

        foreach ($records as $r) {
            $errors = [];

            if ($r->is_vacant) {
                $errors[] = 'Record is marked vacant';
            }

            // Module 1.2 — server-side re-check; client-side disabling alone
            // is never trusted.
            if (!$r->is_renewed) {
                $errors[] = "Action Blocked: {$r->last_name}, {$r->first_name} is currently unrenewed or excluded for this rating period. Resolve status under individual Personnel Inventory before executing batch renewal.";
            }

            // Duplicate check: does a renewal already exist covering this period?
            if ($startDate && $endDate) {
                $duplicate = ContractRenewal::where('plantilla_record_id', $r->id)
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('contract_start_date', [$startDate, $endDate])
                          ->orWhereBetween('contract_end_date', [$startDate, $endDate])
                          ->orWhere(function ($q2) use ($startDate, $endDate) {
                              $q2->where('contract_start_date', '<=', $startDate)
                                 ->where('contract_end_date',   '>=', $endDate);
                          });
                    })->exists();

                if ($duplicate) {
                    $errors[] = 'Renewal already exists for this period';
                }
            }

            $results[] = [
                'id'     => $r->id,
                'name'   => strtoupper($r->last_name) . ', ' . $r->first_name,
                'valid'  => empty($errors),
                'errors' => $errors,
            ];
        }

        $validCount   = collect($results)->where('valid', true)->count();
        $invalidCount = collect($results)->where('valid', false)->count();

        return response()->json([
            'results'       => $results,
            'valid_count'   => $validCount,
            'invalid_count' => $invalidCount,
        ]);
    }

    // ── Process: atomic batch renewal ────────────────────────────────────────

    public function process(Request $request)
    {
        // Module 1A.7 — commit authority is RBAC-gated; everyone else must
        // use requestRenewal() instead.
        RenewalAuthority::assertCanCommit(Auth::user());

        $request->validate([
            'ids'                 => 'required|array|min:1',
            'ids.*'               => 'integer|exists:plantilla_records,id',
            'contract_start_date' => 'required|date',
            'contract_end_date'   => 'required|date|after:contract_start_date',
            'rate'                => 'nullable|numeric|min:0',
            'rate_type'           => 'nullable|in:Daily,Monthly,Annual',
            'notes'               => 'nullable|string|max:500',
            'updates'             => 'nullable|array',
        ]);

        $ids       = $request->ids;
        $startDate = $request->contract_start_date;
        $endDate   = $request->contract_end_date;
        $rate      = $request->rate;
        $rateType  = $request->rate_type;
        $notes     = $request->notes;
        $userId    = Auth::id();
        $updates   = $request->input('updates', []);  // keyed by record id

        $succeeded = [];
        $failed    = [];

        try {
            DB::transaction(function () use (
                $ids, $startDate, $endDate, $rate, $rateType, $notes, $userId, $updates,
                &$succeeded, &$failed
            ) {
                $records = PlantillaRecord::whereIn('id', $ids)->get()->keyBy('id');

                foreach ($ids as $id) {
                    $r = $records->get($id);

                    if (!$r) {
                        $failed[] = ['id' => $id, 'name' => "ID #{$id}", 'reason' => 'Record not found'];
                        continue;
                    }

                    // Apply inline field edits before renewal
                    $rowUpdate = $updates[(string)$id] ?? $updates[$id] ?? [];
                    $dirty = [];
                    if (!empty($rowUpdate['last_name']))             $dirty['last_name']         = strtoupper(trim($rowUpdate['last_name']));
                    if (array_key_exists('middle_name', $rowUpdate)) $dirty['middle_name']   = trim($rowUpdate['middle_name']);
                    if (!empty($rowUpdate['position_title']))        $dirty['position_title'] = strtoupper(trim($rowUpdate['position_title']));
                    if (!empty($rowUpdate['office_department']))     $dirty['office_department'] = trim($rowUpdate['office_department']);
                    if (array_key_exists('detailed_unit', $rowUpdate)) $dirty['detailed_unit'] = trim($rowUpdate['detailed_unit']);
                    if (!empty($dirty)) {
                        $r->fill($dirty)->save();
                    }

                    // Module 1.2 — never trust client-side disabling alone.
                    if (!$r->is_renewed) {
                        $failed[] = [
                            'id' => $id,
                            'name' => strtoupper($r->last_name) . ', ' . $r->first_name,
                            'reason' => "Action Blocked: {$r->last_name}, {$r->first_name} is currently unrenewed or excluded for this rating period. Resolve status under individual Personnel Inventory before executing batch renewal.",
                        ];
                        continue;
                    }

                    // Module 1A.6 — the ONE shared commit path. Guardrails
                    // (vacancy, duplicate period, is_renewed set, signal,
                    // audit log) all live in the service, not here.
                    try {
                        $this->commitService->commitOne(
                            $r,
                            $startDate,
                            $endDate,
                            $rate ?: null,
                            $rateType ?: null,
                            Auth::user(),
                            self::currentRatingPeriod(),
                            $notes ?: null,
                        );
                    } catch (\RuntimeException $e) {
                        $failed[] = [
                            'id' => $id,
                            'name' => strtoupper($r->last_name) . ', ' . $r->first_name,
                            'reason' => $e->getMessage(),
                        ];
                        continue;
                    }

                    $succeeded[] = $id;
                }

                // Roll back the entire transaction if ALL records failed
                if (count($succeeded) === 0 && count($failed) > 0) {
                    throw new \RuntimeException('All records failed validation — transaction rolled back.');
                }
            });
        } catch (\RuntimeException $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => $e->getMessage(),
                'succeeded' => 0,
                'failed'    => $failed,
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unexpected error: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status'    => 'success',
            'succeeded' => count($succeeded),
            'failed'    => $failed,
        ]);
    }

    /**
     * Module 1A.8 — for sessions without renewal_commit authority: raise a
     * request that notifies PHRMO/Appointments with the accumulated signals
     * as supporting evidence, instead of granting commit access.
     */
    public function requestRenewal(Request $request, PlantillaRecord $plantillaRecord)
    {
        $data = $request->validate(['notes' => 'nullable|string|max:500']);
        $user = Auth::user();
        $period = self::currentRatingPeriod();

        $this->signalService->record(
            $plantillaRecord,
            $period,
            'OTHER',
            'RENEWAL_REQUESTED',
            \App\Models\RenewalSignal::STRENGTH_WEAK,
            $user,
            $data['notes'] ?? null,
        );

        $signalCount = $this->signalService->forRecord($plantillaRecord, $period)->count();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Requested Renewal',
            'description' => "Requested renewal review for {$plantillaRecord->last_name}, {$plantillaRecord->first_name} ({$period}) — {$signalCount} signal(s) on file. Awaiting PHRMO/Appointments commit.",
        ]);

        return back()->with('success', 'Renewal request submitted to PHRMO/Appointments with supporting signal history.');
    }
}
