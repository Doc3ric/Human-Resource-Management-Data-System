<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casual_employees', function (Blueprint $table) {
            $table->id();

            // Office / Department
            $table->string('office')->nullable();        // Department/Office

            // Item Numbers (Old / New from Plantilla document)
            $table->string('item_no_old', 20)->nullable();
            $table->string('item_no_new', 20)->nullable();

            // Position
            $table->string('position_title')->nullable();

            // Incumbent identity (nullable when vacant)
            $table->boolean('is_vacant')->default(false);
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_initial', 10)->nullable();
            $table->string('name_extension', 20)->nullable(); // Jr., Sr., III

            // Salary – Current Year (LBC #165 Rate/Annum 2025)
            $table->unsignedTinyInteger('sg_current')->nullable();   // Salary Grade
            $table->unsignedTinyInteger('step_current')->nullable(); // Step
            $table->decimal('salary_current', 12, 2)->nullable();    // Annual amount

            // Salary – Budget Year Proposed (Rate/Annum 2026)
            $table->unsignedTinyInteger('sg_proposed')->nullable();
            $table->unsignedTinyInteger('step_proposed')->nullable();
            $table->decimal('salary_proposed', 12, 2)->nullable();   // Annual amount

            // Increase / Decrease
            $table->decimal('increase_decrease', 12, 2)->nullable();

            // Monthly rates
            $table->decimal('previous_rate', 10, 2)->nullable();   // Previous monthly rate
            $table->decimal('current_rate', 10, 2)->nullable();    // Current monthly rate

            // Personal info
            $table->string('gender', 1)->nullable();               // M or F
            $table->date('birthdate')->nullable();
            $table->date('first_day_of_service')->nullable();
            $table->string('eligibility')->nullable();
            $table->string('address')->nullable();
            $table->boolean('solo_parent')->default(false);
            $table->string('ip_community_membership')->nullable();

            $table->text('remarks')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casual_employees');
    }
};
