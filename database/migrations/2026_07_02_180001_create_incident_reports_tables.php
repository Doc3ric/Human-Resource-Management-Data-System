<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Module 3A — Incident Report with AI/rules-assisted advisory (new domain). */
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->dateTime('incident_datetime');
            $table->date('reported_at');
            $table->string('location')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_name')->nullable();
            $table->unsignedBigInteger('personnel_id')->nullable(); // person involved
            $table->text('witnesses')->nullable();
            $table->string('category', 30); // attendance|conduct|performance|safety|property|interpersonal|other
            $table->text('narrative');
            $table->text('immediate_action_taken')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();

            // Advisory draft (Module 3A.2) — clearly labeled, never a finding.
            $table->json('advisory_citations')->nullable();
            $table->text('advisory_penalty_range')->nullable();
            $table->text('advisory_recommendation')->nullable();
            $table->timestamp('advisory_generated_at')->nullable();

            // Human review & finalization (Module 3A.3/3A.4)
            $table->string('status', 20)->default('draft'); // draft|advisory_ready|under_review|finalized
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('final_citations')->nullable();
            $table->text('review_notes')->nullable();
            $table->string('final_track', 20)->nullable(); // counseling|escalated
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();
        });

        Schema::create('counseling_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_report_id')->constrained('incident_reports')->cascadeOnDelete();
            $table->text('recommendation');
            $table->text('action_plan')->nullable();
            $table->date('target_date')->nullable();
            $table->json('session_log')->nullable(); // [{date, attendees, notes}]
            $table->date('follow_up_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_records');
        Schema::dropIfExists('incident_reports');
    }
};
