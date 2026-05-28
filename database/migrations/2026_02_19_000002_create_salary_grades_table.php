<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Stores the Philippine Salary Standardization Law (SSL) rates.
     * Salary Grade 1-33, Steps 1-8.
     */
    public function up(): void
    {
        Schema::create('salary_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('grade');   // 1–33
            $table->unsignedTinyInteger('step');    // 1–8
            $table->decimal('monthly_salary', 10, 2);
            $table->timestamps();

            $table->unique(['grade', 'step']);
            $table->index('grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_grades');
    }
};
