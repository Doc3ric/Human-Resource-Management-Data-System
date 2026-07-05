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
        Schema::create('hrmpsb_rating_scales', function (Blueprint $table) {
            $table->id();
            $table->enum('position_category', ['eligibility', 'no_eligibility', 'all'])->default('all');
            $table->string('criterion'); // e.g., 'ipcr', 'education', 'experience', 'training', 'length_of_service', 'awards', 'demerits'
            $table->string('level')->nullable(); // e.g., 'first_level', 'second_level' (for education)
            $table->string('condition_name')->nullable(); // e.g., 'Doctorate Degree', 'CSC Award'
            $table->decimal('min_value', 10, 2)->nullable(); // e.g., 4.90
            $table->decimal('max_value', 10, 2)->nullable(); // e.g., 4.99
            $table->decimal('points', 5, 2); // e.g., 9.50
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrmpsb_rating_scales');
    }
};
