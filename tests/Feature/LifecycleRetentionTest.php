<?php

use App\Models\PlantillaRecord;
use App\Models\RetentionSchedule;
use App\Models\User;
use Database\Seeders\RetentionScheduleSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
    $this->viewer = User::factory()->create();
    $this->viewer->assignRole('Viewer');
});

test('a separated permanent employee is excluded from live reporting even though is_renewed is irrelevant to them', function () {
    $active = PlantillaRecord::factory()->create(['employment_status' => 'P', 'lifecycle_status' => 'ACTIVE']);
    $retired = PlantillaRecord::factory()->create(['employment_status' => 'P', 'lifecycle_status' => 'RETIRED']);

    $ids = PlantillaRecord::filled()->pluck('id');
    expect($ids)->toContain($active->id);
    expect($ids)->not->toContain($retired->id);
});

test('an unrenewed casual employee is excluded even if lifecycle_status is still ACTIVE', function () {
    $unrenewed = PlantillaRecord::factory()->create([
        'employment_status' => 'JO', 'lifecycle_status' => 'ACTIVE', 'is_renewed' => false,
    ]);

    expect(PlantillaRecord::filled()->pluck('id'))->not->toContain($unrenewed->id);
});

test('anyone authenticated can view the retention schedule matrix', function () {
    $this->seed(RetentionScheduleSeeder::class);

    $response = $this->actingAs($this->viewer)->get(route('retention-schedule.index'));
    $response->assertOk();
    $response->assertSee('201 File');
});

test('a viewer without the edit permission cannot add a retention schedule entry', function () {
    $response = $this->actingAs($this->viewer)->post(route('retention-schedule.store'), [
        'record_series_title' => 'Test Series',
        'record_category' => 'Financial',
        'time_value' => 'TEMPORARY',
        'disposition_action' => 'DESTRUCTION',
    ]);

    $response->assertForbidden();
    expect(RetentionSchedule::where('record_series_title', 'Test Series')->exists())->toBeFalse();
});

test('an authorized records officer can add and edit retention schedule entries', function () {
    Role::firstOrCreate(['name' => 'Personnel Records', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'edit Records Retention Matrix', 'guard_name' => 'web']);
    Role::where('name', 'Personnel Records')->first()->givePermissionTo('edit Records Retention Matrix');

    $recordsOfficer = User::factory()->create();
    $recordsOfficer->assignRole('Personnel Records');

    $this->actingAs($recordsOfficer)->post(route('retention-schedule.store'), [
        'record_series_title' => 'Payroll Registers',
        'record_category' => 'Financial',
        'time_value' => 'TEMPORARY',
        'disposition_action' => 'DESTRUCTION',
    ])->assertRedirect();

    $series = RetentionSchedule::where('record_series_title', 'Payroll Registers')->first();
    expect($series)->not->toBeNull();

    $this->actingAs($recordsOfficer)->put(route('retention-schedule.update', $series), [
        'record_series_title' => 'Payroll Registers (Updated)',
        'record_category' => 'Financial',
        'time_value' => 'TEMPORARY',
        'disposition_action' => 'REVIEW',
    ])->assertRedirect();

    expect($series->fresh()->record_series_title)->toBe('Payroll Registers (Updated)');
});

test('IDCC doc_type_code rules link to their canonical NAP retention series', function () {
    $this->seed(\Database\Seeders\IdccRetentionRulesSeeder::class);
    $this->seed(RetentionScheduleSeeder::class);

    $rule = \App\Models\DocumentRetentionRule::where('doc_type_code', 'DISC-RACCS')->first();
    expect($rule->recordSeries)->not->toBeNull();
    expect($rule->recordSeries->record_series_title)->toContain('Disciplinary');
});
