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
        if (Schema::hasColumn('plantilla_records', 'nature_of_work')) {
            Schema::table('plantilla_records', function (Blueprint $table) {
                $table->dropColumn('nature_of_work');
            });
        }
        if (Schema::hasColumn('job_orders', 'nature_of_work')) {
            Schema::table('job_orders', function (Blueprint $table) {
                $table->dropColumn('nature_of_work');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->string('nature_of_work')->nullable();
        });
        Schema::table('job_orders', function (Blueprint $table) {
            $table->string('nature_of_work')->nullable();
        });
    }
};
