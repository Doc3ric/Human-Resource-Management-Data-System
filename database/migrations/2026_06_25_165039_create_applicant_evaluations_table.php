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
        Schema::create('applicant_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->enum('qs_requirement', ['Met', 'Unmet'])->nullable();
            $table->enum('exam_status', ['Passed', 'Failed', 'Absent'])->nullable();
            $table->enum('docs_complete', ['Yes', 'No'])->nullable();
            $table->enum('final_rating', ['Qualified', 'Disqualified'])->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_evaluations');
    }
};
