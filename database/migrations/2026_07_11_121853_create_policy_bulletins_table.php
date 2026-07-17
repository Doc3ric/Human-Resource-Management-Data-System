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
        Schema::create('policy_bulletins', function (Blueprint $table) {
            $table->id();
            $table->string('source_agency')->index(); // e.g., CSC, DBM, DILG
            $table->string('reference_no')->nullable()->index(); // e.g., MC No. 08, s. 2025
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('url')->unique(); // Link to the official PDF/Document
            $table->date('date_issued')->nullable()->index();
            $table->integer('applicability_score')->default(0);
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_bulletins');
    }
};
