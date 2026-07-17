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
            'salary_misaligned' => $this->salaryAlignmentCounts()['misaligned'],
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

        $loyaltyCount = PlantillaRecord::loyaltyDue()->get()
            ->filter(fn($r) => $r->is_loyalty_due)->count();

        $magnaCartaCount = PlantillaRecord::magnaCartaNosaDue()->count();

        $totalPositions = PlantillaRecord::where('abolished', false)->count();
        $filledCount    = PlantillaRecord::filled()->count();
        $vacantCount    = PlantillaRecord::vacant()->count();

        $salaryMisalignedCount = $this->salaryAlignmentCounts()['misaligned'];

        return view('step-increment.hub', compact(
            'overdueCount', 'dueCount', 'upcomingCount',
            'loyaltyCount', 'magnaCartaCount',
            'totalPositions', 'filledCount', 'vacantCount',
            'salaryMisalignedCount'
        ));
    }

    /**
     * Bucket every in-scope (P/CT) record into aligned/misaligned/unresolvable against
     * the active Salary Schedule. Shared by hub()'s stat tile and salaryAlignment() so the
     * bucketing logic isn't duplicated a third time (the same duplication being fixed for
     * loyalty above).
     */
    private function salaryAlignmentCounts(): array
    {
        $counts = ['aligned' => 0, 'misaligned' => 0, 'unresolvable' => 0];
        foreach (PlantillaRecord::sslInScope()->get() as $r) {
            $status = $r->salary_alignment_status;
            if ($status === 'aligned') {
                $counts['aligned']++;
            } elseif ($status === 'unresolvable') {
                $counts['unresolvable']++;
            } else {
                $counts['misaligned']++;
            }
        }
        return $counts;
    }

    /**
     * Dedicated Loyalty Incentive list page.
     */
    public function loyalty(Request $request)
    {
        $loyaltyRecords = PlantillaRecord::loyaltyDue()->get()
            ->filter(fn($r) => $r->is_loyalty_due)
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
        if ($activeSchedule && $activeSchedule->effective_date) {
            // Must be chronologically BEFORE the active schedule — a later/reserve
            // tranche (e.g. a future 3rd Tranche) is never a valid "previous" baseline.
            $previousSchedule = \App\Models\SalarySchedule::where('id', '!=', $activeSchedule->id)
                ->where('effective_date', '<', $activeSchedule->effective_date)
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
        ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item_no_new,''), '[^0-9]', '') AS UNSIGNED)");

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
        if ($activeSchedule && $activeSchedule->effective_date) {
            // Must be chronologically BEFORE the active schedule — a later/reserve
            // tranche (e.g. a future 3rd Tranche) is never a valid "previous" baseline.
            $previousSchedule = \App\Models\SalarySchedule::where('id', '!=', $activeSchedule->id)
                ->where('effective_date', '<', $activeSchedule->effective_date)
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
        ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item_no_new,''), '[^0-9]', '') AS UNSIGNED)");

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
        if ($request->has('download')) {
            return $pdf->download('NOSA_' . $safeFilename . '_' . now()->format('Ymd') . '.pdf');
        }
        return $pdf->stream('NOSA_' . $safeFilename . '_' . now()->format('Ymd') . '.pdf');
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
            ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item_no_new,''), '[^0-9]', '') AS UNSIGNED)");

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
            ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item_no_new,''), '[^0-9]', '') AS UNSIGNED)")
            ->get();

        $grouped = [];
        foreach ($allRecords as $record) {
            $office = $record->office_department ?: 'Unassigned';
            $grouped[$office][] = $record;
        }
        ksort($grouped);

        $preparedBy = 'AIDA B. LOVERES';
        $reviewedBy = 'MAYFE P. ALERTA';
        $approvedBy = 'ROGELIO NEIL P. ROQUE';
        $currentTranche = 'LBC # ___ 1st Tranche';
        $proposedTranche = 'EO #64 3rd Tranche';
        $year = now()->year + 1;
        $employmentType = $type === 'casual' ? 'Casual' : 'Permanent';

        return \Excel::download(
            new \App\Exports\LbpForm3Export($employmentType, null, $year, $preparedBy, $reviewedBy, $approvedBy, $currentTranche, $proposedTranche),
            'LBP_FORM_3_All_Offices_' . now()->format('Ymd') . '.xlsx'
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
            ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item_no_new,''), '[^0-9]', '') AS UNSIGNED)")
            ->get();

        $grouped = [];
        foreach ($allRecords as $record) {
            $office = $record->office_department ?: 'Unassigned';
            $grouped[$office][] = $record;
        }
        ksort($grouped);

        $preparedBy = 'AIDA B. LOVERES';
        $reviewedBy = 'MAYFE P. ALERTA';
        $approvedBy = 'ROGELIO NEIL P. ROQUE';
        $currentTranche = 'LBC # ___ 1st Tranche';
        $proposedTranche = 'EO #64 3rd Tranche';
        $year = now()->year + 1;
        $employmentType = $type === 'casual' ? 'Casual' : 'Permanent';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('plantilla.exports.lbp-form-3', [
            'groupedRecords'  => $grouped,
            'employmentType'  => $employmentType,
            'office'          => null,
            'year'            => $year,
            'preparedBy'      => $preparedBy,
            'reviewedBy'      => $reviewedBy,
            'approvedBy'      => $approvedBy,
            'currentTranche'  => $currentTranche,
            'proposedTranche' => $proposedTranche
        ])->setPaper([0, 0, 612.00, 936.00], 'portrait');

        if ($request->has('download')) {
            return $pdf->download('LBP_FORM_3_All_Offices_' . now()->format('Ymd') . '.pdf');
        }
        return $pdf->stream('LBP_FORM_3_All_Offices_' . now()->format('Ymd') . '.pdf');
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
            ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item_no_new,''), '[^0-9]', '') AS UNSIGNED)");
        if ($officeName) {
            $query->where('office_department', $officeName);
        }
        $allRecords = $query->get();

        $grouped = [];
        foreach ($allRecords as $record) {
            $office = $record->office_department ?: 'Unassigned';
            $grouped[$office][] = $record;
        }

        $preparedBy = 'AIDA B. LOVERES';
        $reviewedBy = 'MAYFE P. ALERTA';
        $approvedBy = 'ROGELIO NEIL P. ROQUE';
        $currentTranche = 'LBC # ___ 1st Tranche';
        $proposedTranche = 'EO #64 3rd Tranche';
        $year = now()->year + 1;
        $employmentType = $type === 'casual' ? 'Casual' : 'Permanent';

        $safeFilename = preg_replace('/[^a-z0-9_\-]/i', '_', $officeName ?: 'All');
        return \Excel::download(
            new \App\Exports\LbpForm3Export($employmentType, $officeName ?: null, $year, $preparedBy, $reviewedBy, $approvedBy, $currentTranche, $proposedTranche),
            'LBP_FORM_3_' . $safeFilename . '_' . now()->format('Ymd') . '.xlsx'
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
            ->orderByRaw("CAST(REGEXP_REPLACE(COALESCE(item_no_new,''), '[^0-9]', '') AS UNSIGNED)");
        if ($officeName) {
            $query->where('office_department', $officeName);
        }
        $allRecords = $query->get();

        $grouped = [];
        foreach ($allRecords as $record) {
            $office = $record->office_department ?: 'Unassigned';
            $grouped[$office][] = $record;
        }

        $preparedBy = 'AIDA B. LOVERES';
        $reviewedBy = 'MAYFE P. ALERTA';
        $approvedBy = 'ROGELIO NEIL P. ROQUE';
        $currentTranche = 'LBC # ___ 1st Tranche';
        $proposedTranche = 'EO #64 3rd Tranche';
        $year = now()->year + 1;
        $employmentType = $type === 'casual' ? 'Casual' : 'Permanent';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('plantilla.exports.lbp-form-3', [
            'groupedRecords'  => $grouped,
            'employmentType'  => $employmentType,
            'office'          => $officeName ?: null,
            'year'            => $year,
            'preparedBy'      => $preparedBy,
            'reviewedBy'      => $reviewedBy,
            'approvedBy'      => $approvedBy,
            'currentTranche'  => $currentTranche,
            'proposedTranche' => $proposedTranche
        ])->setPaper([0, 0, 612.00, 936.00], 'portrait');

        $safeFilename = preg_replace('/[^a-z0-9_\-]/i', '_', $officeName ?: 'All');
        if ($request->has('download')) {
            return $pdf->download('LBP_FORM_3_' . $safeFilename . '_' . now()->format('Ymd') . '.pdf');
        }
        return $pdf->stream('LBP_FORM_3_' . $safeFilename . '_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Export a SINGLE individual's Plantilla row — PDF.
     */
    public function exportOfficeReportPdfIndividual(PlantillaRecord $plantilla, Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $grouped = [];
        $office = $plantilla->office_department ?: 'Unassigned';
        $grouped[$office][] = $plantilla;

        $type = $request->input('type', 'permanent');

        $preparedBy = 'AIDA B. LOVERES';
        $reviewedBy = 'MAYFE P. ALERTA';
        $approvedBy = 'ROGELIO NEIL P. ROQUE';
        $currentTranche = 'LBC # ___ 1st Tranche';
        $proposedTranche = 'EO #64 3rd Tranche';
        $year = now()->year + 1;
        $employmentType = $type === 'casual' ? 'Casual' : 'Permanent';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('plantilla.exports.lbp-form-3', [
            'groupedRecords'  => $grouped,
            'employmentType'  => $employmentType,
            'office'          => $office,
            'year'            => $year,
            'preparedBy'      => $preparedBy,
            'reviewedBy'      => $reviewedBy,
            'approvedBy'      => $approvedBy,
            'currentTranche'  => $currentTranche,
            'proposedTranche' => $proposedTranche
        ])->setPaper([0, 0, 612.00, 936.00], 'portrait');

        $safeFilename = preg_replace('/[^a-z0-9_\-]/i', '_', $plantilla->full_name);
        if ($request->has('download')) {
            return $pdf->download('LBP_FORM_3_Row_' . $safeFilename . '_' . now()->format('Ymd') . '.pdf');
        }
        return $pdf->stream('LBP_FORM_3_Row_' . $safeFilename . '_' . now()->format('Ymd') . '.pdf');
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
     * Salary Alignment tab: bucket in-scope (Permanent/Co-Terminous) records into
     * aligned / misaligned / unresolvable against the active Salary Schedule.
     */
    public function salaryAlignment(Request $request)
    {
        $search       = $request->input('search');
        $officeFilter = $request->input('office');
        $statusFilter = $request->input('status', 'misaligned'); // misaligned | unresolvable | aligned

        $activeSchedule = \App\Models\SalarySchedule::getActive();

        $query = PlantillaRecord::sslInScope();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('item_no_new', 'like', "%{$search}%");
            });
        }
        if ($officeFilter) {
            $query->where('office_department', $officeFilter);
        }

        $all = $query->get();

        $buckets = [
            'aligned'      => $all->filter(fn($r) => $r->salary_alignment_status === 'aligned'),
            'misaligned'   => $all->filter(fn($r) => $r->is_salary_misaligned),
            'unresolvable' => $all->filter(fn($r) => $r->salary_alignment_status === 'unresolvable'),
        ];

        $selected = $buckets[$statusFilter] ?? $buckets['misaligned'];
        $ids = $selected->pluck('id');
        $records = PlantillaRecord::whereIn('id', $ids)
            ->orderBy('office_department')->orderBy('salary_grade')
            ->paginate(15)->withQueryString();

        $offices = PlantillaRecord::distinct()->orderBy('office_department')
            ->pluck('office_department')->filter()->values();

        return view('step-increment.salary-alignment', [
            'records'        => $records,
            'activeSchedule' => $activeSchedule,
            'counts'         => [
                'aligned'      => $buckets['aligned']->count(),
                'misaligned'   => $buckets['misaligned']->count(),
                'unresolvable' => $buckets['unresolvable']->count(),
            ],
            'statusFilter'   => $statusFilter,
            'search'         => $search,
            'officeFilter'   => $officeFilter,
            'offices'        => $offices,
        ]);
    }

    /**
     * Reconcile one record's base_salary_amount to the active schedule's Grade/Step rate.
     * Writes the new amount back in the SAME basis (salary_type) the record already used,
     * so a pay fix doesn't silently change the record's storage convention as a side effect.
     * Returns a short human-readable outcome string used by both single and bulk callers.
     */
    private function reconcileOne(PlantillaRecord $plantilla): string
    {
        if ($plantilla->is_vacant) {
            return 'vacant';
        }
        if (!in_array($plantilla->employment_status, PlantillaRecord::SSL_ALIGNMENT_STATUSES, true)) {
            return 'not_in_scope';
        }
        $status = $plantilla->salary_alignment_status;
        if ($status === 'unresolvable') {
            return 'unresolvable';
        }
        if ($status === 'aligned') {
            return 'already_aligned';
        }

        $active = \App\Models\SalarySchedule::getActive();

        $previousAnnual = in_array($plantilla->salary_type, PlantillaRecord::MONTHLY_BASIS_SALARY_TYPES, true)
            ? round(((float) $plantilla->base_salary_amount) * 12, 2)
            : (float) $plantilla->base_salary_amount;

        $newMonthly = $plantilla->expected_monthly_rate;
        $newAnnual  = round($newMonthly * 12, 2);

        $newBaseSalaryAmount = in_array($plantilla->salary_type, PlantillaRecord::MONTHLY_BASIS_SALARY_TYPES, true)
            ? round($newMonthly, 2)
            : $newAnnual;

        $plantilla->update(['base_salary_amount' => $newBaseSalaryAmount]);

        StepIncrementHistory::create([
            'plantilla_record_id'    => $plantilla->id,
            'salary_schedule_id'     => $active?->id,
            'type'                   => 'SSL_ADJUSTMENT',
            'previous_step'          => $plantilla->step,
            'new_step'               => $plantilla->step,
            'previous_salary_grade'  => $plantilla->salary_grade,
            'new_salary_grade'       => $plantilla->salary_grade,
            'previous_annual_salary' => $previousAnnual,
            'new_annual_salary'      => $newAnnual,
            'effective_date'         => now()->toDateString(),
        ]);

        return 'reconciled';
    }

    /**
     * Reconcile ONE employee's salary to the active schedule (human-confirmed action).
     */
    public function reconcileSalary(Request $request, PlantillaRecord $plantilla)
    {
        $newMonthly = $plantilla->expected_monthly_rate;
        $outcome = $this->reconcileOne($plantilla);

        return match ($outcome) {
            'vacant'          => back()->with('error', 'Cannot reconcile salary for a vacant position.'),
            'not_in_scope'    => back()->with('error', 'Salary alignment only applies to Permanent/Co-Terminous employees.'),
            'unresolvable'    => back()->with('error', 'Cannot reconcile: no matching Grade/Step rate found in the active schedule, or salary_type/grade/step data is missing.'),
            'already_aligned' => back()->with('info', "{$plantilla->full_name} is already aligned to the active schedule."),
            'reconciled'      => back()->with('success', "Salary reconciled for {$plantilla->full_name}. New monthly rate: ₱" . number_format($newMonthly, 2)),
            default           => back()->with('error', 'Unable to reconcile salary.'),
        };
    }

    /**
     * Reconcile a human-selected batch of employees to the active schedule — still a single
     * confirmed click, not an unattended background job.
     */
    public function reconcileSalarySelected(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        $reconciled = 0;
        $skipped = 0;

        foreach (PlantillaRecord::whereIn('id', $ids)->get() as $plantilla) {
            if ($this->reconcileOne($plantilla) === 'reconciled') {
                $reconciled++;
            } else {
                $skipped++;
            }
        }

        if ($reconciled === 0) {
            return back()->with('info', 'No selected records needed reconciliation.');
        }

        $message = "Reconciled {$reconciled} employee(s) to the active schedule.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} (already aligned, vacant, or unresolvable).";
        }

        return back()->with('success', $message);
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
