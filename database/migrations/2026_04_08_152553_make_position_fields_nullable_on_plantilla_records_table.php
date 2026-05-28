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
            $table->string('organizational_unit')->nullable()->change();
            $table->string('item')->nullable()->change();
            $table->string('position_title')->nullable()->change();
            $table->unsignedTinyInteger('salary_grade')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->string('organizational_unit')->nullable(false)->change();
            $table->string('item')->nullable(false)->change();
            $table->string('position_title')->nullable(false)->change();
            $table->unsignedTinyInteger('salary_grade')->nullable(false)->change();
        });
    }
};
