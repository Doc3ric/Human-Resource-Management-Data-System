<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 2B — Leave Violation Monitoring. Scoped to what this app can
     * actually detect from leave data (no DTR/attendance minute-level data
     * source exists for tardiness/undertime — see [[m2b-m3a-m10b-hr-ops]]).
     */
    public function up(): void
    {
        Schema::create('leave_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_record_id')->constrained('plantilla_records')->cascadeOnDelete();
            $table->string('violation_type', 30); // AWOL|HABITUAL_ABSENTEEISM|LWOP|HABITUAL_TARDINESS(manual only)
            $table->unsignedTinyInteger('offense_tier')->default(1);
            $table->string('rating_period');
            $table->json('details'); // computed figures backing the flag (dates, counts, days)
            $table->string('status', 20)->default('detected'); // detected|notice_pending|issued|responded|resolved|escalated
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete(); // issued notice letter
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });

        Schema::create('leave_violation_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('violation_type', 30)->unique();
            $table->json('config'); // e.g. {"days_threshold": 30, "months_window": 3}
            $table->text('legal_basis')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_violation_thresholds');
        Schema::dropIfExists('leave_violations');
    }
};
