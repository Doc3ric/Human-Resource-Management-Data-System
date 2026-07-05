<?php

namespace App\Console\Commands;

use App\Models\PlantillaRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSalaries extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'salary:sync {--dry-run : Only show how many records would be updated}';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize plantilla records actual/authorized salaries with the current salary grades table.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting salary synchronization...');
        
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN mode — no database changes will be made.');
        }

        // We bypass Eloquent events for absolute maximum performance on 4000+ rows
        $query = "
            UPDATE plantilla_records pr
            INNER JOIN salary_grades sg 
                ON pr.salary_grade = sg.grade AND pr.step = sg.step
            SET 
                pr.base_salary_amount = (sg.monthly_salary * 12),
                pr.authorized_annual_salary = (sg.monthly_salary * 12),
                pr.updated_at = NOW()
            WHERE 
                pr.base_salary_amount != (sg.monthly_salary * 12)
                OR pr.authorized_annual_salary != (sg.monthly_salary * 12)
        ";

        if ($isDryRun) {
            // Count how many would be affected
            $countQuery = "
                SELECT COUNT(*) as affected
                FROM plantilla_records pr
                INNER JOIN salary_grades sg 
                    ON pr.salary_grade = sg.grade AND pr.step = sg.step
                WHERE 
                    pr.base_salary_amount != (sg.monthly_salary * 12)
                    OR pr.authorized_annual_salary != (sg.monthly_salary * 12)
            ";
            
            $affected = DB::selectOne($countQuery)->affected ?? 0;
            $this->info("Dry run complete. {$affected} record(s) have outdated salaries and would be updated.");
            return Command::SUCCESS;
        }

        $affected = DB::update($query);

        $this->info("✅ Salary synchronization complete! Updated {$affected} record(s).");
        return Command::SUCCESS;
    }
}
