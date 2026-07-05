<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applicant_hrmpsb_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->enum('position_category', ['eligibility', 'no_eligibility']);
            
            // Core Criteria
            $table->decimal('psb_interview_score', 5, 2)->nullable(); 
            $table->decimal('ipcr_score', 5, 2)->nullable(); 
            $table->decimal('awards_score', 5, 2)->nullable(); 
            $table->decimal('education_score', 5, 2)->nullable(); 
            
            // Conditional Criteria
            $table->decimal('experience_score', 5, 2)->nullable(); 
            $table->decimal('training_score', 5, 2)->nullable(); 
            $table->decimal('length_of_service_score', 5, 2)->nullable(); 
            
            // Deductions & Total
            $table->decimal('demerits_deduction', 5, 2)->default(0); 
            $table->decimal('total_score', 5, 2)->nullable(); 
            
            // Meta
            $table->text('remarks')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_hrmpsb_scores');
    }
};
