<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_evaluation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->string('result', 20); // 'ADMITTED' or 'SCREENED_OUT'
            $table->text('remarks')->nullable();
            $table->foreignId('evaluated_by')->constrained('users');
            $table->timestamp('evaluated_at')->useCurrent();
            $table->boolean('is_locked')->default(false);
            $table->foreignId('reopened_by')->nullable()->constrained('users');
            $table->timestamp('reopened_at')->nullable();
            $table->timestamps();
            
            $table->unique('applicant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_evaluation_results');
    }
};
