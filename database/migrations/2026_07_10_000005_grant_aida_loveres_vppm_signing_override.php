<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use App\Models\User;

return new class extends Migration
{
    /**
     * VPPM Phase C open decision, resolved on approval: the spec's
     * signature-authority person (Aida B. Loveres, Dept. Head / appointing
     * authority) holds the "Viewer" role in this system's RBAC, which is
     * read-only. Rather than change her role identity (which would grant
     * her broad Appointment-module access she doesn't need) this grants a
     * direct per-user permission override via Spatie's direct-permission
     * mechanism — the same mechanism the existing "User Overrides" screen
     * (RolePermissionController::storeUserOverrides, Module 4A.2) uses —
     * scoped to exactly the "edit Vacancy Publication" bit her signing/
     * submission actions in VacantPositionController::authorizeEdit() check.
     */
    public function up(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'edit Vacancy Publication', 'guard_name' => 'web']);

        $user = User::where('email', 'abl@gmail.com')->first();
        if ($user) {
            $user->givePermissionTo($permission);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $user = User::where('email', 'abl@gmail.com')->first();
        if ($user) {
            $user->revokePermissionTo('edit Vacancy Publication');
        }
    }
};
