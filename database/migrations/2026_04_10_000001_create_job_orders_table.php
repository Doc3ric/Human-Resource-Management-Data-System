<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();

            // Identity / Office
            $table->string('charges')->nullable();          // Office/charges code e.g. BEMO
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_initial', 10)->nullable();
            $table->string('name_extension', 20)->nullable(); // Jr., Sr., III

            // Position
            $table->string('position_title')->nullable();
            $table->string('nature_of_work')->nullable();   // Clerical / Trades & Crafts / Technical
            $table->string('office')->nullable();           // Assigned office
            $table->decimal('rate_per_day', 10, 2)->nullable();

            // Service
            $table->date('first_day_of_service')->nullable();

            // Personal
            $table->date('birthdate')->nullable();
            $table->text('address')->nullable();

            // Eligibility / Classification
            $table->string('eligibility')->nullable();
            $table->string('gender', 1)->nullable();        // M or F
            $table->boolean('first_level_eligibility')->default(false);
            $table->boolean('second_level_eligibility')->default(false);
            $table->string('ip_community_membership')->nullable();
            $table->boolean('solo_parent')->default(false);

            // Misc
            $table->text('remarks')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};
