<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Enhancement Spec — RBAC Baseline: "Dashboard module and Document Filing
     * module are granted to every user role by default, regardless of other
     * permission restrictions." One-time backfill for roles that already
     * have permissions assigned (RolePermissionController's own "seed
     * defaults for zero-permission roles" rule never touches these), same
     * idempotent firstOrCreate/givePermissionTo shape as migrate_roles_to_spatie.php.
     */
    public function up(): void
    {
        $permissions = ['view Dashboard', 'view LGU Documents'];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        foreach (Role::all() as $role) {
            $role->givePermissionTo($permissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Intentionally a no-op — matches migrate_roles_to_spatie.php's
        // precedent of not reversing RBAC data migrations.
    }
};
