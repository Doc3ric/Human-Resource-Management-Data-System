<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\CasualEmployee;
use App\Models\JobOrder;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with live stats from plantilla_records.
     */
    public function index(): View
    {
        $totalPositions          = 0;
        $filledPositions         = 0;
        $vacantPositions         = 0;
        $abolishedPositions      = 0;
        $totalSalaryBudget       = 0;
        $pwdCount                = 0;
        $ipCount                 = 0;
        $soloParentCount         = 0;
        $stepDueCount            = 0;
        $retirementDueCount      = 0;
        $monthlyBirthdays        = [];
        $vacantFundedPositions   = 0;
        $vacantUnfundedPositions = 0;
        $employeesPerUnit        = [];
        $statusBreakdown         = [];
        $casualTotal             = 0;
        $casualVacant            = 0;
        $jobOrderTotal           = 0;
        $ageBrackets             = [
            '20-29' => 0,
            '30-39' => 0,
            '40-49' => 0,
            '50-59' => 0,
            '60+'   => 0,
        ];

        try {
            $totalPositions     = PlantillaRecord::count();
            $filledPositions    = PlantillaRecord::filled()->count();
            $vacantPositions    = PlantillaRecord::vacant()->count();
            $vacantFundedPositions = PlantillaRecord::vacant()->where('authorized_annual_salary', '>', 0)->count();
            $vacantUnfundedPositions = PlantillaRecord::vacant()->where(function($q) {
                $q->whereNull('authorized_annual_salary')->orWhere('authorized_annual_salary', '<=', 0);
            })->count();
            $abolishedPositions = PlantillaRecord::abolished()->count();
            $permanentEmployeesCount = PlantillaRecord::filled()->whereIn('employment_status', ['P', 'Permanent'])->count();

            $totalSalaryBudget = PlantillaRecord::filled()->sum('actual_annual_salary');

            $pwdCount        = PlantillaRecord::filled()->where('is_pwd', true)->count();
            $ipCount         = PlantillaRecord::filled()->whereNotNull('indigenous_people')->where('indigenous_people', '!=', '')->count();
            $soloParentCount = PlantillaRecord::filled()->whereNotNull('solo_parent')->where('solo_parent', '!=', '')->where('solo_parent', '!=', '-')->count();

            // Employees per organizational unit (top 15 for chart)
            $employeesPerUnit = PlantillaRecord::filled()
                ->selectRaw('organizational_unit, COUNT(*) as count')
                ->groupBy('organizational_unit')
                ->orderByDesc('count')
                ->limit(15)
                ->pluck('count', 'organizational_unit')
                ->toArray();

            // Status breakdown for pie chart
            $statusBreakdown = PlantillaRecord::filled()
                ->selectRaw('employment_status, COUNT(*) as count')
                ->groupBy('employment_status')
                ->pluck('count', 'employment_status')
                ->toArray();

            // Step increment stats — fast SQL-only COUNT (no PHP loops)
            $threeYearsAgo = now()->subYears(3)->toDateString();
            $fiveYearsAgo  = now()->subYears(5)->toDateString();
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
                            $qOu->where('organizational_unit', 'like', '%BPH%')
                                ->orWhere('organizational_unit', 'like', '%HEALTH%')
                                ->orWhere('organizational_unit', 'like', '%MEDICAL%');
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

            // Casual Employees
            $casualTotal  = CasualEmployee::count();
            $casualVacant = CasualEmployee::where('is_vacant', true)->count();

            // Job Orders
            $jobOrderTotal = JobOrder::count();

            // Monthly Birthdays
            $monthlyBirthdays = PlantillaRecord::filled()
                ->whereNotNull('date_of_birth')
                ->whereMonth('date_of_birth', now()->month)
                ->orderByRaw('DAY(date_of_birth)')
                ->get();

            // Age Demographics
            $plantillaAges = DB::table('plantilla_records')
                ->where('is_vacant', false)->where('abolished', false)->whereNotNull('date_of_birth')->whereNull('deleted_at')
                ->selectRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age')
                ->pluck('age');
                
            $casualAges = DB::table('casual_employees')
                ->where('is_vacant', false)->whereNotNull('birthdate')->whereNull('deleted_at')
                ->selectRaw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) as age')
                ->pluck('age');

            $joAges = DB::table('job_orders')
                ->whereNotNull('birthdate')->whereNull('deleted_at')
                ->selectRaw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) as age')
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
            'totalPositions'          => $totalPositions,
            'filledPositions'         => $filledPositions,
            'vacantPositions'         => $vacantPositions,
            'vacantFundedPositions'   => $vacantFundedPositions,
            'vacantUnfundedPositions' => $vacantUnfundedPositions,
            'abolishedPositions'      => $abolishedPositions,
            'permanentEmployeesCount' => $permanentEmployeesCount ?? 0,
            'totalSalaryBudget'       => $totalSalaryBudget,
            'pwdCount'                => $pwdCount,
            'ipCount'                 => $ipCount,
            'soloParentCount'         => $soloParentCount,
            'stepDueCount'            => $stepDueCount,
            'retirementDueCount'      => $retirementDueCount,
            'monthlyBirthdays'        => $monthlyBirthdays,
            'employeesPerUnit'        => $employeesPerUnit,
            'statusBreakdown'         => $statusBreakdown,
            'casualTotal'             => $casualTotal,
            'casualVacant'            => $casualVacant,
            'casualFilled'            => $casualTotal - $casualVacant,
            'jobOrderTotal'           => $jobOrderTotal,
            'ageBrackets'             => $ageBrackets,
        ]);
    }
}
