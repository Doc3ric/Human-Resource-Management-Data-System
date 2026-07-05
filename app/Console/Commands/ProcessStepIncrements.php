<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlantillaRecord;
use App\Models\SalaryGrade;
use App\Models\StepIncrementHistory;

class ProcessStepIncrements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'step-increments:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically process all due step increments and longevity pay';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automated step increment processing...');

        // Get all due step increments (where next_step_due_date is today or earlier)
        // Note: The scope stepDue() internally calculates if it's due based on timers, 
        // but we'll also verify next_step_due_date <= now() to ensure we only process 
        // those who are actually due *now* (including overdue).
        
        $allDue = PlantillaRecord::stepDue()
            ->where('step', '<', 8)
            ->get();

        $dueRecords = $allDue->filter(function($r) {
            return $r->next_step_due_date && $r->next_step_due_date->lte(now());
        });

        if ($dueRecords->isEmpty()) {
            $this->info('No eligible employees found for step increment processing today.');
            return;
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
            $this->info("Processed {$logType} for {$plantilla->full_name}. New step: {$newStep}.");
        }

        $this->info("Completed. Processed {$count} step increments.");
    }
}
