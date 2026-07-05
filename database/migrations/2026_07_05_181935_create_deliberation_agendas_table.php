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
        Schema::create('deliberation_agendas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('part_1_notes')->nullable();
            $table->json('matrix_data')->nullable(); // Stores the offices -> vacancies -> applicants and their board resolutions
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliberation_agendas');
    }
};
