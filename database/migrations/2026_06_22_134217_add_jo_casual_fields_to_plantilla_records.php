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
        Schema::table('plantilla_records', function (Blueprint $table) {
            // Job Order specific fields
            $table->string('charges')->nullable();
            $table->string('name_extension', 20)->nullable();
            $table->string('nature_of_work')->nullable();
            $table->string('nature_of_work_detail')->nullable();
            $table->decimal('rate_per_day', 10, 2)->nullable();
            $table->date('first_day_of_service')->nullable();
            $table->string('civil_status')->nullable();
            $table->text('address')->nullable();
            $table->boolean('first_level_eligibility')->default(false);
            $table->boolean('second_level_eligibility')->default(false);
            $table->boolean('reemployment')->default(false);
            $table->text('remarks')->nullable();

            // Casual Employee specific fields
            $table->string('item_no_old', 20)->nullable();
            $table->string('legislative_district')->nullable();
            $table->tinyInteger('sg_proposed')->unsigned()->nullable();
            $table->tinyInteger('step_proposed')->unsigned()->nullable();
            $table->decimal('salary_proposed', 12, 2)->nullable();
            $table->decimal('increase_decrease', 12, 2)->nullable();
            $table->decimal('previous_rate', 10, 2)->nullable();
            $table->decimal('current_rate', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropColumn([
                'charges', 'name_extension', 'nature_of_work', 'nature_of_work_detail',
                'rate_per_day', 'first_day_of_service', 'civil_status', 'address',
                'first_level_eligibility', 'second_level_eligibility', 'reemployment', 'remarks',
                'item_no_old', 'legislative_district', 'sg_proposed', 'step_proposed',
                'salary_proposed', 'increase_decrease', 'previous_rate', 'current_rate'
            ]);
        });
    }
};
