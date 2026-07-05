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
        // RA 9710 (Magna Carta of Women) mandates the term "sex" not "gender"
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('gender', 'sex');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('sex', 'gender');
        });
    }
};
