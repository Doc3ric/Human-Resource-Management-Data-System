<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL-only DDL (a later migration converts `role` to a plain string
        // anyway); skip on other drivers so it doesn't break e.g. sqlite test runs.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','salary_admin','inventory_admin','viewer') NOT NULL DEFAULT 'viewer'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','salary_admin','inventory_admin') NOT NULL DEFAULT 'inventory_admin'");
    }
};
