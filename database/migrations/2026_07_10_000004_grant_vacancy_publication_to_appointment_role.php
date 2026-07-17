<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * VPPM's "Vacancy Publication" sub-module was added to
     * RolePermissionController's $modules/$defaults['Appointment'] arrays,
     * but that only auto-seeds a role with zero permissions — the
     * "Appointment" role already has permissions assigned, so it needs the
     * same one-time backfill treatment as 2026_07_08_000006.
     */
    public function up(): void
    {
        $permissions = ['view Vacancy Publication', 'add Vacancy Publication', 'edit Vacancy Publication'];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::where('name', 'Appointment')->first();
        if ($role) {
            $role->givePermissionTo($permissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Intentionally a no-op — matches 2026_07_08_000006's precedent of not reversing RBAC data migrations.
    }
};
