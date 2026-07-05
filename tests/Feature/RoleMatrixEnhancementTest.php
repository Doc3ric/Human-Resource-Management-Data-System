<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('role matrix page still loads for system administration user', function () {
    $role = Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole($role);

    $this->actingAs($admin)->get(route('users.role-matrix'))->assertOk();
});

test('per-user overrides page loads and lists other users', function () {
    $role = Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole($role);
    $other = User::factory()->create(['name' => 'Some Encoder']);
    Role::firstOrCreate(['name' => 'Appointment Encoder', 'guard_name' => 'web']);
    $other->assignRole('Appointment Encoder');

    $response = $this->actingAs($admin)->get(route('users.role-matrix.user-overrides'));

    $response->assertOk();
    $response->assertSee('Some Encoder');
});

test('granting Edit auto-grants View for the same sub-module', function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('System & Administration');

    $target = User::factory()->create();
    Role::firstOrCreate(['name' => 'Appointment Encoder', 'guard_name' => 'web']);
    $target->assignRole('Appointment Encoder');

    $this->actingAs($admin)->post(route('users.role-matrix.user-overrides.store', $target), [
        'permissions' => ['edit Recruitment' => '1'],
    ])->assertRedirect();

    $target->refresh();
    expect($target->hasDirectPermission('edit Recruitment'))->toBeTrue();
    expect($target->hasDirectPermission('view Recruitment'))->toBeTrue();
});

test('an admin cannot set permission overrides for themselves', function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('System & Administration');

    $response = $this->actingAs($admin)->post(route('users.role-matrix.user-overrides.store', $admin), [
        'permissions' => ['edit Recruitment' => '1'],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});
