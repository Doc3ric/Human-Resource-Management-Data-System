<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

/**
 * Adds a dedicated "Leave Administration" role, separate from Personnel
 * Records, scoped only to Leave Application / Leave Violations. Default
 * permissions are seeded on first load of the Role-Permission Matrix
 * (see RolePermissionController::$defaults).
 */
return new class extends Migration
{
    public function up(): void
    {
        Role::firstOrCreate(['name' => 'Leave Administration', 'guard_name' => 'web']);
    }

    public function down(): void
    {
        Role::where('name', 'Leave Administration')->delete();
    }
};
