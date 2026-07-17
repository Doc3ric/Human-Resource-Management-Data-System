<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Creates 3 default user accounts, one per role.
     */
    public function run(): void
    {
        // Super Admin – full access to all modules
        User::updateOrCreate(
            ['email' => 'superadmin@csc.gov.ph'],
            [
                'name'     => 'Super Administrator',
                'username' => 'superadmin',
                'password' => Hash::make('Password@123'),
                'role'     => 'super_admin',
                'status'   => 'Active',
            ]
        );

        // Salary Admin – Step Increment & Longevity only
        User::updateOrCreate(
            ['email' => 'salary@csc.gov.ph'],
            [
                'name'     => 'Salary Administrator',
                'username' => 'salaryadmin',
                'password' => Hash::make('Password@123'),
                'role'     => 'salary_admin',
                'status'   => 'Active',
            ]
        );

        // Inventory Admin – Plantilla / Inventory of Personnel only
        User::updateOrCreate(
            ['email' => 'inventory@csc.gov.ph'],
            [
                'name'     => 'Inventory Administrator',
                'username' => 'inventoryadmin',
                'password' => Hash::make('Password@123'),
                'role'     => 'inventory_admin',
                'status'   => 'Active',
            ]
        );

        // Run salary grade seeder
        $this->call(SalaryGradeSeeder::class);

        // Adds Employee Development / Document Filing / Discipline roles
        $this->call(RolePermissionExpansionSeeder::class);
    }
}
