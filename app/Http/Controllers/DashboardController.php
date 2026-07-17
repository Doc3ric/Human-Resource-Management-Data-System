<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\CasualEmployee;
use App\Models\JobOrder;
use App\Support\Metrics\MetricRegistry;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with live stats from plantilla_records.
     */
    public function index(): View
    {
        $totalPositions = 0;
        $filledPositions = 0;
        $permanentFilledPositions = 0;
        $vacantPositions = 0;
        $abolishedPositions = 0;
        $totalSalaryBudget = 0;
        $pwdCount = 0;
        $ipCount = 0;
        $soloParentCount = 0;
        $stepDueCount = 0;
        $retirementDueCount = 0;
        $monthlyBirthdays = collect();
        $vacantFundedPositions = 0;
        $vacantUnfundedPositions = 0;
        $employeesPerUnit = [];
        $statusBreakdown = [];
        $casualTotal = 0;
        $casualVacant = 0;
        $jobOrderTotal = 0;
        // Plantilla status breakdown counts
        $plantillaPermCount   = 0;
        $plantillaElectCount  = 0;
        $plantillaCoterCount  = 0;
        $plantillaTempCount   = 0;
        $plantillaPartCount   = 0;
        $casualInPlantilla    = 0;
        
        // New metrics for arrangement
        $totalEmployeesUnique = 0;
        $regularTotalCount = 0;
        $regularMale = 0;
        $regularFemale = 0;
        $casualOnlyTotal = 0;
        $casualMale = 0;
        $casualFemale = 0;
        $joTotalActive = 0;
        $joMale = 0;
        $joFemale = 0;
        $vacantPositionsTotal = 0;
        $notRenewedActiveCount = 0;
        
        $ageBrackets = [
            '20-29' => 0,
            '30-39' => 0,
            '40-49' => 0,
            '50-59' => 0,
            '60+' => 0,
        ];

        try {
            $totalPositions = PlantillaRecord::count();
            $filledPositions = PlantillaRecord::filled()->count();
            $vacantPositions = PlantillaRecord::vacant()->count();
            $vacantFundedPositions = PlantillaRecord::vacant()->where('authorized_annual_salary', '>', 0)->count();
            $vacantUnfundedPositions = PlantillaRecord::vacant()->where(function ($q) {
                $q->whereNull('authorized_annual_salary')->orWhere('authorized_annual_salary', '<=', 0);
            })->count();
            $abolishedPositions = PlantillaRecord::abolished()->count();

            // Permanent Filled Positions (only P/Permanent — excludes Casual, JO, etc.)
            $permanentFilledPositions = PlantillaRecord::filled()
                ->whereIn('employment_status', ['P', 'Permanent'])
                ->count();
            $permanentEmployeesCount = $permanentFilledPositions;

            // Plantilla Positions pie chart breakdown (Regular types + Casual)
            $plantillaPermCount  = PlantillaRecord::filled()->whereIn('employment_status', ['P', 'Permanent'])->count();
            $plantillaElectCount = PlantillaRecord::filled()->whereIn('employment_status', ['E', 'Elected'])->count();
            $plantillaCoterCount = PlantillaRecord::filled()->whereIn('employment_status', ['CT', 'Co-Terminous', 'Coterminous'])->count();
            $plantillaTempCount  = PlantillaRecord::filled()->whereIn('employment_status', ['Temporary', 'T', 'Temp'])->count();
            $plantillaPartCount  = PlantillaRecord::filled()->whereIn('employment_status', ['Part-Time', 'PT', 'Part Time'])->count();
            // Casual positions inside plantilla_records
            $casualInPlantilla   = PlantillaRecord::filled()->whereIn('employment_status', ['Casual', 'Cas', 'C'])->count();

            $totalSalaryBudget = PlantillaRecord::filled()->sum('base_salary_amount');

            // Module 12.3 non-redundancy: these two are the exact same query as
            // MetricRegistry's canonical "GAD & Workforce / Analytics" entries
            // (workforce_pwd_count / workforce_solo_parent_count) — read from
            // the registry instead of recomputing, so the dashboard and any
            // other surface can never disagree about these figures.
            $pwdCount = MetricRegistry::get('workforce_pwd_count', Auth::user())['value'];
            $ipCount = PlantillaRecord::filled()->whereNotNull('indigenous_people')->where('indigenous_people', '!=', '')->count();
            $soloParentCount = MetricRegistry::get('workforce_solo_parent_count', Auth::user())['value'];

            // Employees per organizational unit (top 15 for chart)
            $employeesPerUnit = PlantillaRecord::filled()
                ->selectRaw('office_department, COUNT(*) as count')
                ->groupBy('office_department')
                ->orderByDesc('count')
                ->limit(15)
                ->pluck('count', 'office_department')
                ->toArray();

            // Status breakdown for pie chart
            $statusBreakdown = PlantillaRecord::filled()
                ->selectRaw('employment_status, COUNT(*) as count')
                ->groupBy('employment_status')
                ->pluck('count', 'employment_status')
                ->toArray();

            // Step increment stats — fast SQL-only COUNT (no PHP loops)
            $threeYearsAgo = now()->subYears(3)->toDateString();
            $fiveYearsAgo = now()->subYears(5)->toDateString();
            $stepDueCount = PlantillaRecord::filled()
                ->where('is_apprehended', false)
                ->where('is_admin_charge', false)
                ->where('employment_status', 'P')
                ->where('step', '<', 8)
                ->whereNotNull('date_original_appointment')
                ->where(function ($q) use ($threeYearsAgo, $fiveYearsAgo) {
                    $q->where(function ($q2) use ($threeYearsAgo) {
                        $q2->whereNotNull('date_last_promotion')
                            ->where('date_last_promotion', '<=', $threeYearsAgo);
                    })->orWhere(function ($q2) use ($threeYearsAgo) {
                        $q2->whereNull('date_last_promotion')
                            ->where('date_original_appointment', '<=', $threeYearsAgo);
                    })->orWhere(function ($q3) use ($fiveYearsAgo) {
                        $q3->where(function ($qOu) {
                            $qOu->where('office_department', 'like', '%BPH%')
                                ->orWhere('office_department', 'like', '%HEALTH%')
                                ->orWhere('office_department', 'like', '%MEDICAL%');
                        })->where(function ($q4) use ($fiveYearsAgo) {
                            $q4->whereNotNull('date_last_nolp')
                                ->where('date_last_nolp', '<=', $fiveYearsAgo);
                        })->orWhere(function ($q4) use ($fiveYearsAgo) {
                            $q4->whereNull('date_last_nolp')
                                ->where('date_original_appointment', '<=', $fiveYearsAgo);
                        });
                    });
                })
                ->count();

            // Retirement stats
            $retirementDueCount = PlantillaRecord::retirementDue()->count();

            // Casual Employees (active = filled + not separated)
            $casualTotal = CasualEmployee::where('is_vacant', false)->whereNull('nature_of_separation')->count();
            $casualVacant = CasualEmployee::where('is_vacant', true)->count();

            // Job Orders (active = not separated)
            $jobOrderTotal = JobOrder::whereNull('nature_of_separation')->count();

            // --- New specific arrangement calculations ---
            // CasualEmployee and JobOrder are PlantillaRecord subclasses that only add a
            // global scope for employment_status ('CASUAL' / 'JO') — they read the same
            // plantilla_records rows, not separate tables. The previous version of this
            // block queried both the subclass AND a parallel PlantillaRecord::whereIn(...)
            // condition and *added* the two counts together, double-counting every single
            // casual/JO employee (verified: 246 casual records counted as 492, inflating
            // the Male/Female GAD totals derived from them too). Counting each employment
            // status exactly once via PlantillaRecord::filled() removes that redundancy,
            // and — as a side effect — correctly excludes any not-yet-renewed Casual/JO
            // record from these totals (filled() only admits a renewal-gated status once
            // is_renewed is true), consistent with the "Action Required" renewal notice
            // above rather than silently double-counting them anyway.

            // 1. TOTAL EMPLOYEES: Active Only, No Duplication
            $totalEmployeesUnique = PlantillaRecord::filled()->count();

            // 2. REGULAR (Elected, Coterminus, Permanent, Part-Time, Temp)
            $regularTotalCount = PlantillaRecord::filled()->whereNotIn('employment_status', ['CASUAL', 'JO'])->count();

            // 3. MALE vs FEMALE (REGULAR)
            $regularMale = PlantillaRecord::filled()
                ->whereNotIn('employment_status', ['CASUAL', 'JO'])
                ->where('sex', 'like', 'M%')->count();
            $regularFemale = PlantillaRecord::filled()
                ->whereNotIn('employment_status', ['CASUAL', 'JO'])
                ->where('sex', 'like', 'F%')->count();

            // 4. CASUAL : Total (active only)
            $casualOnlyTotal = PlantillaRecord::filled()->where('employment_status', 'CASUAL')->count();

            // 5. MALE vs FEMALE (CASUAL, active only)
            $casualMale = PlantillaRecord::filled()->where('employment_status', 'CASUAL')->where('sex', 'like', 'M%')->count();
            $casualFemale = PlantillaRecord::filled()->where('employment_status', 'CASUAL')->where('sex', 'like', 'F%')->count();

            // 6. JOB ORDER (active only)
            $joTotalActive = PlantillaRecord::filled()->where('employment_status', 'JO')->count();

            // 7. VACANT POSITIONS (Using only plantilla vacants since they hold the actual items usually)
            $vacantPositionsTotal = PlantillaRecord::vacant()->count();

            // 8. MALE vs. FEMALE (JO, active only)
            $joMale = PlantillaRecord::filled()->where('employment_status', 'JO')->where('sex', 'like', 'M%')->count();
            $joFemale = PlantillaRecord::filled()->where('employment_status', 'JO')->where('sex', 'like', 'F%')->count();
            // ---------------------------------------------

            // 9. Casual/JO personnel not yet renewed for the current period are now
            // correctly EXCLUDED from the totals above — PlantillaRecord::filled()
            // only admits a renewal-gated status once is_renewed is true. This count
            // is purely informational: it tells the admin how many are missing from
            // the totals pending renewal, surfaced via the "Action Required" banner
            // with a link to Batch Renewal, rather than the number just quietly
            // disappearing from the dashboard with no explanation.
            $notRenewedActiveCount = PlantillaRecord::whereIn('employment_status', PlantillaRecord::RENEWAL_GATED_STATUSES)
                ->where('is_renewed', false)
                ->where('is_vacant', false)
                ->where('abolished', false)
                ->whereNull('deleted_at')
                ->whereNull('nature_of_separation')
                ->count();

            // Monthly Birthdays
            $monthlyBirthdays = PlantillaRecord::filled()
                ->whereNotNull('date_of_birth')
                ->whereMonth('date_of_birth', now()->month)
                ->orderByRaw('DAY(date_of_birth)')
                ->get();

            // Age Demographics
            $plantillaAges = DB::table('plantilla_records')
                ->whereNotIn('employment_status', ['JO', 'CASUAL'])
                ->where('is_vacant', false)->where('abolished', false)->whereNotNull('date_of_birth')->whereNull('deleted_at')->whereNull('nature_of_separation')
                ->selectRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age')
                ->pluck('age');

            $casualAges = CasualEmployee::where('is_vacant', false)->whereNotNull('date_of_birth')
                ->selectRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age')
                ->pluck('age');

            $joAges = JobOrder::whereNull('nature_of_separation')->whereNotNull('date_of_birth')
                ->selectRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age')
                ->pluck('age');

            $allAges = $plantillaAges->concat($casualAges)->concat($joAges);

            foreach ($allAges as $age) {
                if ($age >= 20 && $age <= 29) {
                    $ageBrackets['20-29']++;
                } elseif ($age >= 30 && $age <= 39) {
                    $ageBrackets['30-39']++;
                } elseif ($age >= 40 && $age <= 49) {
                    $ageBrackets['40-49']++;
                } elseif ($age >= 50 && $age <= 59) {
                    $ageBrackets['50-59']++;
                } elseif ($age >= 60) {
                    $ageBrackets['60+']++;
                }
            }

        } catch (\Exception $e) {
            // Log the error if necessary
            // \Log::error("Database error in DashboardController: " . $e->getMessage());
            // All counts and arrays will remain at their default empty/zero values
        }

        return view('dashboard', [
            'totalPositions'           => $totalPositions,
            'filledPositions'          => $filledPositions,
            'permanentFilledPositions' => $permanentFilledPositions,
            'vacantPositions'          => $vacantPositions,
            'vacantFundedPositions'    => $vacantFundedPositions,
            'vacantUnfundedPositions'  => $vacantUnfundedPositions,
            'abolishedPositions'       => $abolishedPositions,
            'permanentEmployeesCount'  => $permanentEmployeesCount ?? 0,
            'totalSalaryBudget'        => $totalSalaryBudget,
            'pwdCount'                 => $pwdCount,
            'ipCount'                  => $ipCount,
            'soloParentCount'          => $soloParentCount,
            'stepDueCount'             => $stepDueCount,
            'retirementDueCount'       => $retirementDueCount,
            'monthlyBirthdays'         => $monthlyBirthdays,
            'employeesPerUnit'         => $employeesPerUnit,
            'statusBreakdown'          => $statusBreakdown,
            'casualTotal'              => $casualTotal,
            'casualVacant'             => $casualVacant,
            'casualFilled'             => $casualTotal - $casualVacant,
            'jobOrderTotal'            => $jobOrderTotal,
            'ageBrackets'              => $ageBrackets,
            // Plantilla pie chart data
            'plantillaPermCount'       => $plantillaPermCount,
            'plantillaElectCount'      => $plantillaElectCount,
            'plantillaCoterCount'      => $plantillaCoterCount,
            'plantillaTempCount'       => $plantillaTempCount,
            'plantillaPartCount'       => $plantillaPartCount,
            'casualInPlantilla'        => $casualInPlantilla,
            // New Dashboard Data
            'totalEmployeesUnique'     => $totalEmployeesUnique,
            'regularTotalCount'        => $regularTotalCount,
            'regularMale'              => $regularMale,
            'regularFemale'            => $regularFemale,
            'casualOnlyTotal'          => $casualOnlyTotal,
            'casualMale'               => $casualMale,
            'casualFemale'             => $casualFemale,
            'joTotalActive'            => $joTotalActive,
            'joMale'                   => $joMale,
            'joFemale'                 => $joFemale,
            'vacantPositionsTotal'     => $vacantPositionsTotal,
            'notRenewedActiveCount'    => $notRenewedActiveCount,
        ]);
    }
}
