<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hrmpsb_interview_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('hrmpsb_interview_criteria')->cascadeOnDelete();
            $table->foreignId('member_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->text('remarks')->nullable();
            $table->timestamp('scored_at')->useCurrent();
            $table->boolean('is_consolidated')->default(false);
            $table->foreignId('consolidated_by')->nullable()->constrained('users');
            $table->timestamp('consolidated_at')->nullable();
            $table->timestamps();

            $table->unique(['applicant_id', 'criterion_id', 'member_user_id'], 'uq_interview_score_row');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hrmpsb_interview_scores');
    }
};
