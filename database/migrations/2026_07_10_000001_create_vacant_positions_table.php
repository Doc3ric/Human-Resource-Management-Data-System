<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * VPPM (Vacant Position Publication & Monitoring) — RA 7041 / 2025 ORAOHRA
     * Sec. 26/30/31. One row per vacancy episode being published. References
     * plantilla_records (nullable — "entered manually" per spec Sec.2.1 is
     * also allowed) by id, not item_no_new, since item_no_new's uniqueness
     * was dropped in an earlier migration. Fields that describe the vacancy
     * episode itself (parenthetical_title, vacancy_type, vice_whom,
     * vacated_date) live only here — they don't exist on plantilla_records
     * and don't belong there; plantilla_records is left untouched.
     */
    public function up(): void
    {
        Schema::create('vacant_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_record_id')->nullable()->constrained('plantilla_records')->nullOnDelete();

            // Snapshots at creation time (same precedent as applicants.salary_grade_snapshot) —
            // publication content must not silently drift if the master plantilla record changes later.
            $table->string('item_no_snapshot')->nullable();
            $table->string('position_title');
            $table->string('parenthetical_title')->nullable(); // Sec 26 carve-out: missing this is NOT a disapproval ground
            $table->string('salary_grade', 10);
            $table->decimal('monthly_salary', 12, 2);
            $table->string('place_of_assignment');
            $table->string('office_division')->nullable();

            $table->enum('appointment_status', ['Permanent', 'Temporary', 'Casual', 'Coterminous']);
            $table->enum('vacancy_type', ['Original', 'Vice', 'Reclassified', 'Created']);
            $table->string('vice_whom')->nullable();
            $table->date('vacated_date')->nullable();

            $table->boolean('is_anticipated')->default(false); // Sec 31: up to 180 days before incumbent separates
            $table->date('anticipated_incumbent_separation_date')->nullable();

            $table->text('qs_education');
            $table->text('qs_training');
            $table->text('qs_experience');
            $table->text('qs_eligibility');

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['DRAFT', 'FOR_PUBLICATION', 'FILLED', 'CANCELLED'])->default('DRAFT');
            $table->timestamps();

            $table->index('status');
            $table->index('is_anticipated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacant_positions');
    }
};
