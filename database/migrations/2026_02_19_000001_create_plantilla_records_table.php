<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the unified plantilla_records table matching the CSC Plantilla Excel format.
     */
    public function up(): void
    {
        Schema::create('plantilla_records', function (Blueprint $table) {
            $table->id();

            // Position / Office Info
            $table->string('organizational_unit');               // PROV'L GOVERNOR'S OFFICE
            $table->string('item')->unique();                    // PGO-MGMT-1
            $table->string('position_title');                    // Provincial Governor
            $table->unsignedTinyInteger('salary_grade');         // 1–33
            $table->decimal('authorized_annual_salary', 12, 2)->default(0);
            $table->decimal('actual_annual_salary', 12, 2)->default(0);
            $table->unsignedTinyInteger('step')->default(1);     // 1–8
            $table->string('area_code', 10)->nullable();         // 10
            $table->string('area_type', 5)->nullable();          // P, C, etc.
            $table->string('level', 5)->nullable();              // K, T, S, A

            // Employee Info (null if vacant)
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->enum('sex', ['M', 'F'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('tin', 50)->nullable();
            $table->date('date_original_appointment')->nullable();
            $table->date('date_last_promotion')->nullable();

            // Status
            // E=Elected, CT=Co-Terminous, P=Permanent, Casual, JO=Job Order
            $table->string('employment_status', 20)->nullable();

            // CSC Fields
            $table->string('civil_service_eligibility')->nullable();
            $table->text('comment_annotation')->nullable();
            $table->boolean('is_pwd')->default(false);
            $table->string('indigenous_people', 20)->nullable(); // 'Y' or blank
            $table->string('solo_parent')->nullable();           // ID number if solo parent
            $table->boolean('abolished')->default(false);
            $table->boolean('dissolved')->default(false);
            $table->string('gsis_bp_number', 50)->nullable();
            $table->string('position_classification')->nullable(); // 1st Level, 2nd Level, EXECUTIVE/MANAGERIAL
            $table->string('umid', 50)->nullable();

            // Computed flag
            $table->boolean('is_vacant')->default(false);

            $table->timestamps();

            // Indexes for common queries
            $table->index('organizational_unit');
            $table->index('employment_status');
            $table->index('salary_grade');
            $table->index('is_vacant');
            $table->index('abolished');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantilla_records');
    }
};
