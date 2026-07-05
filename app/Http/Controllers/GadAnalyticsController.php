<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GadAnalyticsController extends Controller
{
    // ── Global helper: base query (non-vacant, non-deleted) ────────────────
    private function baseQuery(Request $request)
    {
        $q = PlantillaRecord::query()
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('is_vacant', false)->orWhereNull('is_vacant');
            });

        if ($request->filled('status')) {
            $q->where('employment_status', $request->status);
        }
        if ($request->filled('office')) {
            $q->where('office_department', $request->office);
        }

        return $q;
    }

    // ── Index: render all modules ──────────────────────────────────────────
    public function index(Request $request)
    {
        $base = $this->baseQuery($request);

        // ── Summary counts ──────────────────────────────────────────────
        // sex column stores 'M' / 'F' (not 'Male'/'Female')
        $total  = (clone $base)->count();
        $male   = (clone $base)->where('sex', 'M')->count();
        $female = (clone $base)->where('sex', 'F')->count();
        $other  = $total - $male - $female;

        $gapPct     = $total > 0 ? round(abs($male - $female) / $total * 100, 1) : 0;
        $femalePct  = $total > 0 ? round($female / $total * 100, 1) : 0;
        $gadCompliant = $femalePct >= 40 && $femalePct <= 60;

        // ── Module 2: By Employment Status ──────────────────────────────
        $byStatus = (clone $base)
            ->selectRaw('employment_status, sex, COUNT(*) as cnt')
            ->groupBy('employment_status', 'sex')
            ->get();

        $statusLabels = $byStatus->pluck('employment_status')->unique()->sort()->values();
        $statusMale   = $statusLabels->map(fn($s) => $byStatus->where('employment_status', $s)->where('sex', 'M')->sum('cnt'));
        $statusFemale = $statusLabels->map(fn($s) => $byStatus->where('employment_status', $s)->where('sex', 'F')->sum('cnt'));

        // ── Module 3: By Office ──────────────────────────────────────────
        $byOffice = (clone $base)
            ->selectRaw('office_department, sex, COUNT(*) as cnt')
            ->whereNotNull('office_department')
            ->groupBy('office_department', 'sex')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(20)
            ->get();

        $officeLabels  = $byOffice->pluck('office_department')->unique()->values();
        $officeMale    = $officeLabels->map(fn($o) => $byOffice->where('office_department', $o)->where('sex', 'M')->sum('cnt'));
        $officeFemale  = $officeLabels->map(fn($o) => $byOffice->where('office_department', $o)->where('sex', 'F')->sum('cnt'));

        // Office drill-down table
        $officeTable = $officeLabels->map(function ($o) use ($byOffice, $total) {
            $m = $byOffice->where('office_department', $o)->where('sex', 'M')->sum('cnt');
            $f = $byOffice->where('office_department', $o)->where('sex', 'F')->sum('cnt');
            $t = $m + $f;
            return [
                'office'    => $o,
                'male'      => $m,
                'female'    => $f,
                'total'     => $t,
                'female_pct'=> $t > 0 ? round($f / $t * 100, 1) : 0,
                'gad_flag'  => $t > 0 && ($f / $t < 0.40 || $f / $t > 0.60),
            ];
        })->sortByDesc('total')->values();

        // ── Module 4: Age Groups ─────────────────────────────────────────
        $ageGroups = ['<25', '25–34', '35–44', '45–54', '55–64', '65+'];
        $now = now();
        $ageBands = [
            '<25'   => [0,   24],
            '25–34' => [25,  34],
            '35–44' => [35,  44],
            '45–54' => [45,  54],
            '55–64' => [55,  64],
            '65+'   => [65, 200],
        ];

        $ageMale   = collect();
        $ageFemale = collect();
        foreach ($ageBands as $label => [$min, $max]) {
            $minDob = $now->copy()->subYears($max + 1)->addDay();
            $maxDob = $now->copy()->subYears($min);
            $sub = (clone $base)->whereBetween('date_of_birth', [$minDob->toDateString(), $maxDob->toDateString()]);
            $ageMale->push($sub->where('sex', 'M')->count());
            $ageFemale->push($sub->where('sex', 'F')->count());
        }

        // ── Module 5: Salary Grade Bands ────────────────────────────────
        $sgBands = ['SG 1–6', 'SG 7–12', 'SG 13–18', 'SG 19–24', 'SG 25–30', 'SG 31–33'];
        $sgRanges = [[1,6],[7,12],[13,18],[19,24],[25,30],[31,33]];
        $sgMale   = collect();
        $sgFemale = collect();
        $sgAlert  = false;

        foreach ($sgRanges as $i => [$lo, $hi]) {
            $sub = (clone $base)->whereBetween(DB::raw('CAST(salary_grade AS UNSIGNED)'), [$lo, $hi]);
            $m = $sub->where('sex', 'M')->count();
            $f = $sub->where('sex', 'F')->count();
            $sgMale->push($m);
            $sgFemale->push($f);
            // Vertical segregation: SG 19+ male-dominated (>70% male)
            if ($i >= 3 && ($m + $f) > 0 && $m / ($m + $f) > 0.70) {
                $sgAlert = true;
            }
        }

        // ── Module 6: PWD & Solo Parent ─────────────────────────────────
        $pwdTotal  = (clone $base)->where('is_pwd', true)->count();
        $pwdMale   = (clone $base)->where('is_pwd', true)->where('sex', 'M')->count();
        $pwdFemale = (clone $base)->where('is_pwd', true)->where('sex', 'F')->count();
        $pwdPct    = $total > 0 ? round($pwdTotal / $total * 100, 2) : 0;
        $pwdQuotaBreach = $pwdPct < 1.0;

        $spTotal  = (clone $base)->where('solo_parent', true)->count();
        $spMale   = (clone $base)->where('solo_parent', true)->where('sex', 'M')->count();
        $spFemale = (clone $base)->where('solo_parent', true)->where('sex', 'F')->count();
        $spPct    = $total > 0 ? round($spTotal / $total * 100, 2) : 0;

        // ── Module 7: Recruitment (last 12 months) ───────────────────────
        $recruitMonths = collect(range(11, 0))->map(fn($i) => now()->subMonths($i)->format('Y-m'));
        $recruitData   = Applicant::selectRaw("DATE_FORMAT(created_at,'%Y-%m') as ym, sex, COUNT(*) as cnt")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('ym', 'sex')
            ->get();

        // applicants.sex stores 'Male'/'Female' (different from plantilla_records 'M'/'F')
        $recruitMale   = $recruitMonths->map(fn($m) => $recruitData->where('ym', $m)->where('sex', 'Male')->sum('cnt'));
        $recruitFemale = $recruitMonths->map(fn($m) => $recruitData->where('ym', $m)->where('sex', 'Female')->sum('cnt'));
        $recruitLabels = $recruitMonths->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->format('M Y'));

        // Funnel: applied → qualified → recommended (using preeval final_rating)
        $applied     = Applicant::count();
        $qualified   = DB::table('applicant_evaluations')->where('final_rating', 'Qualified')->count();
        $recommended = DB::table('applicant_evaluations')->where('final_rating', 'Qualified')
            ->where('qs_requirement', 'Met')->where('docs_complete', 'Yes')->count();

        // ── Module 8: SPMS ───────────────────────────────────────────────
        $spmsRated = (clone $base)->whereNotNull('spms_rating');
        $spmsTotal = (clone $spmsRated)->count();

        $spmsBands = [
            'Outstanding (≥4.90)'       => [4.90, 5.00],
            'Very Satisfactory (3.80–4.89)' => [3.80, 4.89],
            'Satisfactory (2.50–3.79)'  => [2.50, 3.79],
            'Unsatisfactory (<2.50)'    => [0.00, 2.49],
        ];
        $spmsCounts = collect();
        foreach ($spmsBands as $label => [$lo, $hi]) {
            $spmsCounts->push((clone $spmsRated)->whereBetween('spms_rating', [$lo, $hi])->count());
        }

        $outstanding = (clone $spmsRated)
            ->where('spms_rating', '>=', 4.90)
            ->orderByDesc('spms_rating')
            ->select(['last_name','first_name','middle_name','sex','office_department','position_title','salary_grade','spms_rating'])
            ->get();

        // ── Distinct offices/statuses for filter dropdowns ───────────────
        $officeOptions = PlantillaRecord::whereNotNull('office_department')
            ->whereNull('deleted_at')
            ->distinct()->orderBy('office_department')
            ->pluck('office_department');
        $statusOptions = PlantillaRecord::whereNotNull('employment_status')
            ->whereNull('deleted_at')
            ->distinct()->orderBy('employment_status')
            ->pluck('employment_status');

        return view('gad.index', compact(
            'total', 'male', 'female', 'other',
            'gapPct', 'femalePct', 'gadCompliant',
            'statusLabels', 'statusMale', 'statusFemale',
            'officeLabels', 'officeMale', 'officeFemale', 'officeTable',
            'ageGroups', 'ageMale', 'ageFemale',
            'sgBands', 'sgMale', 'sgFemale', 'sgAlert',
            'pwdTotal', 'pwdMale', 'pwdFemale', 'pwdPct', 'pwdQuotaBreach',
            'spTotal', 'spMale', 'spFemale', 'spPct',
            'recruitLabels', 'recruitMale', 'recruitFemale',
            'applied', 'qualified', 'recommended',
            'spmsBands', 'spmsCounts', 'spmsTotal', 'outstanding',
            'officeOptions', 'statusOptions',
        ));
    }

    // ── Export outstanding employees as CSV ──────────────────────────────
    public function exportOutstandingCsv(Request $request)
    {
        $records = $this->baseQuery($request)
            ->whereNotNull('spms_rating')
            ->where('spms_rating', '>=', 4.90)
            ->orderByDesc('spms_rating')
            ->get(['last_name','first_name','middle_name','sex','office_department','position_title','salary_grade','spms_rating']);

        $filename = 'gad_outstanding_spms_' . date('Ymd') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($records) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['Last Name','First Name','Middle Name','Sex','Office/Department','Position','SG','SPMS Rating']);
            foreach ($records as $r) {
                fputcsv($fh, [$r->last_name, $r->first_name, $r->middle_name, $r->sex,
                    $r->office_department, $r->position_title, $r->salary_grade, $r->spms_rating]);
            }
            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }
}
