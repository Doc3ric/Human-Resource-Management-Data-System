<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add missing columns for real plantilla data
     */
    public function up(): void
    {
        // Add columns to positions table
        Schema::table('positions', function (Blueprint $table) {
            $table->decimal('authorized_annual_salary', 12, 2)->nullable()->after('salary_grade');
            $table->decimal('actual_annual_salary', 12, 2)->nullable()->after('authorized_annual_salary');
            $table->integer('step')->nullable()->after('actual_annual_salary');
            $table->string('level')->nullable()->after('step'); // K, T, S, A, etc.
            $table->string('area_code')->nullable()->after('level');
            $table->string('area_type')->nullable()->after('area_code'); // P, C, etc.
            $table->string('position_classification')->nullable()->after('area_type'); // EXECUTIVE/MANAGERIAL, 2nd Level, etc.
        });

        // Add columns to employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->string('tin')->nullable()->after('email'); // Tax ID
            $table->string('civil_service_eligibility')->nullable()->after('status');
            $table->boolean('pwd')->default(false)->after('civil_service_eligibility'); // Person with Disability
            $table->string('indigenous_people')->nullable()->after('pwd'); // Y/N with ID
            $table->string('solo_parent')->nullable()->after('indigenous_people'); // ID number
            $table->string('gsis_bp_number')->nullable()->after('solo_parent');
            $table->string('umid')->nullable()->after('gsis_bp_number');
        });

        // Add columns to appointments table
        Schema::table('appointments', function (Blueprint $table) {
            $table->date('date_of_last_promotion')->nullable()->after('appointment_end');
            $table->string('employment_status')->default('P')->after('status'); // E, CT, P, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn(['authorized_annual_salary', 'actual_annual_salary', 'step', 'level', 'area_code', 'area_type', 'position_classification']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['tin', 'civil_service_eligibility', 'pwd', 'indigenous_people', 'solo_parent', 'gsis_bp_number', 'umid']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['date_of_last_promotion', 'employment_status']);
        });
    }
};
