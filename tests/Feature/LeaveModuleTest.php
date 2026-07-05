<?php

use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\PlantillaRecord;
use App\Models\User;
use App\Support\Leave\LeaveAccrualService;
use App\Support\Leave\LeaveApplicationService;
use App\Support\Leave\LeaveGuardrail;
use Database\Seeders\LeaveTypesSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(LeaveTypesSeeder::class);

    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Personnel Records', 'guard_name' => 'web']);
    foreach (['view Leave Application', 'add Leave Application', 'edit Leave Application'] as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    Role::where('name', 'Personnel Records')->first()->givePermissionTo(['view Leave Application', 'add Leave Application', 'edit Leave Application']);
    $this->recordsClerk = User::factory()->create();
    $this->recordsClerk->assignRole('Personnel Records');
});

test('a Job Order employee is blocked from leave accrual with the exact statutory message', function () {
    $jo = PlantillaRecord::factory()->create(['employment_status' => 'JO']);

    expect(fn () => app(LeaveAccrualService::class)->accrueMonth($jo, 2026))
        ->toThrow(RuntimeException::class, LeaveGuardrail::BLOCK_MESSAGE);
});

test('a permanent employee accrues 1.25 VL and 1.25 SL per month', function () {
    $permanent = PlantillaRecord::factory()->create(['employment_status' => 'P']);

    app(LeaveAccrualService::class)->accrueMonth($permanent, 2026);
    app(LeaveAccrualService::class)->accrueMonth($permanent, 2026);

    $vl = LeaveBalance::where('plantilla_record_id', $permanent->id)
        ->whereHas('leaveType', fn ($q) => $q->where('code', 'VL'))->first();

    expect((float) $vl->earned_days)->toBe(2.5);
});

test('a co-terminous employee (CT) is NOT excluded from leave — only JO/COS are', function () {
    $ct = PlantillaRecord::factory()->create(['employment_status' => 'CT']);
    expect(LeaveGuardrail::isExcluded($ct))->toBeFalse();
});

test('filing leave beyond the available balance is rejected', function () {
    $permanent = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $vl = LeaveType::where('code', 'VL')->first();

    expect(fn () => app(LeaveApplicationService::class)->file(
        $permanent, $vl, '2026-08-01', '2026-08-05', 5.0, '2026-07-20', $this->admin
    ))->toThrow(RuntimeException::class);
});

test('an authorized clerk can file and approve leave within balance, deducting it', function () {
    $permanent = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    app(LeaveAccrualService::class)->accrueMonth($permanent, 2026); // 1.25 VL

    $vl = LeaveType::where('code', 'VL')->first();

    $response = $this->actingAs($this->recordsClerk)->post(route('leave.store'), [
        'plantilla_record_id' => $permanent->id,
        'leave_type_id' => $vl->id,
        'date_from' => '2026-03-02',
        'date_to' => '2026-03-02',
        'days_requested' => 1,
        'filed_at' => '2026-02-25',
    ]);

    $response->assertRedirect();
    $application = LeaveApplication::first();
    expect($application)->not->toBeNull();
    expect($application->status)->toBe('pending');

    $this->actingAs($this->recordsClerk)->post(route('leave.approve', $application))->assertRedirect();

    $balance = LeaveBalance::where('plantilla_record_id', $permanent->id)
        ->where('leave_type_id', $vl->id)->where('year', 2026)->first();
    expect((float) $balance->used_days)->toBe(1.0);
});

test('a viewer without leave permissions cannot file leave', function () {
    Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');

    $permanent = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $vl = LeaveType::where('code', 'VL')->first();

    $this->actingAs($viewer)->post(route('leave.store'), [
        'plantilla_record_id' => $permanent->id,
        'leave_type_id' => $vl->id,
        'date_from' => '2026-03-02',
        'date_to' => '2026-03-02',
        'days_requested' => 1,
        'filed_at' => '2026-02-25',
    ])->assertForbidden();
});
