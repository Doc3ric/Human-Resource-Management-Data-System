<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\SalaryGrade;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    /**
     * Generate bulk A4 PDF for Notice of Step Increment (NOSI)
     */
    public function generateNosiBulk(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $month = $request->query('month', now()->format('Y-m'));
        try {
            $targetDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $targetDate = now()->startOfMonth();
        }

        $allDue = PlantillaRecord::stepDue()
            ->where('is_vacant', false)
            ->orderBy('office_department')
            ->orderBy('salary_grade')
            ->get();

        // Get exactly the ones due in the specified month
        $employees = $allDue->filter(function($r) use ($targetDate) {
            $due = $r->next_step_due_date;
            return !$r->is_hospital_personnel && 
                   $due && 
                   $due->year === $targetDate->year && 
                   $due->month === $targetDate->month;
        });

        if ($employees->isEmpty()) {
            return back()->with('error', 'No NOSI records found for this month.');
        }

        $records = [];
        foreach ($employees as $plantilla) {
            $currentStep = $plantilla->step ?: 1;
            $currentSalary = SalaryGrade::getRate($plantilla->salary_grade, $currentStep);
            $newStep = min(8, $currentStep + 1);
            $newSalary = SalaryGrade::getRate($plantilla->salary_grade, $newStep);
            $diff = $newSalary - $currentSalary;
            
            $appointmentDate = $plantilla->date_last_promotion ?? $plantilla->date_original_appointment;
            $effectiveDate = $appointmentDate ? $appointmentDate->clone()->addYears(3) : now();

            $records[] = [
                'type' => 'NOSI',
                'employee' => $plantilla,
                'currentSalary' => $currentSalary,
                'currentStep' => $currentStep,
                'newStep' => $newStep,
                'newSalary' => $newSalary,
                'diff' => $diff,
                'effectiveDate' => $effectiveDate,
            ];
        }

        $pdf = Pdf::loadView('step-increment.pdf.nosi-bulk', compact('records'))->setPaper('a4', 'portrait');
        if ($request->has('download')) {
            return $pdf->download('NOSI_Bulk_' . $targetDate->format('M_Y') . '.pdf');
        }
        return $pdf->stream('NOSI_Bulk_' . $targetDate->format('M_Y') . '.pdf');
    }

    /**
     * Generate bulk A4 PDF for Notice of Longevity Pay (NOLP)
     */
    public function generateNolpBulk(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $month = $request->query('month', now()->format('Y-m'));
        try {
            $targetDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $targetDate = now()->startOfMonth();
        }

        $allDue = PlantillaRecord::stepDue()
            ->where('is_vacant', false)
            ->orderBy('office_department')
            ->orderBy('salary_grade')
            ->get();

        $employees = $allDue->filter(function($r) use ($targetDate) {
            $due = $r->next_step_due_date;
            return $r->is_hospital_personnel && 
                   $due && 
                   $due->year === $targetDate->year && 
                   $due->month === $targetDate->month;
        });

        if ($employees->isEmpty()) {
            return back()->with('error', 'No NOLP records found for this month.');
        }

        $records = [];
        foreach ($employees as $plantilla) {
            $currentStep = $plantilla->step ?: 1;
            $currentSalary = SalaryGrade::getRate($plantilla->salary_grade, $currentStep);
            $newStep = min(8, $currentStep + 2);
            $newSalary = SalaryGrade::getRate($plantilla->salary_grade, $newStep);
            $diff = $newSalary - $currentSalary;
            
            $appointmentDate = $plantilla->date_last_nolp ?? $plantilla->date_original_appointment;
            $effectiveDate = $appointmentDate ? $appointmentDate->clone()->addYears(5) : now();

            $records[] = [
                'type' => 'NOLP',
                'employee' => $plantilla,
                'currentSalary' => $currentSalary,
                'currentStep' => $currentStep,
                'newStep' => $newStep,
                'newSalary' => $newSalary,
                'diff' => $diff,
                'effectiveDate' => $effectiveDate,
            ];
        }

        $pdf = Pdf::loadView('step-increment.pdf.nolp-bulk', compact('records'))->setPaper('a4', 'portrait');
        if ($request->has('download')) {
            return $pdf->download('NOLP_Bulk_' . $targetDate->format('M_Y') . '.pdf');
        }
        return $pdf->stream('NOLP_Bulk_' . $targetDate->format('M_Y') . '.pdf');
    }
    /**
     * Generate A4 PDF for Notice of Step Increment (NOSI)
     */
    public function generateNosi(Request $request, PlantillaRecord $plantilla)
    {
        // Require that we have the employee's info
        if ($plantilla->is_vacant) {
            return back()->with('error', 'Cannot generate NOSI for a vacant position.');
        }

        // Get actual current salary based on step
        $currentStep = $plantilla->step ?: 1;
        $currentSalary = SalaryGrade::getRate($plantilla->salary_grade, $currentStep);

        // Calculate NEW salary based on NOSI (+1 step)
        $newStep = min(8, $currentStep + 1);
        $newSalary = SalaryGrade::getRate($plantilla->salary_grade, $newStep);
        $diff = $newSalary - $currentSalary;

        // Base date for effective date calculation
        $appointmentDate = $plantilla->date_last_promotion ?? $plantilla->date_original_appointment;
        $effectiveDate = $appointmentDate ? $appointmentDate->clone()->addYears(3) : now();

        $data = [
            'type' => 'NOSI',
            'employee' => $plantilla,
            'currentSalary' => $currentSalary,
            'currentStep' => $currentStep,
            'newStep' => $newStep,
            'newSalary' => $newSalary,
            'diff' => $diff,
            'effectiveDate' => $effectiveDate,
        ];

        $pdf = Pdf::loadView('step-increment.pdf.nosi', $data)->setPaper('a4', 'portrait');

        // Ensure A4 size for NOSI
        if ($request->has('download')) {
            return $pdf->download('NOSI_' . \Str::slug($plantilla->full_name) . '.pdf');
        }
        return $pdf->stream('NOSI_' . \Str::slug($plantilla->full_name) . '.pdf');
    }

    /**
     * Generate Long/Legal PDF for Notice of Longevity Pay (NOLP)
     */
    public function generateNolp(Request $request, PlantillaRecord $plantilla)
    {
        if ($plantilla->is_vacant) {
            return back()->with('error', 'Cannot generate NOLP for a vacant position.');
        }

        if (!$plantilla->is_hospital_personnel) {
            return back()->with('error', 'NOLP is only applicable for Hospital/Medical personnel.');
        }

        $currentStep = $plantilla->step ?: 1;
        $currentSalary = SalaryGrade::getRate($plantilla->salary_grade, $currentStep);

        // NOLP goes up by 2 steps
        $newStep = min(8, $currentStep + 2);
        $newSalary = SalaryGrade::getRate($plantilla->salary_grade, $newStep);
        $diff = $newSalary - $currentSalary;

        // Base date for effective date calculation
        $appointmentDate = $plantilla->date_last_nolp ?? $plantilla->date_original_appointment;
        $effectiveDate = $appointmentDate ? $appointmentDate->clone()->addYears(5) : now();

        $data = [
            'type' => 'NOLP',
            'employee' => $plantilla,
            'currentSalary' => $currentSalary,
            'currentStep' => $currentStep,
            'newStep' => $newStep,
            'newSalary' => $newSalary,
            'diff' => $diff,
            'effectiveDate' => $effectiveDate,
        ];

        $pdf = Pdf::loadView('step-increment.pdf.nolp', $data)->setPaper('a4', 'portrait');

        // Ensure A4 size for NOLP
        if ($request->has('download')) {
            return $pdf->download('NOLP_' . \Str::slug($plantilla->full_name) . '.pdf');
        }
        return $pdf->stream('NOLP_' . \Str::slug($plantilla->full_name) . '.pdf');
    }

    /**
     * Generate A4 PDF for Notice of Salary Adjustment (NOSA)
     */
    public function generateNosa(Request $request, PlantillaRecord $plantilla)
    {
        if ($plantilla->is_vacant) {
            return back()->with('error', 'Cannot generate NOSA for a vacant position.');
        }

        $schedule = \App\Models\SalarySchedule::getActive();

        $newSalary      = \App\Models\SalaryGrade::getRate($plantilla->salary_grade, $plantilla->step ?: 1);
        $previousSalary = $plantilla->base_salary_amount
            ? (float) $plantilla->base_salary_amount
            : $newSalary;

        if ((abs($previousSalary - $newSalary) < 1 || $previousSalary > $newSalary) && $plantilla->previous_rate > 0 && $plantilla->previous_rate < $newSalary) {
            $previousSalary = (float) $plantilla->previous_rate;
        }

        // Effective date: prefer schedule date, then today
        $effectiveDate = ($schedule && $schedule->effective_date)
            ? $schedule->effective_date
            : now();

        $data = [
            'type'           => 'NOSA',
            'employee'       => $plantilla,
            'schedule'       => $schedule,
            'newSalary'      => $newSalary,
            'previousSalary' => $previousSalary,
            'effectiveDate'  => $effectiveDate,
        ];

        $pdf = Pdf::loadView('step-increment.pdf.nosa', $data)->setPaper('a4', 'portrait');

        if ($request->has('download')) {
            return $pdf->download('NOSA_' . \Str::slug($plantilla->full_name) . '.pdf');
        }
        return $pdf->stream('NOSA_' . \Str::slug($plantilla->full_name) . '.pdf');
    }

    /**
     * Generate an A4 Landscape PDF for the Loyalty Incentive Certificate (two pages).
     */
    public function generateLoyaltyIncentive(Request $request, PlantillaRecord $plantilla)
    {
        if ($plantilla->is_vacant) {
            return back()->with('error', 'Cannot generate Loyalty Incentive Certificate for a vacant position.');
        }

        if (!$plantilla->date_original_appointment) {
            return back()->with('error', 'No original appointment date recorded for this employee.');
        }

        $startDate   = $plantilla->date_original_appointment;
        $yearsOfService = (int) $startDate->diffInYears(now());

        // Business rule computation
        // First 10 full years unlock ₱10,000; each subsequent 5-year block = ₱5,000
        if ($yearsOfService < 5) {
            // Not yet eligible — still show the certificate but with 0 amt
            $amount      = 0;
            $milestone   = 0;
        } elseif ($yearsOfService >= 5 && $yearsOfService < 10) {
            $amount      = 5000;
            $milestone   = 5;
        } elseif ($yearsOfService === 10) {
            $amount      = 10000;
            $milestone   = 10;
        } else {
            // After 10 years: determine milestone block (every 5 years from 10 onward)
            $afterTen = $yearsOfService - 10;
            $blocks   = (int) floor($afterTen / 5); // how many 5-yr blocks completed
            $amount   = ($blocks > 0) ? 5000 : 10000;
            $milestone = 10 + ($blocks * 5);
        }

        // Date range: from the milestone anniversary date backwards
        $toDate   = $startDate->copy()->addYears($milestone);   // e.g. Feb 16, 2026
        $fromDate = $toDate->copy()->subYears($milestone === 10 ? 10 : 5); // from date

        // Human-readable amount
        $amountWords = $this->pesoToWords($amount);

        $data = [
            'employee'       => $plantilla,
            'startDate'      => $startDate,
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
            'yearsOfService' => $milestone ?: $yearsOfService,
            'amount'         => $amount,
            'amountWords'    => $amountWords,
            'generatedMonth' => now()->format('F'),
            'generatedYear'  => now()->year,
            'backgroundPath' => \App\Http\Controllers\LoyaltyIncentiveSettingController::getActiveBackgroundPath(),
        ];

        $pdf = Pdf::loadView('step-increment.pdf.loyalty-incentive', $data)
            ->setPaper('a4', 'landscape');

        if ($request->has('download')) {
            return $pdf->download('LoyaltyIncentive_' . \Str::slug($plantilla->full_name) . '.pdf');
        }
        return $pdf->stream('LoyaltyIncentive_' . \Str::slug($plantilla->full_name) . '.pdf');
    }

    /**
     * Convert a peso amount to Filipino words representation.
     */
    private function pesoToWords(int $amount): string
    {
        $map = [
            5000  => 'Five thousand pesos (5,000.00)',
            10000 => 'Ten thousand pesos (10,000.00)',
            0     => 'Not yet eligible',
        ];
        return $map[$amount] ?? number_format($amount, 2) . ' pesos';
    }

    public function generateNosiBulkDocx(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $month = $request->query('month', now()->format('Y-m'));
        try {
            $targetDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $targetDate = now()->startOfMonth();
        }

        $allDue = PlantillaRecord::stepDue()
            ->where('is_vacant', false)
            ->orderBy('office_department')
            ->orderBy('salary_grade')
            ->get();

        $employees = $allDue->filter(function($r) use ($targetDate) {
            $due = $r->next_step_due_date;
            return !$r->is_hospital_personnel && 
                   $due && 
                   $due->year === $targetDate->year && 
                   $due->month === $targetDate->month;
        });

        if ($employees->isEmpty()) {
            return back()->with('error', 'No NOSI records found for this month.');
        }

        $records = [];
        foreach ($employees as $plantilla) {
            $currentStep = $plantilla->step ?: 1;
            $currentSalary = SalaryGrade::getRate($plantilla->salary_grade, $currentStep);
            $newStep = min(8, $currentStep + 1);
            $newSalary = SalaryGrade::getRate($plantilla->salary_grade, $newStep);
            $diff = $newSalary - $currentSalary;
            
            $appointmentDate = $plantilla->date_last_promotion ?? $plantilla->date_original_appointment;
            $effectiveDate = $appointmentDate ? $appointmentDate->clone()->addYears(3) : now();

            $records[] = [
                'employee' => $plantilla,
                'currentSalary' => $currentSalary,
                'currentStep' => $currentStep,
                'newStep' => $newStep,
                'newSalary' => $newSalary,
                'diff' => $diff,
                'effectiveDate' => $effectiveDate,
            ];
        }

        $phpWord = \App\Services\DocxGenerator::generateNosi(['records' => $records], true);
        return \App\Services\DocxGenerator::download($phpWord, 'NOSI_Bulk_' . $targetDate->format('M_Y') . '.docx');
    }

    public function generateNolpBulkDocx(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $month = $request->query('month', now()->format('Y-m'));
        try {
            $targetDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $targetDate = now()->startOfMonth();
        }

        $allDue = PlantillaRecord::stepDue()
            ->where('is_vacant', false)
            ->orderBy('office_department')
            ->orderBy('salary_grade')
            ->get();

        $employees = $allDue->filter(function($r) use ($targetDate) {
            $due = $r->next_step_due_date;
            return $r->is_hospital_personnel && 
                   $due && 
                   $due->year === $targetDate->year && 
                   $due->month === $targetDate->month;
        });

        if ($employees->isEmpty()) {
            return back()->with('error', 'No NOLP records found for this month.');
        }

        $records = [];
        foreach ($employees as $plantilla) {
            $currentStep = $plantilla->step ?: 1;
            $currentSalary = SalaryGrade::getRate($plantilla->salary_grade, $currentStep);
            $newStep = min(8, $currentStep + 2);
            $newSalary = SalaryGrade::getRate($plantilla->salary_grade, $newStep);
            $diff = $newSalary - $currentSalary;
            
            $appointmentDate = $plantilla->date_last_nolp ?? $plantilla->date_original_appointment;
            $effectiveDate = $appointmentDate ? $appointmentDate->clone()->addYears(5) : now();

            $records[] = [
                'employee' => $plantilla,
                'currentSalary' => $currentSalary,
                'currentStep' => $currentStep,
                'newStep' => $newStep,
                'newSalary' => $newSalary,
                'diff' => $diff,
                'effectiveDate' => $effectiveDate,
            ];
        }

        $phpWord = \App\Services\DocxGenerator::generateNolp(['records' => $records], true);
        return \App\Services\DocxGenerator::download($phpWord, 'NOLP_Bulk_' . $targetDate->format('M_Y') . '.docx');
    }

    public function generateNosiDocx(PlantillaRecord $plantilla)
    {
        if ($plantilla->is_vacant) {
            return back()->with('error', 'Cannot generate NOSI for a vacant position.');
        }

        $currentStep = $plantilla->step ?: 1;
        $currentSalary = SalaryGrade::getRate($plantilla->salary_grade, $currentStep);
        $newStep = min(8, $currentStep + 1);
        $newSalary = SalaryGrade::getRate($plantilla->salary_grade, $newStep);
        $diff = $newSalary - $currentSalary;
        $appointmentDate = $plantilla->date_last_promotion ?? $plantilla->date_original_appointment;
        $effectiveDate = $appointmentDate ? $appointmentDate->clone()->addYears(3) : now();

        $data = [
            'employee' => $plantilla,
            'currentSalary' => $currentSalary,
            'currentStep' => $currentStep,
            'newStep' => $newStep,
            'newSalary' => $newSalary,
            'diff' => $diff,
            'effectiveDate' => $effectiveDate,
        ];

        $phpWord = \App\Services\DocxGenerator::generateNosi($data);
        return \App\Services\DocxGenerator::download($phpWord, 'NOSI_' . \Str::slug($plantilla->full_name) . '.docx');
    }

    public function generateNolpDocx(PlantillaRecord $plantilla)
    {
        if ($plantilla->is_vacant) {
            return back()->with('error', 'Cannot generate NOLP for a vacant position.');
        }

        if (!$plantilla->is_hospital_personnel) {
            return back()->with('error', 'NOLP is only applicable for Hospital/Medical personnel.');
        }

        $currentStep = $plantilla->step ?: 1;
        $currentSalary = SalaryGrade::getRate($plantilla->salary_grade, $currentStep);
        $newStep = min(8, $currentStep + 2);
        $newSalary = SalaryGrade::getRate($plantilla->salary_grade, $newStep);
        $diff = $newSalary - $currentSalary;
        $appointmentDate = $plantilla->date_last_nolp ?? $plantilla->date_original_appointment;
        $effectiveDate = $appointmentDate ? $appointmentDate->clone()->addYears(5) : now();

        $data = [
            'employee' => $plantilla,
            'currentSalary' => $currentSalary,
            'currentStep' => $currentStep,
            'newStep' => $newStep,
            'newSalary' => $newSalary,
            'diff' => $diff,
            'effectiveDate' => $effectiveDate,
        ];

        $phpWord = \App\Services\DocxGenerator::generateNolp($data);
        return \App\Services\DocxGenerator::download($phpWord, 'NOLP_' . \Str::slug($plantilla->full_name) . '.docx');
    }
}
