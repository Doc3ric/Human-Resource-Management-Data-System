<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Module 9 Stage 5 — seeds the RACCS-authorized roles named in the spec.
 * These didn't exist in the app's role set before Module 9; nothing is
 * assigned to them automatically — an Administrator grants them via the
 * existing User Management / Role Matrix screens.
 */
class IdccRolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Discipline Committee'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
