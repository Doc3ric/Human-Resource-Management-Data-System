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
        Schema::create('step_increment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_record_id')->constrained('plantilla_records')->onDelete('cascade');
            $table->enum('type', ['NOSI', 'NOLP', 'NOSA']);
            $table->integer('previous_step')->nullable();
            $table->integer('new_step')->nullable();
            $table->integer('previous_salary_grade')->nullable();
            $table->integer('new_salary_grade')->nullable();
            $table->decimal('previous_annual_salary', 15, 2)->nullable();
            $table->decimal('new_annual_salary', 15, 2)->nullable();
            $table->date('effective_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_increment_histories');
    }
};
