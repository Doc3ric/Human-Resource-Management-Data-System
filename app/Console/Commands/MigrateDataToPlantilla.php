<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobOrder;
use App\Models\CasualEmployee;
use App\Models\PlantillaRecord;
use Illuminate\Support\Facades\DB;

class MigrateDataToPlantilla extends Command
{
    protected $signature = 'data:migrate-to-plantilla';
    protected $description = 'Migrates Job Orders and Casual Employees to PlantillaRecords table.';

    public function handle()
    {
        $this->info('Starting migration...');
        
        DB::beginTransaction();
        try {
            // Migrate Job Orders
            $this->info('Migrating Job Orders...');
            $jobOrders = JobOrder::withTrashed()->get();
            foreach ($jobOrders as $jo) {
                // Find existing plantilla record or create new
                $pr = PlantillaRecord::withTrashed()->where('employee_code', $jo->employee_code)->first();
                if (!$pr) {
                    $pr = new PlantillaRecord();
                    $pr->employee_code = $jo->employee_code;
                }
                
                $pr->employment_status = 'JO';
                $pr->office_department = $jo->office;
                $pr->office_department = $jo->office_department;
                $pr->last_name = $jo->last_name;
                $pr->first_name = $jo->first_name;
                $pr->middle_name = $jo->middle_initial; // Map middle initial to middle name
                $pr->name_extension = $jo->name_extension;
                $pr->position_title = $jo->position_title;
                $pr->nature_of_work = $jo->nature_of_work;
                $pr->nature_of_work_detail = $jo->nature_of_work_detail;
                $pr->rate_per_day = $jo->rate_per_day;
                $pr->first_day_of_service = $jo->first_day_of_service;
                $pr->date_of_birth = $jo->date_of_birth;
                $pr->civil_status = $jo->civil_status;
                $pr->address = $jo->address;
                $pr->civil_service_eligibility = $jo->eligibility;
                $pr->sex = $jo->sex;
                $pr->level = \Illuminate\Support\Str::limit($jo->level, 5, '');
                $pr->first_level_eligibility = $jo->first_level_eligibility;
                $pr->second_level_eligibility = $jo->second_level_eligibility;
                $pr->indigenous_people = $jo->ip_community_membership;
                $pr->solo_parent = $jo->solo_parent ? 'Yes' : null;
                $pr->reemployment = $jo->reemployment;
                $pr->remarks = $jo->remarks;
                $pr->profile_picture = $jo->profile_picture;
                $pr->deleted_at = $jo->deleted_at;
                
                $pr->save();

                // Update attachments
                DB::table('employee_attachments')
                    ->where('job_order_id', $jo->id)
                    ->update([
                        'plantilla_record_id' => $pr->id,
                        'job_order_id' => null
                    ]);
            }

            // Migrate Casual Employees
            $this->info('Migrating Casual Employees...');
            $casuals = CasualEmployee::withTrashed()->get();
            foreach ($casuals as $cas) {
                $pr = PlantillaRecord::withTrashed()->where('employee_code', $cas->employee_code)->first();
                if (!$pr) {
                    $pr = new PlantillaRecord();
                    $pr->employee_code = $cas->employee_code;
                }

                $pr->employment_status = 'Casual';
                $pr->office_department = $cas->office;
                $pr->item_no_new_no_old = $cas->item_no_new_no_old;
                $pr->item_no_new = $cas->item_no_new_no_new;
                $pr->position_title = $cas->position_title;
                $pr->is_vacant = $cas->is_vacant;
                $pr->last_name = $cas->last_name;
                $pr->first_name = $cas->first_name;
                $pr->middle_name = $cas->middle_initial; // Map middle initial to middle name
                $pr->name_extension = $cas->name_extension;
                $pr->legislative_district = $cas->legislative_district;
                $pr->salary_grade = $cas->sg_current;
                $pr->step = $cas->step_current;
                $pr->base_salary_amount = $cas->salary_current ? $cas->salary_current * 12 : null;
                $pr->authorized_annual_salary = $cas->salary_current ? $cas->salary_current * 12 : null;
                $pr->sg_proposed = $cas->sg_proposed;
                $pr->step_proposed = $cas->step_proposed;
                $pr->salary_proposed = $cas->salary_proposed;
                $pr->increase_decrease = $cas->increase_decrease;
                $pr->previous_rate = $cas->previous_rate;
                $pr->current_rate = $cas->current_rate;
                $pr->sex = $cas->sex;
                $pr->date_of_birth = $cas->date_of_birth;
                $pr->first_day_of_service = $cas->first_day_of_service;
                $pr->civil_service_eligibility = $cas->eligibility;
                $pr->remarks_annotation = $cas->annotation;
                $pr->address = $cas->address;
                $pr->solo_parent = $cas->solo_parent ? 'Yes' : null;
                $pr->indigenous_people = $cas->ip_community_membership;
                $pr->profile_picture = $cas->profile_picture;
                $pr->deleted_at = $cas->deleted_at;

                $pr->save();

                // Update attachments
                DB::table('employee_attachments')
                    ->where('casual_employee_id', $cas->id)
                    ->update([
                        'plantilla_record_id' => $pr->id,
                        'casual_employee_id' => null
                    ]);
            }

            DB::commit();
            $this->info('Migration complete!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Migration failed: ' . $e->getMessage());
        }
    }
}


