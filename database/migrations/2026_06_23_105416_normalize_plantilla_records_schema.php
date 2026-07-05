<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add new columns
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->string('detailed_unit')->nullable()->after('organizational_unit');
            $table->text('remarks_annotation')->nullable();
            $table->decimal('base_salary_amount', 12, 2)->nullable();
            $table->enum('salary_type', ['Annual', 'Daily', 'Monthly'])->nullable();
        });

        // 2. Safely migrate existing data using raw SQL (MySQL-specific — e.g.
        // CONCAT_WS — and moot on a fresh test DB with no rows yet anyway).
        if (DB::connection()->getDriverName() === 'mysql') {
        // A. Office & Detailed Unit Data Swap
        // Regulars/Casuals: organizational_unit remains Office. No detailed_unit needed initially.
        // Job Orders: charges -> office_department, organizational_unit -> detailed_unit
        DB::statement("UPDATE plantilla_records SET detailed_unit = organizational_unit WHERE employment_status IN ('JO', 'Job Order', 'J.O.', 'J')");
        DB::statement("UPDATE plantilla_records SET organizational_unit = charges WHERE employment_status IN ('JO', 'Job Order', 'J.O.', 'J')");

        // B. Remarks & Annotations Merge
        DB::statement("UPDATE plantilla_records SET remarks_annotation = CONCAT_WS(' | ', NULLIF(comment_annotation, ''), NULLIF(remarks, ''))");

        // C. Salary Merge
        DB::statement("UPDATE plantilla_records SET base_salary_amount = actual_annual_salary, salary_type = 'Annual' WHERE employment_status IN ('P', 'E', 'CT')");
        DB::statement("UPDATE plantilla_records SET base_salary_amount = current_rate, salary_type = 'Monthly' WHERE employment_status IN ('Casual', 'Cas', 'C')");
        DB::statement("UPDATE plantilla_records SET base_salary_amount = rate_per_day, salary_type = 'Daily' WHERE employment_status IN ('JO', 'Job Order', 'J.O.', 'J')");
        }

        // 3. Rename existing columns and drop obsolete ones
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->renameColumn('organizational_unit', 'office_department');
            $table->renameColumn('item', 'item_no_new');
            
            // Drop merged/obsolete columns
            $table->dropColumn([
                'charges',
                'comment_annotation',
                'remarks',
                'actual_annual_salary',
                'current_rate',
                'rate_per_day'
            ]);
        });

        // 4. Drop obsolete legacy tables and their foreign keys
        Schema::table('employee_attachments', function (Blueprint $table) {
            $table->dropForeign(['job_order_id']);
            $table->dropForeign(['casual_employee_id']);
            $table->dropColumn(['job_order_id', 'casual_employee_id']);
        });

        Schema::dropIfExists('job_orders');
        Schema::dropIfExists('casual_employees');
    }

    public function down(): void
    {
        // Reversal logic
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->renameColumn('office_department', 'organizational_unit');
            $table->renameColumn('item_no_new', 'item');
            
            $table->string('charges')->nullable();
            $table->text('comment_annotation')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('actual_annual_salary', 12, 2)->nullable();
            $table->decimal('current_rate', 10, 2)->nullable();
            $table->decimal('rate_per_day', 10, 2)->nullable();
        });

        DB::statement("UPDATE plantilla_records SET charges = organizational_unit WHERE employment_status IN ('JO', 'Job Order', 'J.O.', 'J')");
        DB::statement("UPDATE plantilla_records SET organizational_unit = detailed_unit WHERE employment_status IN ('JO', 'Job Order', 'J.O.', 'J')");
        
        DB::statement("UPDATE plantilla_records SET comment_annotation = remarks_annotation, remarks = remarks_annotation");

        DB::statement("UPDATE plantilla_records SET actual_annual_salary = base_salary_amount WHERE salary_type = 'Annual'");
        DB::statement("UPDATE plantilla_records SET current_rate = base_salary_amount WHERE salary_type = 'Monthly'");
        DB::statement("UPDATE plantilla_records SET rate_per_day = base_salary_amount WHERE salary_type = 'Daily'");

        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropColumn(['detailed_unit', 'remarks_annotation', 'base_salary_amount', 'salary_type']);
        });
    }
};
