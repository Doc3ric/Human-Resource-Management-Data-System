<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add salary_schedule_id FK to salary_grades.
     * Existing rows (legacy baseline) keep salary_schedule_id = NULL.
     * Drop the old unique(grade,step) and replace with unique(salary_schedule_id,grade,step).
     */
    public function up(): void
    {
        Schema::table('salary_grades', function (Blueprint $table) {
            $table->foreignId('salary_schedule_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('salary_schedules')
                  ->nullOnDelete();

            // Remove old unique constraint (grade, step) so we can have
            // the same (grade, step) pair appear in multiple schedules.
            $table->dropUnique(['grade', 'step']);

            // New unique: one rate per (schedule, grade, step)
            // NULL schedule_id rows (legacy) are exempt from uniqueness by DB standards.
            $table->unique(['salary_schedule_id', 'grade', 'step'], 'sg_schedule_grade_step_unique');

            $table->index('salary_schedule_id', 'sg_schedule_idx');
        });
    }

    public function down(): void
    {
        Schema::table('salary_grades', function (Blueprint $table) {
            $table->dropIndex('sg_schedule_idx');
            $table->dropUnique('sg_schedule_grade_step_unique');
            $table->dropConstrainedForeignId('salary_schedule_id');
            $table->unique(['grade', 'step']);
        });
    }
};
