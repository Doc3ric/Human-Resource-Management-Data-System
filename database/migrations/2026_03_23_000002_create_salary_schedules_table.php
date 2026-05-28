<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the salary_schedules table.
     * Each row represents one SSL Tranche (e.g. SSL VI – Tranche 1).
     * Only one schedule may be active at a time.
     */
    public function up(): void
    {
        Schema::create('salary_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // e.g. "SSL VI – Tranche 2"
            $table->string('law_name')->nullable();        // e.g. "R.A. 11466"
            $table->date('effective_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_schedules');
    }
};
