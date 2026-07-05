<?php

use App\Http\Controllers\BatchRenewalController;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\LeaveViolation;
use App\Models\PlantillaRecord;
use App\Models\User;
use App\Support\Leave\LeaveViolationDetectionService;
use App\Support\Leave\LeaveViolationLetterService;
use Database\Seeders\LeaveTypesSeeder;
use Database\Seeders\LeaveViolationThresholdsSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Module 2B.9-11 — leave-filing/procedure violations, distinct from the
 * attendance-pattern violations covered by LeaveViolationTest.php.
 */
beforeEach(function () {
    $this->seed(LeaveTypesSeeder::class);
    $this->seed(LeaveViolationThresholdsSeeder::class);

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

test('a JO employee produces no filing violations either', function () {
    $jo = PlantillaRecord::factory()->create(['employment_status' => 'JO']);
    $vl = LeaveType::where('code', 'VL')->first();

    LeaveApplication::create([
        'plantilla_record_id' => $jo->id, 'leave_type_id' => $vl->id,
        'date_from' => now()->addDays(1), 'date_to' => now()->addDays(2),
        'days_requested' => 1, 'filed_at' => now(), 'status' => 'pending',
    ]);

    $flags = app(LeaveViolationDetectionService::class)->scan($jo, BatchRenewalController::currentRatingPeriod());
    expect($flags)->toBeEmpty();
});

test('Sec. 51: vacation leave filed less than 5 days in advance is flagged late', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $vl = LeaveType::where('code', 'VL')->first();

    LeaveApplication::create([
        'plantilla_record_id' => $employee->id, 'leave_type_id' => $vl->id,
        'date_from' => now()->addDays(2), 'date_to' => now()->addDays(3),
        'days_requested' => 1, 'filed_at' => now(), 'status' => 'pending',
    ]);

    app(LeaveViolationDetectionService::class)->scan($employee, BatchRenewalController::currentRatingPeriod());

    expect(LeaveViolation::where('violation_type', 'LATE_FILING_VACATION_LEAVE')
        ->where('plantilla_record_id', $employee->id)->exists())->toBeTrue();
});

test('Sec. 51: vacation leave filed 5+ days in advance is NOT flagged', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $vl = LeaveType::where('code', 'VL')->first();

    LeaveApplication::create([
        'plantilla_record_id' => $employee->id, 'leave_type_id' => $vl->id,
        'date_from' => now()->addDays(10), 'date_to' => now()->addDays(11),
        'days_requested' => 1, 'filed_at' => now(), 'status' => 'pending',
    ]);

    app(LeaveViolationDetectionService::class)->scan($employee, BatchRenewalController::currentRatingPeriod());

    expect(LeaveViolation::where('violation_type', 'LATE_FILING_VACATION_LEAVE')
        ->where('plantilla_record_id', $employee->id)->exists())->toBeFalse();
});

test('Sec. 53: sick leave not filed immediately upon return is flagged late', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $sl = LeaveType::where('code', 'SL')->first();

    LeaveApplication::create([
        'plantilla_record_id' => $employee->id, 'leave_type_id' => $sl->id,
        'date_from' => now()->subDays(10), 'date_to' => now()->subDays(8),
        'days_requested' => 2, 'filed_at' => now(), 'status' => 'pending',
    ]);

    app(LeaveViolationDetectionService::class)->scan($employee, BatchRenewalController::currentRatingPeriod());

    expect(LeaveViolation::where('violation_type', 'LATE_FILING_SICK_LEAVE')
        ->where('plantilla_record_id', $employee->id)->exists())->toBeTrue();
});

test('Sec. 53: sick leave filed immediately upon return is NOT flagged', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $sl = LeaveType::where('code', 'SL')->first();

    LeaveApplication::create([
        'plantilla_record_id' => $employee->id, 'leave_type_id' => $sl->id,
        'date_from' => now()->subDays(3), 'date_to' => now()->subDays(1),
        'days_requested' => 2, 'filed_at' => now(), 'status' => 'pending',
    ]);

    app(LeaveViolationDetectionService::class)->scan($employee, BatchRenewalController::currentRatingPeriod());

    expect(LeaveViolation::where('violation_type', 'LATE_FILING_SICK_LEAVE')
        ->where('plantilla_record_id', $employee->id)->exists())->toBeFalse();
});

test('Sec. 53: sick leave over 5 days with no attached document is flagged for missing supporting docs', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $sl = LeaveType::where('code', 'SL')->first();

    LeaveApplication::create([
        'plantilla_record_id' => $employee->id, 'leave_type_id' => $sl->id,
        'date_from' => now()->subDays(10), 'date_to' => now()->subDays(3),
        'days_requested' => 7, 'filed_at' => now()->subDays(3), 'status' => 'pending',
        'document_id' => null,
    ]);

    app(LeaveViolationDetectionService::class)->scan($employee, BatchRenewalController::currentRatingPeriod());

    expect(LeaveViolation::where('violation_type', 'MISSING_SUPPORTING_DOCS')
        ->where('plantilla_record_id', $employee->id)->exists())->toBeTrue();
});

test('a disapproved application generates its own Notice of Disapproval, without double-counting AWOL/absenteeism', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $vl = LeaveType::where('code', 'VL')->first();

    LeaveApplication::create([
        'plantilla_record_id' => $employee->id, 'leave_type_id' => $vl->id,
        'date_from' => now()->subDays(10), 'date_to' => now()->subDays(9),
        'days_requested' => 1, 'filed_at' => now()->subDays(15), 'status' => 'disapproved',
        'disapproval_reason' => 'Filed without sufficient advance notice.',
    ]);

    app(LeaveViolationDetectionService::class)->scan($employee, BatchRenewalController::currentRatingPeriod());

    expect(LeaveViolation::where('violation_type', 'LEAVE_DISAPPROVED')
        ->where('plantilla_record_id', $employee->id)->count())->toBe(1);
    // A single 1-day disapproved application never reaches the AWOL/habitual
    // absenteeism thresholds on its own — confirms no double count occurred.
    expect(LeaveViolation::where('violation_type', 'AWOL')->where('plantilla_record_id', $employee->id)->exists())->toBeFalse();
});

test('re-scanning the same application does not create duplicate filing violations', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $vl = LeaveType::where('code', 'VL')->first();

    LeaveApplication::create([
        'plantilla_record_id' => $employee->id, 'leave_type_id' => $vl->id,
        'date_from' => now()->addDays(1), 'date_to' => now()->addDays(2),
        'days_requested' => 1, 'filed_at' => now(), 'status' => 'pending',
    ]);

    $detector = app(LeaveViolationDetectionService::class);
    $detector->scan($employee, BatchRenewalController::currentRatingPeriod());
    $detector->scan($employee, BatchRenewalController::currentRatingPeriod());

    expect(LeaveViolation::where('violation_type', 'LATE_FILING_VACATION_LEAVE')
        ->where('plantilla_record_id', $employee->id)->count())->toBe(1);
});

test('two separate late-filed applications in the same period each get their own violation row', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $vl = LeaveType::where('code', 'VL')->first();

    LeaveApplication::create([
        'plantilla_record_id' => $employee->id, 'leave_type_id' => $vl->id,
        'date_from' => now()->addDays(1), 'date_to' => now()->addDays(2),
        'days_requested' => 1, 'filed_at' => now(), 'status' => 'pending',
    ]);
    LeaveApplication::create([
        'plantilla_record_id' => $employee->id, 'leave_type_id' => $vl->id,
        'date_from' => now()->addDays(20), 'date_to' => now()->addDays(21),
        'days_requested' => 1, 'filed_at' => now()->addDays(19), 'status' => 'pending',
    ]);

    app(LeaveViolationDetectionService::class)->scan($employee, BatchRenewalController::currentRatingPeriod());

    expect(LeaveViolation::where('violation_type', 'LATE_FILING_VACATION_LEAVE')
        ->where('plantilla_record_id', $employee->id)->count())->toBe(2);
});

test('issuing a filing-violation notice generates the correct letter title and legal basis', function () {
    $employee = PlantillaRecord::factory()->create(['employment_status' => 'P']);
    $violation = LeaveViolation::create([
        'plantilla_record_id' => $employee->id, 'violation_type' => 'LATE_FILING_VACATION_LEAVE', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'status' => 'detected',
        'details' => ['leave_application_id' => 1, 'advance_notice_days_required' => 5, 'days_short' => 3],
    ]);

    $letters = app(LeaveViolationLetterService::class);
    expect($letters->getLetterTitle('LATE_FILING_VACATION_LEAVE'))->toBe('NOTICE OF LATE/IMPROPER FILING OF LEAVE');
    expect($letters->getLegalBasis('LATE_FILING_VACATION_LEAVE'))->toContain('Sec. 51');

    $this->actingAs($this->hrmo)->post(route('leave-violations.issue-notice', $violation))->assertRedirect();

    $violation->refresh();
    expect($violation->status)->toBe('notice_pending');
    expect($violation->document_id)->not->toBeNull();
});

test('the Notice of Disapproval cites the correct legal basis', function () {
    $letters = app(LeaveViolationLetterService::class);
    expect($letters->getLetterTitle('LEAVE_DISAPPROVED'))->toBe('NOTICE OF DISAPPROVAL OF LEAVE APPLICATION');
    expect($letters->getLegalBasis('LEAVE_DISAPPROVED'))->toContain('disapproval');
});

test('the Notice to Submit Lacking Requirement cites Sec. 53', function () {
    $letters = app(LeaveViolationLetterService::class);
    expect($letters->getLetterTitle('MISSING_SUPPORTING_DOCS'))->toBe('NOTICE TO SUBMIT LACKING REQUIREMENT');
    expect($letters->getLegalBasis('MISSING_SUPPORTING_DOCS'))->toContain('Sec. 53');
});
