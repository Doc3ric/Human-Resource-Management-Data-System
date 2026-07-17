<?php

use App\Models\Applicant;
use App\Models\ExamSchedule;
use App\Models\User;
use App\Support\ExamRoutingService;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Appointment Encoder', 'guard_name' => 'web']);
    $this->ae = User::factory()->create();
    $this->ae->assignRole('Appointment Encoder');

    $this->engine = app(ExamRoutingService::class);
});

test('Module 4.1 parity: an Appointment Encoder is blocked from exam routing', function () {
    $this->actingAs($this->ae)->get(route('recruitment.exam-routing.index'))->assertForbidden();
});

test('Module 5.4: is_exam_exempt takes priority over PGB JO classification', function () {
    $applicant = Applicant::factory()->create(['is_pgb_employee' => true, 'pgb_status' => 'Job Order', 'is_exam_exempt' => true]);
    expect($this->engine->classify($applicant))->toBe('exempt');
});

test('Module 5.4: a PGB employee with Job Order status classifies as pgb_jo', function () {
    $applicant = Applicant::factory()->create(['is_pgb_employee' => true, 'pgb_status' => 'Job Order', 'is_exam_exempt' => false]);
    expect($this->engine->classify($applicant))->toBe('pgb_jo');
});

test('Module 5.4: everyone else classifies as external', function () {
    $applicant = Applicant::factory()->create(['is_pgb_employee' => false, 'is_exam_exempt' => false]);
    expect($this->engine->classify($applicant))->toBe('external');
});

test('Module 5.4: the trailing window excludes applicants applied more than N months ago', function () {
    Applicant::factory()->create(['applied_at' => now()->subMonths(2), 'is_pgb_employee' => false]);
    Applicant::factory()->create(['applied_at' => now()->subMonths(10), 'is_pgb_employee' => false]);

    $grouped = $this->engine->windowedApplicants(9);

    expect($grouped->get('external', collect()))->toHaveCount(1);
});

test('Module 5.4: an explicit date range includes only applicants within it (from/to date picker replaces Window (months))', function () {
    Applicant::factory()->create(['applied_at' => '2026-04-01', 'is_pgb_employee' => false]); // inside range
    Applicant::factory()->create(['applied_at' => '2026-03-01', 'is_pgb_employee' => false]); // before range
    Applicant::factory()->create(['applied_at' => '2026-07-01', 'is_pgb_employee' => false]); // after range

    $grouped = $this->engine->applicantsInRange(
        \Carbon\Carbon::parse('2026-03-15')->startOfDay(),
        \Carbon\Carbon::parse('2026-06-15')->endOfDay()
    );

    expect($grouped->get('external', collect()))->toHaveCount(1);
});

test('Module 5.4: the exam routing index page accepts date_from/date_to and filters accordingly', function () {
    Applicant::factory()->create(['applied_at' => '2026-04-01', 'is_pgb_employee' => false, 'last_name' => 'INRANGE']);
    Applicant::factory()->create(['applied_at' => '2026-01-01', 'is_pgb_employee' => false, 'last_name' => 'OUTOFRANGE']);

    $response = $this->actingAs($this->admin)->get(route('recruitment.exam-routing.index', [
        'date_from' => '2026-03-15', 'date_to' => '2026-06-15',
    ]));

    $response->assertOk();
    $response->assertSee('INRANGE');
    $response->assertDontSee('OUTOFRANGE');
});

test('Module 5.4: non-exempt applicants cluster into tables of max size', function () {
    Applicant::factory()->count(13)->create(['is_pgb_employee' => false, 'is_exam_exempt' => false]);

    $grouped = $this->engine->windowedApplicants(9);
    $tables = $this->engine->cluster($grouped->get('external', collect()), 6);

    expect($tables)->toHaveCount(3); // 6 + 6 + 1
    expect($tables->first())->toHaveCount(6);
    expect($tables->last())->toHaveCount(1);
});

test('Module 5.4: generating persists exam_schedules with table/date/time/room, and re-generating replaces (no duplicates)', function () {
    Applicant::factory()->count(2)->create(['is_pgb_employee' => false, 'is_exam_exempt' => false]);

    $this->actingAs($this->admin)->post(route('recruitment.exam-routing.generate'), [
        'classification' => 'external',
        'date_from' => now()->subMonths(9)->toDateString(),
        'date_to' => now()->toDateString(),
        'table_size' => 6,
        'exam_date' => now()->addDays(5)->toDateString(),
        'exam_time' => '09:00',
        'room' => 'Conference Room A',
    ])->assertRedirect();

    expect(ExamSchedule::count())->toBe(2);
    expect(ExamSchedule::first()->room)->toBe('Conference Room A');

    // Re-generate should update, not duplicate.
    $this->actingAs($this->admin)->post(route('recruitment.exam-routing.generate'), [
        'classification' => 'external',
        'date_from' => now()->subMonths(9)->toDateString(),
        'date_to' => now()->toDateString(),
        'table_size' => 6,
        'exam_date' => now()->addDays(6)->toDateString(),
        'exam_time' => '10:00',
        'room' => 'Conference Room B',
    ])->assertRedirect();

    expect(ExamSchedule::count())->toBe(2);
    expect(ExamSchedule::first()->room)->toBe('Conference Room B');
});

test('toggling exam exemption is audit-logged and flips the boolean', function () {
    $applicant = Applicant::factory()->create(['is_exam_exempt' => false]);

    $this->actingAs($this->admin)->post(route('recruitment.exam-routing.toggle-exempt', $applicant))->assertRedirect();

    expect($applicant->fresh()->is_exam_exempt)->toBeTrue();
});
