<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\SalaryGrade;
use App\Models\StepIncrementHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ActivityLog;

class StepIncrementController extends Controller
{
    /**
     * Show list of employees due/overdue for step increment.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'due'); // due | upcoming_nolp | upcoming_nosi | magna_carta | history
        $filterMonth = $request->input('filter_month', now()->format('Y-m'));
        try {
            $targetDate = \Carbon\Carbon::createFromFormat('Y-m', $filterMonth)->startOfMonth();
        } catch (\Exception $e) {
            $targetDate = now()->startOfMonth();
            $filterMonth = now()->format('Y-m');
        }

        // OVERDUE / DUE THIS MONTH
        $allDue = PlantillaRecord::stepDue()
            ->orderBy('office_department')
            ->orderBy('salary_grade')
            ->get();

        $overdueIds = $allDue->filter(fn($r) => $r->next_step_due_date && $r->next_step_due_date->lt($targetDate))->pluck('id');
        
        $dueIds = [];
        $nosiIds = [];
        $nolpIds = [];
        foreach ($allDue as $r) {
            $due = $r->next_step_due_date;
            if ($due && $due->format('Y-m') === $targetDate->format('Y-m')) {
                $dueIds[] = $r->id;
                if ($r->is_hospital_personnel) {
                    $nolpIds[] = $r->id;
                } else {
                    $nosiIds[] = $r->id;
                }
            }
        }

        $overdue = PlantillaRecord::whereIn('id', $overdueIds)
            ->orderBy('office_department')->orderBy('salary_grade')
            ->paginate(15, ['*'], 'od_page')->appends($request->except('od_page'));

        $due = PlantillaRecord::whereIn('id', $dueIds)
            ->orderBy('office_department')->orderBy('salary_grade')
            ->paginate(15, ['*'], 'due_page')->appends($request->except('due_page'));

        $nosi = PlantillaRecord::whereIn('id', $nosiIds)
            ->orderBy('office_department')->orderBy('salary_grade')
            ->paginate(15, ['*'], 'nosi_page')->appends($request->except('nosi_page'));

        $nolp = PlantillaRecord::whereIn('id', $nolpIds)
            ->orderBy('office_department')->orderBy('salary_grade')
            ->paginate(15, ['*'], 'nolp_page')->appends($request->except('nolp_page'));

        // UPCOMING (within 6 months) — split into NOLP (hospital) and NOSI (non-hospital)
        $sixMonthsFromNow = now()->addMonths(6);
        $upcomingAll = PlantillaRecord::filled()
            ->where('employment_status', 'P')
            ->where('step', '<', 8)
            ->whereNotNull('date_original_appointment')
            ->get()
            ->filter(function ($r) use ($sixMonthsFromNow) {
                $due = $r->next_step_due_date;
                return $due && $due->gt(now()) && $due->lte($sixMonthsFromNow);
            });

        $upcomingNolpIds = $upcomingAll->filter(fn($r) => $r->is_hospital_personnel)->pluck('id');
        $upcomingNosiIds = $upcomingAll->filter(fn($r) => !$r->is_hospital_personnel)->pluck('id');

        $upcomingNolp = PlantillaRecord::whereIn('id', $upcomingNolpIds)
            ->orderBy('office_department')->orderBy('salary_grade')
            ->paginate(15, ['*'], 'up_nolp_page')->withQueryString();

        $upcomingNosi = PlantillaRecord::whereIn('id', $upcomingNosiIds)
            ->orderBy('office_department')->orderBy('salary_grade')
            ->paginate(15, ['*'], 'up_nosi_page')->withQueryString();

        $upcomingCount = $upcomingNolpIds->count() + $upcomingNosiIds->count();

        // MAGNA CARTA PRE-RETIREMENT (NOSA) — now a tab inside NOSI/NOLP view
        $magnaCartaDue = PlantillaRecord::magnaCartaNosaDue()
            ->orderBy('office_department')
            ->orderBy('salary_grade')
            ->paginate(15, ['*'], 'mc_page')
            ->withQueryString();

        // INCREMENT OF HISTORY — now a tab inside NOSI/NOLP view
        $historySearch = $request->input('history_search');
        $historyQuery = \App\Models\StepIncrementHistory::with('plantillaRecord')
            ->orderBy('effective_date', 'desc');
        if ($historySearch) {
            $historyQuery->whereHas('plantillaRecord', function ($q) use ($historySearch) {
                $q->where('last_name', 'like', "%{$historySearch}%")
                  ->orWhere('first_name', 'like', "%{$historySearch}%")
                  ->orWhere('item_no_new', 'like', "%{$historySearch}%");
            });
        }
        $histories = $historyQuery->paginate(25, ['*'], 'hist_page')->withQueryString();

        $stats = [
            'overdue'      => $overdueIds->count(),
            'due'          => count($dueIds),
            'upcoming'     => $upcomingCount,
            'upcoming_nolp'=> $upcomingNolpIds->count(),
            'upcoming_nosi'=> $upcomingNosiIds->count(),
            'maxStep'      => PlantillaRecord::filled()->where('step', 8)->count(),
            'magna_carta'  => $magnaCartaDue->total(),
        ];

        return view('step-increment.index', compact(
            'overdue', 'due', 'nosi', 'nolp',
            'upcomingNolp', 'upcomingNosi',
            'magnaCartaDue', 'histories', 'historySearch',
            'stats', 'tab', 'filterMonth'
        ));
    }

    /**
     * Hub page: Plantilla of Personnel — choose Annual / NOSI–NOLP / NOSA.
     */
    public function hub()
    {
        // Quick stats for hub cards
        $allDue = PlantillaRecord::stepDue()->get();
        $overdueCount = $allDue->filter(fn($r) => $r->next_step_due_date && $r->next_step_due_date->lt(now()->startOfMonth()))->count();
        $dueCount     = $allDue->filter(fn($r) => $r->next_step_due_date && $r->next_step_due_date->gte(now()->startOfMonth()) && $r->next_step_due_date->lte(now()))->count();

        $sixMonthsFromNow = now()->addMonths(6);
        $upcomingCount = PlantillaRecord::filled()
            ->where('employment_status', 'P')->where('step', '<', 8)
            ->whereNotNull('date_original_appointment')->get()
            ->filter(fn($r) => ($d = $r->next_step_due_date) && $d->gt(now()) && $d->lte($sixMonthsFromNow))->count();

        $loyaltyCount = PlantillaRecord::filled()
            ->where('employment_status', 'P')
            ->whereNotNull('date_original_appointment')
            ->whereNull('loyalty_dismissed_at')
            ->get()
            ->filter(function ($r) {
                $years = (int) $r->date_original_appointment->diffInYears(now());
                if ($years < 10) return false;
                if ($years === 10) return true;
                return ($years - 10) % 5 === 0;
            })->count();

        $magnaCartaCount = PlantillaRecord::magnaCartaNosaDue()->count();

        $totalPositions = PlantillaRecord::where('abolished', false)->count();
        $filledCount    = PlantillaRecord::filled()->count();
        $vacantCount    = PlantillaRecord::vacant()->count();

        return view('step-increment.hub', compact(
            'overdueCount', 'dueCount', 'upcomingCount',
            'loyaltyCount', 'magnaCartaCount',
            'totalPositions', 'filledCount', 'vacantCount'
        ));
    }

    /**
     * Dedicated Loyalty Incentive list page.
     */
    public function loyalty(Request $request)
    {
        $loyaltyRecords = PlantillaRecord::filled()
            ->where('employment_status', 'P')
            ->whereNotNull('date_original_appointment')
            ->whereNull('loyalty_dismissed_at')
            ->get()
            ->filter(function ($r) {
                if (!$r->date_original_appointment) return false;
                $years = (int) $r->date_original_appointment->diffInYears(now());
                if ($years < 10) return false;
                if ($years === 10) return true;
                $afterTen = $years - 10;
                return $afterTen % 5 === 0;
            })
            ->sortBy('office_department');

        $loyaltyIds = $loyaltyRecords->pluck('id');

        $loyalty = PlantillaRecord::whereIn('id', $loyaltyIds)
            ->orderBy('office_department')
            ->orderBy('salary_grade')
            ->paginate(15)
            ->withQueryString();

        return view('step-increment.loyalty', compact('loyalty'));
    }

    /**
     * NOSA Report — SSL Tranche Adjustment.
     * Shows Permanent or Casual employees with previous vs. new salary from active schedule.
     */
    public function nosaReport(Request $request)
    {
        $type           = $request->input('type', 'permanent');
        $search         = $request->input('search');
        $officeFilter   = $request->input('office');
        $positionFilter = $request->input('position');

        // Active schedule (new tranche)
        $activeSchedule = \App\Models\SalarySchedule::getActive();

        // Previous schedule: The most recent schedule prior to the active one based on effective date
        $previousSchedule = null;
        if ($activeSchedule) {
            $previousSchedule = \App\Models\SalarySchedule::where('id', '!=', $activeSchedule->id)
                ->orderByDesc('effective_date')
                ->orderByDesc('id')
                ->first();
        }

        $query = PlantillaRecord::where(function ($q) use ($type) {
            if ($type === 'casual') {
                $q->whereIn('employment_status', ['Casual', 'C', 'Cas']);
            } else {
                $q->whereIn('employment_status', ['P', 'Permanent', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                  ->orWhere('is_vacant', true);
            }
        })
        ->where('abolished', false)
        ->orderBy('office_department')
        ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item,''), '[^0-9]', '') AS UNSIGNED)");

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('item_no_new', 'like', "%{$search}%")
                  ->orWhere('position_title', 'like', "%{$search}%")
                  ->orWhere('office_department', 'like', "%{$search}%");
            });
        }

        if ($officeFilter) {
            $query->where('office_department', $officeFilter);
        }

        if ($positionFilter) {
            $query->where('position_title', $positionFilter);
        }

        $allRecords = $query->get();

        $grouped = [];
        foreach ($allRecords as $record) {
            $office = $record->office_department ?: 'Unassigned';
            $grouped[$office][] = $record;
        }
        ksort($grouped);

        $offices = PlantillaRecord::distinct()->orderBy('office_department')
            ->pluck('office_department')->filter()->values();

        $positions = PlantillaRecord::distinct()->pluck('position_title')
            ->filter()->map(fn($v) => trim($v))->unique()->sortBy(fn($v) => strtolower($v))->values();

        return view('step-increment.nosa-report', [
            'grouped'          => $grouped,
            'activeSchedule'   => $activeSchedule,
            'previousSchedule' => $previousSchedule,
            'type'             => $type,
            'search'           => $search,
            'offices'          => $offices,
            'positions'        => $positions,
        ]);
    }

    /**
     * Export a single office NOSA — PDF.
     */
    public function exportNosaReportPdfByOffice(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $officeName = $request->query('office', '');
        $type       = $request->input('type', 'permanent');

        $activeSchedule   = \App\Models\SalarySchedule::getActive();
        $previousSchedule = null;
        if ($activeSchedule) {
            $previousSchedule = \App\Models\SalarySchedule::where('id', '!=', $activeSchedule->id)
                ->orderByDesc('effective_date')
                ->orderByDesc('id')
                ->first();
        }

        $query = PlantillaRecord::where(function ($q) use ($type) {
            if ($type === 'casual') {
                $q->whereIn('employment_status', ['Casual', 'C', 'Cas']);
            } else {
                $q->whereIn('employment_status', ['P', 'Permanent', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                  ->orWhere('is_vacant', true);
            }
        })
        ->where('abolished', false)
        ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item,''), '[^0-9]', '') AS UNSIGNED)");

        if ($officeName) {
            $query->where('office_department', $officeName);
        }

        $allRecords = $query->get();

        $grouped = [];
        foreach ($allRecords as $record) {
            $office = $record->office_department ?: 'Unassigned';
            $grouped[$office][] = $record;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('step-increment.nosa-report-pdf', [
            'grouped'          => $grouped,
            'activeSchedule'   => $activeSchedule,
            'previousSchedule' => $previousSchedule,
            'officeName'       => $officeName ?: null,
            'type'             => $type,
        ])->setPaper([0, 0, 612.00, 936.00], 'landscape');

        $safeFilename = preg_replace('/[^a-z0-9_\-]/i', '_', $officeName ?: 'All');
        return $pdf->download('NOSA_' . $safeFilename . '_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Show salary table for a given salary grade.
     */
    public function salaryTable(Request $request)
    {
        // Build a full [grade => [step => monthly_salary]] matrix for the grid view
        $allRates = SalaryGrade::orderBy('grade')->orderBy('step')->get();

        $matrix = [];
        foreach ($allRates as $row) {
            $matrix[$row->grade][$row->step] = $row->monthly_salary;
        }

        return view('step-increment.salary-table', [
            'matrix' => $matrix,
        ]);
    }

    /**
     * Process a step increment for a plantilla record.
     */
    public function process(Request $request, PlantillaRecord $plantilla)
    {
        // Validate
        if ($plantilla->is_vacant) {
            return back()->with('error', 'Cannot process step increment for a vacant position.');
        }
        if ($plantilla->step >= 8) {
            return back()->with('error', 'Employee is already at the maximum step (Step 8).');
        }

        $dueType = $plantilla->due_type;
        $stepIncrease = ($dueType === 'nolp' || $dueType === 'both') ? 2 : 1;
        
        $previousStep = $plantilla->step;
        $previousSg = $plantilla->salary_grade;
        $previousSalary = $plantilla->base_salary_amount;

        $newStep = min(8, $plantilla->step + $stepIncrease);

        // Get new salary from SSL table
        $newMonthlySalary = SalaryGrade::getRate($plantilla->salary_grade, $newStep);
        $newAnnualSalary  = round($newMonthlySalary * 12, 2);

        $updates = [
            'step'                 => $newStep,
            'base_salary_amount' => $newAnnualSalary ?: $plantilla->base_salary_amount,
        ];

        if ($dueType === 'nolp' || $dueType === 'both') {
            $updates['date_last_nolp'] = now()->toDateString();
        }
        if ($dueType === 'nosi' || $dueType === 'both') {
            $updates['date_last_promotion'] = now()->toDateString();
        }

        $plantilla->update($updates);

        $logType = str_contains($dueType, 'nolp') ? 'NOLP' : 'NOSI';
        StepIncrementHistory::create([
            'plantilla_record_id' => $plantilla->id,
            'type' => $logType,
            'previous_step' => $previousStep,
            'new_step' => $newStep,
            'previous_salary_grade' => $previousSg,
            'new_salary_grade' => $previousSg,
            'previous_annual_salary' => $previousSalary,
            'new_annual_salary' => $newAnnualSalary,
            'effective_date' => now()->toDateString(),
        ]);

        $typeLabel = ($dueType === 'nolp') ? 'NOLP (Longevity)' : 'NOSI (Step Increment)';
        if ($dueType === 'both') $typeLabel = 'NOSI & NOLP';

        return back()->with('success',
            "{$typeLabel} processed for {$plantilla->full_name}. " .
            "New step: {$newStep}, New annual salary: ₱" . number_format($newAnnualSalary, 2)
        );
    }
    /**
     * Process all eligible due step increments automatically.
     */
    public function processAllDue(Request $request)
    {
        $dueRecords = PlantillaRecord::stepDue()
            ->where('step', '<', 8)
            ->get();

        if ($dueRecords->isEmpty()) {
            return back()->with('info', 'No eligible employees found for step increment processing.');
        }

        $count = 0;
        foreach ($dueRecords as $plantilla) {
            $dueType = $plantilla->due_type;
            if (!$dueType) continue; // safety check

            $previousStep = $plantilla->step;
            $previousSg = $plantilla->salary_grade;
            $previousSalary = $plantilla->base_salary_amount;

            $stepIncrease = ($dueType === 'nolp' || $dueType === 'both') ? 2 : 1;
            $newStep = min(8, $plantilla->step + $stepIncrease);
            
            // Look up the exact new salary from the matrix
            $newMonthlySalary = SalaryGrade::getRate($plantilla->salary_grade, $newStep);
            $newAnnualSalary  = round($newMonthlySalary * 12, 2);

            $updates = [
                'step'                 => $newStep,
                'base_salary_amount' => $newAnnualSalary ?: $plantilla->base_salary_amount,
            ];

            if ($dueType === 'nolp' || $dueType === 'both') {
                $updates['date_last_nolp'] = now()->toDateString();
            }
            if ($dueType === 'nosi' || $dueType === 'both') {
                $updates['date_last_promotion'] = now()->toDateString();
            }

            $plantilla->update($updates);

            $logType = str_contains($dueType, 'nolp') ? 'NOLP' : 'NOSI';
            StepIncrementHistory::create([
                'plantilla_record_id' => $plantilla->id,
                'type' => $logType,
                'previous_step' => $previousStep,
                'new_step' => $newStep,
                'previous_salary_grade' => $previousSg,
                'new_salary_grade' => $previousSg,
                'previous_annual_salary' => $previousSalary,
                'new_annual_salary' => $newAnnualSalary,
                'effective_date' => now()->toDateString(),
            ]);

            $count++;
        }

        return back()->with('success', "Bulk processing successful! Processed step increments for {$count} employee(s).");
    }
    /**
     * Show the formal exact Office Salary Plantilla Report format.
     * Shows ALL records (not just step-increment eligible) grouped by office.
     */
    public function officeReport(Request $request)
    {
        $search = $request->input('search');
        $officeFilter = $request->input('office');
        $positionFilter = $request->input('position');

        $type = $request->input('type');
        $query = PlantillaRecord::where(function($q) use ($type) {
                if ($type === 'casual') {
                    $q->whereIn('employment_status', ['Casual', 'C', 'Cas']);
                } elseif ($type === 'permanent') {
                    $q->whereIn('employment_status', ['P', 'Permanent', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                      ->orWhere('is_vacant', true);
                } else {
                    $q->whereIn('employment_status', ['P', 'Permanent', 'Casual', 'C', 'Cas', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                      ->orWhere('is_vacant', true);
                }
            })
            ->where('abolished', false)
            ->orderBy('office_department')
            ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item,''), '[^0-9]', '') AS UNSIGNED)");

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('item_no_new', 'like', "%{$search}%")
                  ->orWhere('position_title', 'like', "%{$search}%")
                  ->orWhere('office_department', 'like', "%{$search}%");
            });
        }

        if ($officeFilter) {
            $query->where('office_department', $officeFilter);
        }

        if ($positionFilter) {
            $query->where('position_title', $positionFilter);
        }

        $allRecords = $query->get();

        $grouped = [];
        foreach ($allRecords as $record) {
            $office = $record->office_department ?: 'Unassigned';
            $grouped[$office][] = $record;
        }
        ksort($grouped);

        $offices = PlantillaRecord::distinct()->orderBy('office_department')
            ->pluck('office_department')->filter()->values();
        
        $positions = PlantillaRecord::distinct()->pluck('position_title')
            ->filter()->map(fn($v) => trim($v))->unique()->sortBy(fn($v) => strtolower($v))->values();

        return view('step-increment.office-report', [
            'grouped' => $grouped,
            'search' => $search,
            'offices' => $offices,
            'positions' => $positions
        ]);
    }


    /**
     * Export ALL offices – Plantilla of Personnel format – Excel.
     */
    public function exportOfficeReportExcel(Request $request)
    {
        $type = $request->input('type');
        $allRecords = PlantillaRecord::where(function($q) use ($type) {
                if ($type === 'casual') {
                    $q->whereIn('employment_status', ['Casual', 'C', 'Cas']);
                } elseif ($type === 'permanent') {
                    $q->whereIn('employment_status', ['P', 'Permanent', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                      ->orWhere('is_vacant', true);
                } else {
                    $q->whereIn('employment_status', ['P', 'Permanent', 'Casual', 'C', 'Cas', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                      ->orWhere('is_vacant', true);
                }
            })
            ->where('abolished', false)
            ->orderBy('office_department')
            ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item,''), '[^0-9]', '') AS UNSIGNED)")
            ->get();

        $grouped = [];
        foreach ($allRecords as $record) {
            $office = $record->office_department ?: 'Unassigned';
            $grouped[$office][] = $record;
        }
        ksort($grouped);

        return \Excel::download(
            new \App\Exports\PlantillaReportExport($grouped),
            'Plantilla_of_Personnel_' . now()->format('Ymd') . '.xlsx'
        );
    }

    /**
     * Export ALL offices – Plantilla of Personnel format – PDF.
     */
    public function exportOfficeReportPdf(Request $request)
    {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '2048M');

        $type = $request->input('type');
        $allRecords = PlantillaRecord::where(function($q) use ($type) {
                if ($type === 'casual') {
                    $q->whereIn('employment_status', ['Casual', 'C', 'Cas']);
                } elseif ($type === 'permanent') {
                    $q->whereIn('employment_status', ['P', 'Permanent', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                      ->orWhere('is_vacant', true);
                } else {
                    $q->whereIn('employment_status', ['P', 'Permanent', 'Casual', 'C', 'Cas', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                      ->orWhere('is_vacant', true);
                }
            })
            ->where('abolished', false)
            ->orderBy('office_department')
            ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item,''), '[^0-9]', '') AS UNSIGNED)")
            ->get();

        $grouped = [];
        foreach ($allRecords as $record) {
            $office = $record->office_department ?: 'Unassigned';
            $grouped[$office][] = $record;
        }
        ksort($grouped);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('step-increment.plantilla-report-pdf', [
            'grouped'    => $grouped,
            'officeName' => null,
            'mode'       => $request->input('mode', 'annual'),
            'type'       => $type,
        ])->setPaper([0, 0, 612.00, 936.00], 'landscape');

        return $pdf->download('Plantilla_of_Personnel_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Export a single office – Plantilla of Personnel format – Excel.
     */
    public function exportOfficeReportExcelByOffice(Request $request)
    {
        $officeName = $request->query('office', '');

        $type = $request->input('type');
        $query = PlantillaRecord::where(function($q) use ($type) {
                if ($type === 'casual') {
                    $q->whereIn('employment_status', ['Casual', 'C', 'Cas']);
                } elseif ($type === 'permanent') {
                    $q->whereIn('employment_status', ['P', 'Permanent', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                      ->orWhere('is_vacant', true);
                } else {
                    $q->whereIn('employment_status', ['P', 'Permanent', 'Casual', 'C', 'Cas', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                      ->orWhere('is_vacant', true);
                }
            })
            ->where('abolished', false)
            ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item,''), '[^0-9]', '') AS UNSIGNED)");
        if ($officeName) {
            $query->where('office_department', $officeName);
        }
        $allRecords = $query->get();

        $grouped = [];
        foreach ($allRecords as $record) {
            $office = $record->office_department ?: 'Unassigned';
            $grouped[$office][] = $record;
        }

        $safeFilename = preg_replace('/[^a-z0-9_\-]/i', '_', $officeName ?: 'All');
        return \Excel::download(
            new \App\Exports\PlantillaReportExport($grouped, $officeName ?: null),
            'Plantilla_' . $safeFilename . '_' . now()->format('Ymd') . '.xlsx'
        );
    }

    /**
     * Export a single office – Plantilla of Personnel format – PDF.
     */
    public function exportOfficeReportPdfByOffice(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $officeName = $request->query('office', '');

        $type = $request->input('type');
        $query = PlantillaRecord::where(function($q) use ($type) {
                if ($type === 'casual') {
                    $q->whereIn('employment_status', ['Casual', 'C', 'Cas']);
                } elseif ($type === 'permanent') {
                    $q->whereIn('employment_status', ['P', 'Permanent', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                      ->orWhere('is_vacant', true);
                } else {
                    $q->whereIn('employment_status', ['P', 'Permanent', 'Casual', 'C', 'Cas', 'Co-Terminous', 'Coterminous', 'CT', 'Elected', 'E'])
                      ->orWhere('is_vacant', true);
                }
            })
            ->where('abolished', false)
            ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item,''), '[^0-9]', '') AS UNSIGNED)");
        if ($officeName) {
            $query->where('office_department', $officeName);
        }
        $allRecords = $query->get();

        $grouped = [];
        foreach ($allRecords as $record) {
            $office = $record->office_department ?: 'Unassigned';
            $grouped[$office][] = $record;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('step-increment.plantilla-report-pdf', [
            'grouped'    => $grouped,
            'officeName' => $officeName ?: null,
            'mode'       => $request->input('mode', 'annual'),
            'type'       => $type,
        ])->setPaper([0, 0, 612.00, 936.00], 'landscape');

        $safeFilename = preg_replace('/[^a-z0-9_\-]/i', '_', $officeName ?: 'All');
        return $pdf->download('Plantilla_' . $safeFilename . '_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Process Magna Carta (Health Worker) NOSA (SG + 1, Retain Step).
     */
    public function processMagnaCarta(Request $request, PlantillaRecord $plantilla)
    {
        if ($plantilla->is_vacant) {
            return back()->with('error', 'Cannot process NOSA for a vacant position.');
        }
        if ($plantilla->salary_grade >= 33) {
            return back()->with('error', 'Employee is already at the maximum salary grade.');
        }

        $previousStep = $plantilla->step;
        $previousSg = $plantilla->salary_grade;
        $previousSalary = $plantilla->base_salary_amount;

        $newSg = min(33, $plantilla->salary_grade + 1);
        $newStep = $plantilla->step; // retain step
        
        $newMonthlySalary = SalaryGrade::getRate($newSg, $newStep);
        $newAnnualSalary  = round($newMonthlySalary * 12, 2);

        $plantilla->update([
            'salary_grade' => $newSg,
            'base_salary_amount' => $newAnnualSalary,
            'authorized_annual_salary' => $newAnnualSalary,
        ]);

        StepIncrementHistory::create([
            'plantilla_record_id' => $plantilla->id,
            'type' => 'NOSA',
            'previous_step' => $previousStep,
            'new_step' => $newStep,
            'previous_salary_grade' => $previousSg,
            'new_salary_grade' => $newSg,
            'previous_annual_salary' => $previousSalary,
            'new_annual_salary' => $newAnnualSalary,
            'effective_date' => now()->toDateString(),
        ]);

        return back()->with('success', "Magna Carta NOSA processed for {$plantilla->full_name}. SG updated to {$newSg}.");
    }

    /**
     * Dismiss an employee from the Loyalty Incentive list for this cycle.
     */
    public function dismissLoyalty(Request $request, PlantillaRecord $plantilla)
    {
        $plantilla->update(['loyalty_dismissed_at' => now()]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Dismiss Loyalty',
            'description' => "Dismissed {$plantilla->full_name} from the Loyalty Incentive list.",
        ]);

        return back()->with('success', "{$plantilla->full_name} has been removed from the Loyalty Incentive list.");
    }

    /**
     * Restore an employee back to the Loyalty Incentive list.
     */
    public function restoreLoyalty(Request $request, PlantillaRecord $plantilla)
    {
        $plantilla->update(['loyalty_dismissed_at' => null]);

        return back()->with('success', "{$plantilla->full_name} has been restored to the Loyalty Incentive list.");
    }

    /**
     * Show ALL step increments histories across all employees.
     */
    public function history(Request $request)
    {
        $search = $request->input('search');

        $query = StepIncrementHistory::with('plantillaRecord')
            ->orderBy('effective_date', 'desc');

        if ($search) {
            $query->whereHas('plantillaRecord', function ($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('item_no_new', 'like', "%{$search}%");
            });
        }

        $histories = $query->paginate(25)->withQueryString();

        return view('step-increment.history', compact('histories', 'search'));
    }
}
