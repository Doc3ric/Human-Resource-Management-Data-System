<?php

use App\Models\CasualEmployee;
use App\Models\JobOrder;
use App\Models\PlantillaRecord;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Module 4A RBAC route cutover pilot (Job Orders + Casual Employees +
 * Plantilla): verifies the switch from role: to permission: middleware
 * preserves the exact same effective access for every real role, using the
 * actual permission bits already granted in the live app (Personnel Records
 * holds the full CRUD set for each; Viewer holds view-only).
 */
beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Personnel Records', 'guard_name' => 'web']);
    foreach (['Job Orders', 'Casual Employees', 'Plantilla', 'Permanent Employees', 'All Data'] as $module) {
        foreach (['view', 'add', 'edit', 'delete', 'archive'] as $action) {
            Permission::firstOrCreate(['name' => "{$action} {$module}", 'guard_name' => 'web']);
        }
    }
    Role::where('name', 'Personnel Records')->first()->givePermissionTo([
        'view Job Orders', 'add Job Orders', 'edit Job Orders', 'delete Job Orders',
        'view Casual Employees', 'add Casual Employees', 'edit Casual Employees', 'delete Casual Employees',
        'view Plantilla', 'add Plantilla', 'edit Plantilla', 'delete Plantilla',
        'view Permanent Employees',
        'view All Data', 'add All Data', 'edit All Data', 'delete All Data',
    ]);
    $this->recordsOfficer = User::factory()->create();
    $this->recordsOfficer->assignRole('Personnel Records');

    Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
    Role::where('name', 'Viewer')->first()->givePermissionTo([
        'view Job Orders', 'view Casual Employees', 'view Plantilla', 'view Permanent Employees', 'view All Data',
    ]);
    $this->viewer = User::factory()->create();
    $this->viewer->assignRole('Viewer');

    Role::firstOrCreate(['name' => 'Appointment Encoder', 'guard_name' => 'web']);
    $this->outsider = User::factory()->create();
    $this->outsider->assignRole('Appointment Encoder'); // holds neither bit
});

test('Personnel Records retains full Job Orders CRUD access after the cutover', function () {
    $jobOrder = JobOrder::create(['last_name' => 'DELA CRUZ', 'first_name' => 'JUAN', 'sex' => 'Male']);

    $this->actingAs($this->recordsOfficer)->get(route('job-orders.index'))->assertOk();
    $this->actingAs($this->recordsOfficer)->get(route('job-orders.create'))->assertOk();
    $this->actingAs($this->recordsOfficer)->get(route('job-orders.edit', $jobOrder))->assertOk();
    // Note: job-orders.template is not exercised here — JobOrderController::
    // downloadTemplate() ends with a raw exit;, which terminates the whole
    // in-process test runner (Laravel tests dispatch through the real Kernel
    // in-process, not over real HTTP) rather than just that one request.
    // Harmless in real production (each HTTP request is its own process),
    // but untestable in-process without changing that unrelated legacy code.
    $updateResponse = $this->actingAs($this->recordsOfficer)->put(route('job-orders.update', $jobOrder), ['last_name' => 'DELA CRUZ', 'first_name' => 'JUAN']);
    expect($updateResponse->status())->not->toBe(403);
    $this->actingAs($this->recordsOfficer)->delete(route('job-orders.destroy', $jobOrder))->assertRedirect();
});

test('Viewer keeps read-only Job Orders access after the cutover — write routes stay blocked', function () {
    $jobOrder = JobOrder::create(['last_name' => 'SANTOS', 'first_name' => 'MARIA', 'sex' => 'Female']);

    $this->actingAs($this->viewer)->get(route('job-orders.index'))->assertOk();
    $this->actingAs($this->viewer)->get(route('job-orders.edit', $jobOrder))->assertOk();
    $this->actingAs($this->viewer)->get(route('job-orders.create'))->assertForbidden();
    $this->actingAs($this->viewer)->put(route('job-orders.update', $jobOrder), [])->assertForbidden();
    $this->actingAs($this->viewer)->delete(route('job-orders.destroy', $jobOrder))->assertForbidden();
});

test('a role with neither bit is blocked from Job Orders entirely, including the index', function () {
    $this->actingAs($this->outsider)->get(route('job-orders.index'))->assertForbidden();
});

test('Job Orders delete-all remains super-admin-only after the cutover (deliberately not converted)', function () {
    $this->actingAs($this->recordsOfficer)->delete(route('job-orders.delete-all'))->assertForbidden();
    $response = $this->actingAs($this->admin)->delete(route('job-orders.delete-all'));
    expect($response->status())->not->toBe(403);
});

test('Personnel Records retains full Casual Employees CRUD access after the cutover', function () {
    $casual = CasualEmployee::create(['last_name' => 'REYES', 'first_name' => 'PEDRO', 'sex' => 'Male']);

    $this->actingAs($this->recordsOfficer)->get(route('casual.index'))->assertOk();
    $this->actingAs($this->recordsOfficer)->get(route('casual.create'))->assertOk();
    $this->actingAs($this->recordsOfficer)->get(route('casual.edit', $casual))->assertOk();
    $this->actingAs($this->recordsOfficer)->delete(route('casual.destroy', $casual))->assertRedirect();
});

test('Casual Employees delete-all was already effectively super-admin-only via an internal controller hard-gate, unaffected by the route middleware cutover', function () {
    // CasualController::deleteAll() checks Auth::user()->isSuperAdmin() itself
    // (line ~621), regardless of what route middleware says — the old
    // role:super_admin,inventory_admin wording was misleading; Personnel
    // Records was never actually able to reach this action.
    $this->actingAs($this->recordsOfficer)->delete(route('casual.delete-all'))->assertForbidden();
    $response = $this->actingAs($this->admin)->delete(route('casual.delete-all'));
    expect($response->status())->not->toBe(403);
});

test('Viewer keeps read-only Casual Employees access — write routes stay blocked', function () {
    $casual = CasualEmployee::create(['last_name' => 'CRUZ', 'first_name' => 'ANA', 'sex' => 'Female']);

    $this->actingAs($this->viewer)->get(route('casual.index'))->assertOk();
    $this->actingAs($this->viewer)->get(route('casual.create'))->assertForbidden();
    $this->actingAs($this->viewer)->delete(route('casual.destroy', $casual))->assertForbidden();
});

test('super admin retains full access via the Gate::before bypass, unaffected by the permission: middleware switch', function () {
    $jobOrder = JobOrder::create(['last_name' => 'BYPASS', 'first_name' => 'TEST', 'sex' => 'Male']);

    $this->actingAs($this->admin)->get(route('job-orders.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('job-orders.create'))->assertOk();
    $this->actingAs($this->admin)->delete(route('job-orders.destroy', $jobOrder))->assertRedirect();
});

test('Personnel Records retains full Plantilla CRUD access after the cutover', function () {
    $record = PlantillaRecord::factory()->create();

    $this->actingAs($this->recordsOfficer)->get(route('plantilla.index'))->assertOk();
    $this->actingAs($this->recordsOfficer)->get(route('plantilla.show', $record))->assertOk();
    $this->actingAs($this->recordsOfficer)->get(route('plantilla.create'))->assertOk();
    $this->actingAs($this->recordsOfficer)->get(route('plantilla.edit', $record))->assertOk();
    $updateResponse = $this->actingAs($this->recordsOfficer)->put(route('plantilla.update', $record), $record->only(['last_name', 'first_name']));
    expect($updateResponse->status())->not->toBe(403);
    $this->actingAs($this->recordsOfficer)->delete(route('plantilla.destroy', $record))->assertRedirect();
});

test('Viewer keeps read-only Plantilla access after the cutover — write routes stay blocked', function () {
    $record = PlantillaRecord::factory()->create();

    $this->actingAs($this->viewer)->get(route('plantilla.index'))->assertOk();
    $this->actingAs($this->viewer)->get(route('plantilla.show', $record))->assertOk();
    $this->actingAs($this->viewer)->get(route('plantilla.create'))->assertForbidden();
    $this->actingAs($this->viewer)->put(route('plantilla.update', $record), [])->assertForbidden();
    $this->actingAs($this->viewer)->delete(route('plantilla.destroy', $record))->assertForbidden();
});

test('a role with neither bit is blocked from Plantilla entirely, including the index', function () {
    $this->actingAs($this->outsider)->get(route('plantilla.index'))->assertForbidden();
});

test('super admin retains full Plantilla access via the Gate::before bypass', function () {
    $record = PlantillaRecord::factory()->create();

    $this->actingAs($this->admin)->get(route('plantilla.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('plantilla.create'))->assertOk();
    $this->actingAs($this->admin)->delete(route('plantilla.destroy', $record))->assertRedirect();
});

test('Personnel Records and Viewer both keep Permanent Employees read access after the cutover (no write CRUD exists on this route group)', function () {
    $this->actingAs($this->recordsOfficer)->get(route('permanent.index'))->assertOk();
    $this->actingAs($this->viewer)->get(route('permanent.index'))->assertOk();
    $this->actingAs($this->outsider)->get(route('permanent.index'))->assertForbidden();
});

test('Permanent Employees delete-all remains super-admin-only via its internal hard-gate, unaffected by the cutover', function () {
    $this->actingAs($this->recordsOfficer)->delete(route('permanent.delete-all'))->assertForbidden();
    $response = $this->actingAs($this->admin)->delete(route('permanent.delete-all'));
    expect($response->status())->not->toBe(403);
});

test('Personnel Records retains full All Data CRUD access after the cutover', function () {
    $record = PlantillaRecord::factory()->create();

    $this->actingAs($this->recordsOfficer)->get(route('all-data.index'))->assertOk();
    $this->actingAs($this->recordsOfficer)->get(route('all-data.create'))->assertOk();
    $this->actingAs($this->recordsOfficer)->get(route('all-data.edit', $record))->assertOk();
    $this->actingAs($this->recordsOfficer)->get(route('all-data.export.full'))->assertOk();
    $updateResponse = $this->actingAs($this->recordsOfficer)->put(route('all-data.update', $record), $record->only(['last_name', 'first_name']));
    expect($updateResponse->status())->not->toBe(403);
    $this->actingAs($this->recordsOfficer)->delete(route('all-data.destroy', $record))->assertRedirect();
});

test('Viewer keeps read-only All Data access — export/full stays blocked since it is not a plain view action', function () {
    $record = PlantillaRecord::factory()->create();

    $this->actingAs($this->viewer)->get(route('all-data.index'))->assertOk();
    $this->actingAs($this->viewer)->get(route('all-data.export.excel'))->assertOk();
    $this->actingAs($this->viewer)->get(route('all-data.export.full'))->assertForbidden();
    $this->actingAs($this->viewer)->get(route('all-data.create'))->assertForbidden();
    $this->actingAs($this->viewer)->delete(route('all-data.destroy', $record))->assertForbidden();
});

test('a role with neither bit is blocked from All Data entirely, including the index', function () {
    $this->actingAs($this->outsider)->get(route('all-data.index'))->assertForbidden();
});
