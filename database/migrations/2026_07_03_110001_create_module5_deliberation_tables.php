<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 5 — split-screen deliberation workspace + Module 5.4 exam
     * routing. Both new columns are named explicitly in the master spec's
     * Step 1 nullable-column list (deliberation_phase generalizes what the
     * spec calls the workspace's phase badge; is_exam_exempt is named
     * verbatim). Additive only — nothing existing altered.
     */
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('deliberation_phase', 30)->nullable()->default('screening')->after('acknowledged');
            $table->boolean('is_exam_exempt')->nullable()->default(false)->after('deliberation_phase');
        });

        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->string('classification', 20); // pgb_jo|external|exempt
            $table->unsignedInteger('table_number')->nullable();
            $table->date('exam_date')->nullable();
            $table->time('exam_time')->nullable();
            $table->string('room')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['deliberation_phase', 'is_exam_exempt']);
        });
    }
};
