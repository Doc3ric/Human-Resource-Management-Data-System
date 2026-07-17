<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Adds the "Employee Development", "Document Filing", and "Discipline" roles
 * that already exist as module groups in the Role-Permission Matrix
 * (RolePermissionController::$modules) but had no corresponding Role row, so
 * they never appeared in the Create/Edit User role picker. Additive only —
 * does not touch any existing role or its permissions.
 *
 * Permission bits for these roles are seeded lazily by
 * RolePermissionController::index()'s existing "zero-permissions" bootstrap
 * the next time /users/role-matrix is loaded, same as every other role.
 */
class RolePermissionExpansionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Employee Development', 'Document Filing', 'Discipline'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
