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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('restrict');
            $table->foreignId('position_id')->constrained('positions')->onDelete('restrict');
            $table->date('appointment_start');
            $table->date('appointment_end')->nullable();
            $table->string('appointment_type')->nullable(); // Regular, Temporary, etc.
            $table->text('remarks')->nullable();
            $table->enum('status', ['Active', 'Ended'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
