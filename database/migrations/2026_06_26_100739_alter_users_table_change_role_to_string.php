<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change the column from ENUM to VARCHAR
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('Viewer')->change();
        });

        // Update existing legacy ENUM values to match the new roles
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'System & Administration']);
        DB::table('users')->where('role', 'inventory_admin')->update(['role' => 'Personnel Records']);
        DB::table('users')->where('role', 'salary_admin')->update(['role' => 'Welfare & Benefits']);
        DB::table('users')->where('role', 'viewer')->update(['role' => 'Viewer']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('string', function (Blueprint $table) {
            //
        });
    }
};
