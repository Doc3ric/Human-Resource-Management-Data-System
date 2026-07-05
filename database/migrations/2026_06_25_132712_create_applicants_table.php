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
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('name_extension')->nullable();
            $table->string('sex');
            $table->date('date_of_birth');
            $table->text('address');
            $table->string('phone_number');
            $table->string('email_address');
            $table->boolean('is_pgb_employee')->default(false);
            $table->boolean('is_pwd')->default(false);
            $table->string('position_applied');
            $table->string('item_no')->nullable();
            $table->string('office')->nullable();
            $table->string('highest_educational_attainment');
            $table->string('degree')->nullable();
            $table->string('eligibility')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
