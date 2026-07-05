<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractStatusController extends Controller
{
    private const CASUAL_STATUSES    = ['CASUAL', 'Casual', 'Cas'];
    private const JO_STATUSES        = ['JO', 'Job Order', 'J.O.'];
    private const PERMANENT_STATUSES = ['P', 'Permanent', 'CT', 'Co-Terminous', 'Coterminous', 'E', 'Elected'];

    // ── Scope helpers ─────────────────────────────────────────────────────────

    private function applyContractActive($query, string $today)
    {
        return $query
            ->where(fn($q) => $q->whereNull('nature_of_separation')->orWhere('nature_of_separation', ''))
            ->whereExists(fn($q) => $q->select(DB::raw(1))
                ->from('contract_renewals as cr')
                ->whereColumn('cr.plantilla_record_id', 'plantilla_records.id')
                ->whereIn('cr.id', fn($s) => $s->select(DB::raw('MAX(id)'))->from('contract_renewals')->groupBy('plantilla_record_id'))
                ->whereDate('cr.contract_end_date', '>=', $today));
    }

    private function applyContractLapsed($query, string $today)
    {
        return $query
            ->where(fn($q) => $q->whereNull('nature_of_separation')->orWhere('nature_of_separation', ''))
            ->where(fn($q) => $q
                ->whereNotExists(fn($q2) => $q2->select(DB::raw(1))
                    ->from('contract_renewals as cr')
                    ->whereColumn('cr.plantilla_record_id', 'plantilla_records.id'))
                ->orWhereExists(fn($q2) => $q2->select(DB::raw(1))
                    ->from('contract_renewals as cr')
                    ->whereColumn('cr.plantilla_record_id', 'plantilla_records.id')
                    ->whereIn('cr.id', fn($s) => $s->select(DB::raw('MAX(id)'))->from('contract_renewals')->groupBy('plantilla_record_id'))
                    ->whereDate('cr.contract_end_date', '<', $today)));
    }

    private function applySeparated($query)
    {
        return $query->whereNotNull('nature_of_separation')->where('nature_of_separation', '!=', '');
    }

    private function applyPermanentActive($query)
    {
        return $query->where(fn($q) => $q->whereNull('nature_of_separation')->orWhere('nature_of_separation', ''));
    }

    // Normalise separation reason into one of: Resigned | Transferred | Retired | Terminated | Deceased | Other
    public static function classifySeparation(?string $reason): string
    {
        if (!$reason) return 'Other';
        $r = strtolower($reason);
        if (str_contains($r, 'resign'))   return 'Resigned';
        if (str_contains($r, 'transfer')) return 'Transferred';
        if (str_contains($r, 'retir'))    return 'Retired';
        if (str_contains($r, 'terminat') || str_contains($r, 'dismiss') || str_contains($r, 'dropped')) return 'Terminated';
        if (str_contains($r, 'death') || str_contains($r, 'deceas') || str_contains($r, 'died')) return 'Deceased';
        return 'Other';
    }

    // ── Main dashboard ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $officeFilter = $request->input('office');
        $today        = now()->toDateString();

        $base = fn($statuses) => PlantillaRecord::whereIn('employment_status', $statuses)
            ->when($officeFilter, fn($q) => $q->where('office_department', $officeFilter));

        // Casual / JO summary
        $summary = [
            'casual' => [
                'active'     => $this->applyContractActive(($base)(self::CASUAL_STATUSES), $today)->count(),
                'lapsed'     => $this->applyContractLapsed(($base)(self::CASUAL_STATUSES), $today)->count(),
                'separated'  => $this->applySeparated(($base)(self::CASUAL_STATUSES))->count(),
            ],
            'jo' => [
                'active'     => $this->applyContractActive(($base)(self::JO_STATUSES), $today)->count(),
                'lapsed'     => $this->applyContractLapsed(($base)(self::JO_STATUSES), $today)->count(),
                'separated'  => $this->applySeparated(($base)(self::JO_STATUSES))->count(),
            ],
            'permanent' => [
                'active'     => $this->applyPermanentActive(($base)(self::PERMANENT_STATUSES))->count(),
                'separated'  => $this->applySeparated(($base)(self::PERMANENT_STATUSES))->count(),
            ],
        ];

        // Separation breakdown for permanent (Resigned / Transferred / Retired / Terminated / Deceased / Other)
        $permSepBreakdown = $this->applySeparated(($base)(self::PERMANENT_STATUSES))
            ->pluck('nature_of_separation')
            ->groupBy(fn($r) => self::classifySeparation($r))
            ->map->count();

        // All offices across all types
        $allStatuses = array_merge(self::CASUAL_STATUSES, self::JO_STATUSES, self::PERMANENT_STATUSES);

        $allOffices = PlantillaRecord::whereIn('employment_status', $allStatuses)
            ->whereNotNull('office_department')->where('office_department', '!=', '')
            ->distinct()->orderBy('office_department')->pluck('office_department');

        $offices = PlantillaRecord::whereIn('employment_status', $allStatuses)
            ->when($officeFilter, fn($q) => $q->where('office_department', $officeFilter))
            ->whereNotNull('office_department')->where('office_department', '!=', '')
            ->distinct()->orderBy('office_department')->pluck('office_department');

        $breakdown = $offices->map(function ($office) use ($today) {
            $casual = fn() => PlantillaRecord::whereIn('employment_status', self::CASUAL_STATUSES)->where('office_department', $office);
            $jo     = fn() => PlantillaRecord::whereIn('employment_status', self::JO_STATUSES)->where('office_department', $office);
            $perm   = fn() => PlantillaRecord::whereIn('employment_status', self::PERMANENT_STATUSES)->where('office_department', $office);

            return [
                'office'            => $office,
                'casual_active'     => $this->applyContractActive($casual(), $today)->count(),
                'casual_lapsed'     => $this->applyContractLapsed($casual(), $today)->count(),
                'casual_separated'  => $this->applySeparated($casual())->count(),
                'jo_active'         => $this->applyContractActive($jo(), $today)->count(),
                'jo_lapsed'         => $this->applyContractLapsed($jo(), $today)->count(),
                'jo_separated'      => $this->applySeparated($jo())->count(),
                'perm_active'       => $this->applyPermanentActive($perm())->count(),
                'perm_separated'    => $this->applySeparated($perm())->count(),
            ];
        })->filter(fn($r) =>
            $r['casual_active'] + $r['casual_lapsed'] + $r['casual_separated'] +
            $r['jo_active']     + $r['jo_lapsed']     + $r['jo_separated'] +
            $r['perm_active']   + $r['perm_separated'] > 0
        )->values();

        return view('contract-status.index', compact(
            'summary', 'permSepBreakdown', 'breakdown', 'allOffices'
        ));
    }

    // ── Drilldown (AJAX) ──────────────────────────────────────────────────────

    public function drilldown(Request $request)
    {
        $office = $request->input('office');
        $type   = $request->input('type');   // casual | jo | permanent
        $state  = $request->input('state');  // active | lapsed | separated
        $today  = now()->toDateString();

        $statuses = match($type) {
            'casual'    => self::CASUAL_STATUSES,
            'jo'        => self::JO_STATUSES,
            'permanent' => self::PERMANENT_STATUSES,
            default     => self::PERMANENT_STATUSES,
        };

        $query = PlantillaRecord::whereIn('employment_status', $statuses)
            ->where('office_department', $office)
            ->orderBy('last_name');

        match($state) {
            'active' => $type === 'permanent'
                ? $this->applyPermanentActive($query)
                : $this->applyContractActive($query, $today),
            'lapsed'    => $this->applyContractLapsed($query, $today),
            'separated' => $this->applySeparated($query),
            default     => null,
        };

        $records = $query->with('latestRenewal')->get()->map(fn($r) => [
            'id'                   => $r->id,
            'last_name'            => $r->last_name,
            'first_name'           => $r->first_name,
            'middle_name'          => $r->middle_name,
            'name_extension'       => $r->name_extension,
            'position_title'       => $r->position_title,
            'employment_status'    => $r->employment_status,
            'nature_of_separation' => $r->nature_of_separation,
            'sep_class'            => self::classifySeparation($r->nature_of_separation),
            'contract_start'       => $r->latestRenewal?->contract_start_date?->format('M d, Y'),
            'contract_end'         => $r->latestRenewal?->contract_end_date?->format('M d, Y'),
            'days_left'            => $r->latestRenewal?->contract_end_date
                                          ? now()->diffInDays($r->latestRenewal->contract_end_date, false)
                                          : null,
        ]);

        return response()->json([
            'office'  => $office,
            'type'    => $type,
            'state'   => $state,
            'count'   => $records->count(),
            'records' => $records,
        ]);
    }
}
