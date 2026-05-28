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
            $table->decimal('authorized_annual_salary', 12, 2)->nullable()->change();
            $table->decimal('actual_annual_salary', 12, 2)->nullable()->change();
            $table->unsignedTinyInteger('step')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->decimal('authorized_annual_salary', 12, 2)->nullable(false)->default(0)->change();
            $table->decimal('actual_annual_salary', 12, 2)->nullable(false)->default(0)->change();
            $table->unsignedTinyInteger('step')->nullable(false)->default(1)->change();
        });
    }
};
