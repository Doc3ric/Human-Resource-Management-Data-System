<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 6.1 — a dynamic, admin-editable TWG criteria library, additive
     * alongside the existing fixed-column applicant_hrmpsb_scores/
     * interview_evaluations tables (Module 4/6/7 keep working unchanged).
     * There is no Vacancy entity in this app — item_no is the closest
     * existing proxy, consistent with how the rest of the app treats
     * "vacancy" implicitly (see PlantillaSyncValidator, RecruitmentController).
     */
    public function up(): void
    {
        Schema::create('twg_rating_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('criterion_key')->unique(); // e.g. 'psb_interview', 'ipcr', 'education_eligibility'
            $table->string('criterion_name');
            $table->string('criterion_category', 30)->default('all'); // eligibility|no_eligibility|all
            $table->text('score_basis_description')->nullable();
            $table->decimal('point_value', 5, 2); // maximum points for this criterion
            $table->string('rating_scale_key')->nullable(); // maps to hrmpsb_rating_scales.criterion, if bracket-based
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('twg_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->string('item_no')->nullable(); // vacancy proxy, snapshotted at scoring time
            $table->foreignId('criterion_id')->constrained('twg_rating_criteria')->cascadeOnDelete();
            $table->decimal('auto_populated_value', 5, 2)->nullable();
            $table->decimal('assessor_value', 5, 2)->nullable();
            $table->boolean('is_edited')->default(false);
            $table->text('assessor_notes')->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();

            $table->unique(['applicant_id', 'criterion_id']);
        });

        Schema::create('twg_score_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->decimal('total_auto', 6, 2)->default(0);
            $table->decimal('total_assessor', 6, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->string('adjectival_classification', 30)->nullable();
            $table->string('recommendation', 40)->nullable(); // Highly Recommended|Recommended|Recommended with Reservation|Not Recommended
            $table->text('overall_notes')->nullable();
            $table->boolean('certification_accepted')->default(false);
            $table->string('status', 20)->default('draft'); // draft|submitted
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('unlocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->unique('applicant_id');
        });

        Schema::create('twg_score_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->string('action', 30); // submitted|unlocked|edited
            $table->decimal('total_score', 6, 2)->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('twg_score_history');
        Schema::dropIfExists('twg_score_submissions');
        Schema::dropIfExists('twg_scores');
        Schema::dropIfExists('twg_rating_criteria');
    }
};
