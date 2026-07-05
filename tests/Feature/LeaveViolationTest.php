<?php

use App\Http\Controllers\BatchRenewalController;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\LeaveViolation;
use App\Models\PlantillaRecord;
use App\Models\User;
use App\Support\Leave\LeaveViolationDetectionService;
use Database\Seeders\LeaveTypesSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(LeaveTypesSeeder::class);

    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Personnel Records', 'guard_name' => 'web']);
    foreach (['view Leave Violations', 'add Leave Violations', 'edit Leave Violations'] as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    Role::where('name', 'Personnel Records')->first()->givePermissionTo([
        'view Leave Violations', 'add Leave Violations', 'edit Leave Violations',
    ]);
    $this->hrmo = User::factory()->create();
    $this->hrmo->assignRole('Personnel Records');
});

test('a JO employee produces no violations regardless of disapproved leave history', function () {
    $jo = PlantillaRecord::factory()->create(['employment_status' => 'JO']);
    $vl = LeaveType::where('code', 'VL')->first();

    LeaveApplication::create([
        'plantilla_record_id' => $jo->id, 'leave_type_id' => $vl->id,
        'date_from' => now()->subDays(40), 'date_to' => now()->subDays(5),
        'days_requested' => 35, 'filed_at' => now()->subDays(41), 'status' => 'disapproved',
    ]);

    $flags = app(LeaveViolationDetectionService::class)->scan($jo, BatchRenewalController::currentRatingPeriod());
    expect($flags)->toBeEmpty();
});

test('a single disapproved leave stretch of 30+ days flags AWOL', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $vl = LeaveType::where('code', 'VL')->first();

    LeaveApplication::create([
        'plantilla_record_id' => $employee->id, 'leave_type_id' => $vl->id,
        'date_from' => now()->subDays(40), 'date_to' => now()->subDays(5),
        'days_requested' => 35, 'filed_at' => now()->subDays(41), 'status' => 'disapproved',
    ]);

    $flags = app(LeaveViolationDetectionService::class)->scan($employee, BatchRenewalController::currentRatingPeriod());

    expect($flags)->not->toBeEmpty();
    expect(LeaveViolation::where('violation_type', 'AWOL')->where('plantilla_record_id', $employee->id)->exists())->toBeTrue();
});

test('a pattern of disapproved leave across 3+ months flags habitual absenteeism', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $vl = LeaveType::where('code', 'VL')->first();

    foreach ([now()->subMonths(3), now()->subMonths(2), now()->subMonths(1)] as $month) {
        LeaveApplication::create([
            'plantilla_record_id' => $employee->id, 'leave_type_id' => $vl->id,
            'date_from' => $month->copy()->startOfMonth()->addDays(2),
            'date_to' => $month->copy()->startOfMonth()->addDays(5),
            'days_requested' => 3, 'filed_at' => $month->copy()->startOfMonth(), 'status' => 'disapproved',
        ]);
    }

    app(LeaveViolationDetectionService::class)->scan($employee, BatchRenewalController::currentRatingPeriod());

    expect(LeaveViolation::where('violation_type', 'HABITUAL_ABSENTEEISM')->where('plantilla_record_id', $employee->id)->exists())->toBeTrue();
});

test('issuing a notice generates a PDF, links it via IDCC, and moves status to notice_pending', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $violation = LeaveViolation::create([
        'plantilla_record_id' => $employee->id, 'violation_type' => 'AWOL', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => ['total_unauthorized_days' => 35], 'status' => 'detected',
    ]);

    $this->actingAs($this->hrmo)->post(route('leave-violations.issue-notice', $violation))->assertRedirect();

    $violation->refresh();
    expect($violation->status)->toBe('notice_pending');
    expect($violation->document_id)->not->toBeNull();
});

test('a user without leave-violation permission cannot issue a notice', function () {
    Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');

    $employee = PlantillaRecord::factory()->create();
    $violation = LeaveViolation::create([
        'plantilla_record_id' => $employee->id, 'violation_type' => 'AWOL', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => [], 'status' => 'detected',
    ]);

    $this->actingAs($viewer)->post(route('leave-violations.issue-notice', $violation))->assertForbidden();
});
