<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Roles
        $roles = [
            'System & Administration',
            'Personnel Records',
            'Appointment',
            'Performance Management',
            'Welfare & Benefits',
            'Viewer',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 2. Migrate existing users
        $users = User::all();
        foreach ($users as $user) {
            if ($user->role === 'super_admin') {
                $user->assignRole('System & Administration');
            } elseif ($user->role === 'inventory_admin') {
                $user->assignRole('Personnel Records');
            } elseif ($user->role === 'salary_admin') {
                $user->assignRole('Welfare & Benefits');
            } elseif ($user->role === 'viewer') {
                $user->assignRole('Viewer');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spatie', function (Blueprint $table) {
            //
        });
    }
};
