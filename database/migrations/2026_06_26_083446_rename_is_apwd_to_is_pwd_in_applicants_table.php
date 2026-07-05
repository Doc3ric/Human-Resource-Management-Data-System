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
        // create_applicants_table already uses is_pwd directly on fresh installs
        // (this rename only applies to databases created before that fix).
        if (! Schema::hasColumn('applicants', 'is_apwd')) {
            return;
        }
        Schema::table('applicants', function (Blueprint $table) {
            $table->renameColumn('is_apwd', 'is_pwd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->renameColumn('is_pwd', 'is_apwd');
        });
    }
};
