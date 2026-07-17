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
        Schema::table('step_increment_histories', function (Blueprint $table) {
            $table->foreignId('salary_schedule_id')->nullable()
                ->after('plantilla_record_id')
                ->constrained('salary_schedules')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('step_increment_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('salary_schedule_id');
        });
    }
};
