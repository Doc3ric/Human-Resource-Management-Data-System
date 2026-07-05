<?php

namespace App\Console\Commands;

use App\Models\PlantillaRecord;
use Illuminate\Console\Command;

class ProcessRetirements extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'retirement:process {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Automatically mark plantilla positions as vacant for employees who have reached retirement age (65).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $retirementAge = 65;
        $cutoffDate    = now()->subYears($retirementAge)->toDateString();
        $isDryRun      = $this->option('dry-run');

        $records = PlantillaRecord::query()
            ->where('is_vacant', false)
            ->where('abolished', false)
            ->whereNotNull('date_of_birth')
            ->whereNull('retired_at')
            ->where('date_of_birth', '<=', $cutoffDate)
            ->get();

        if ($records->isEmpty()) {
            $this->info('✅  No employees are currently overdue for compulsory retirement.');
            return Command::SUCCESS;
        }

        $this->info("Found {$records->count()} employee(s) overdue for retirement (age ≥ {$retirementAge}).");

        if ($isDryRun) {
            $this->warn('DRY RUN mode — no changes will be made.');
        }

        $retirementDate = now()->toDateString();
        $count = 0;

        $this->table(
            ['#', 'Item', 'Name', 'DOB', 'Age', 'Position', 'Office'],
            $records->map(fn ($r, $i) => [
                $i + 1,
                $r->item_no_new,
                strtoupper($r->last_name ?? '') . ', ' . ($r->first_name ?? ''),
                $r->date_of_birth?->format('m/d/Y'),
                $r->age,
                $r->position_title,
                \Str::limit($r->office_department, 30),
            ])->toArray()
        );

        if ($isDryRun) {
            $this->warn("Dry run complete. {$records->count()} record(s) would be processed.");
            return Command::SUCCESS;
        }

        foreach ($records as $plantilla) {
            $formerName = trim(
                strtoupper($plantilla->last_name ?? '') . ', ' .
                ($plantilla->first_name ?? '') . ' ' .
                ($plantilla->middle_name ?? '')
            );

            $annotation = "COMPULSORY RETIREMENT effective {$retirementDate}. "
                        . "Former employee: {$formerName}. "
                        . "DOB: " . ($plantilla->date_of_birth?->format('m/d/Y') ?? 'N/A') . ". "
                        . "SG-{$plantilla->salary_grade} Step {$plantilla->step}. "
                        . "TIN: " . ($plantilla->tin ?? 'N/A') . '.';

            $existing       = $plantilla->remarks_annotation;
            $fullAnnotation = $existing ? $existing . "\n\n" . $annotation : $annotation;

            $plantilla->update([
                'is_vacant'          => true,
                'retired_at'         => $retirementDate,
                'last_name'          => null,
                'first_name'         => null,
                'middle_name'        => null,
                'sex'                => null,
                'date_of_birth'      => null,
                'tin'                => null,
                'gsis_bp_number'     => null,
                'umid'               => null,
                'remarks_annotation' => $fullAnnotation,
            ]);

            $count++;
            $this->line("  ✓ Retired: {$formerName} ({$plantilla->item_no_new})");
        }

        $this->newLine();
        $this->info("✅  Processed {$count} retirement(s). Positions marked as vacant.");

        return Command::SUCCESS;
    }
}
