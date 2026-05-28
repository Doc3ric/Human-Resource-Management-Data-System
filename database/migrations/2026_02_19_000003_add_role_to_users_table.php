<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds role-based access column to users table.
     * Roles:
     *   super_admin     – full access to all modules
     *   salary_admin    – Step Increment & Longevity only
     *   inventory_admin – Inventory of Personnel (Plantilla) only
     */
    public function up(): void
    {
        // Guard: skip if role column was already added by a previous migration
        if (Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'salary_admin', 'inventory_admin'])
                  ->default('super_admin')
                  ->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
