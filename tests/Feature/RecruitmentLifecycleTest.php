<?php

/**
 * Module 2 — Recruitment View & NAP Retention. Built against `applicants`
 * (Track B's table) with explicit user sign-off to cross that boundary,
 * after confirming is_filled/date_filled were never actually added despite
 * being in HRDMS_PHASE0_FOUNDATION.txt's own Step 1 column list. Read-side
 * only — never touches an applicant's application data, only the lifecycle
 * fields this module owns, and never hard-deletes (see
 * RecruitmentLifecycleController's class docblock for why).
 */

use App\Models\Applicant;
use App\Models\DisposalAuthorization;
use App\Models\User;
use App\Support\Recruitment\RecruitmentLifecycleService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Appointment', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'view Recruitment', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'delete Recruitment', 'guard_name' => 'web']);
    Role::where('name', 'Appointment')->first()->givePermissionTo(['view Recruitment', 'delete Recruitment']);

    $this->officer = User::factory()->create();
    $this->officer->assignRole('Appointment');
});

test('a record applied for less than a year ago computes as ACTIVE', function () {
    $applicant = Applicant::factory()->create(['applied_at' => now()->subMonths(3)]);

    expect(app(RecruitmentLifecycleService::class)->computeStage($applicant))->toBe(RecruitmentLifecycleService::STAGE_ACTIVE);
});

test('a record applied for more than a year but not filled computes as ARCHIVED', function () {
    $applicant = Applicant::factory()->create(['applied_at' => now()->subDays(400)]);

    expect(app(RecruitmentLifecycleService::class)->computeStage($applicant))->toBe(RecruitmentLifecycleService::STAGE_ARCHIVED);
});

test('a record filled more than 2 years ago computes as VALUELESS_QUEUE regardless of application age', function () {
    $applicant = Applicant::factory()->create([
        'applied_at' => now()->subYears(3),
        'is_filled' => true,
        'date_filled' => now()->subDays(731),
    ]);

    expect(app(RecruitmentLifecycleService::class)->computeStage($applicant))->toBe(RecruitmentLifecycleService::STAGE_VALUELESS_QUEUE);
});

test('a filled record less than 2 years past filling stays out of the valueless queue', function () {
    $applicant = Applicant::factory()->create([
        'applied_at' => now()->subYears(3),
        'is_filled' => true,
        'date_filled' => now()->subDays(400),
    ]);

    expect(app(RecruitmentLifecycleService::class)->computeStage($applicant))->toBe(RecruitmentLifecycleService::STAGE_ARCHIVED);
});

test('advanceAll re-stages every record that has drifted and leaves correctly-staged ones alone', function () {
    $shouldArchive = Applicant::factory()->create(['applied_at' => now()->subDays(400), 'lifecycle_stage' => 'ACTIVE']);
    $alreadyCorrect = Applicant::factory()->create(['applied_at' => now()->subDays(10), 'lifecycle_stage' => 'ACTIVE']);

    $changed = app(RecruitmentLifecycleService::class)->advanceAll();

    expect($changed)->toBe(1);
    expect($shouldArchive->fresh()->lifecycle_stage)->toBe('ARCHIVED');
    expect($alreadyCorrect->fresh()->lifecycle_stage)->toBe('ACTIVE');
});

test('the recruitment view lists only records in the requested stage', function () {
    Applicant::factory()->create(['lifecycle_stage' => 'ACTIVE', 'last_name' => 'ACTIVEONE']);
    Applicant::factory()->create(['lifecycle_stage' => 'ARCHIVED', 'last_name' => 'ARCHIVEDONE']);

    $response = $this->actingAs($this->officer)->get(route('recruitment-view.index', ['stage' => 'ARCHIVED']));

    $response->assertOk();
    $response->assertSee('ARCHIVEDONE');
    $response->assertDontSee('ACTIVEONE');
});

test('authorizing disposal on a valueless-queue record records the authorization without deleting the record', function () {
    $applicant = Applicant::factory()->create(['lifecycle_stage' => 'VALUELESS_QUEUE']);

    $response = $this->actingAs($this->officer)->post(route('recruitment-view.authorize-disposal', $applicant), [
        'nap_form_reference' => 'NAP-2026-RV-001',
    ]);

    $response->assertRedirect();
    expect(Applicant::find($applicant->id))->not->toBeNull();
    expect(DisposalAuthorization::where('disposable_type', (new Applicant())->getMorphClass())
        ->where('disposable_id', $applicant->id)
        ->where('nap_form_reference', 'NAP-2026-RV-001')
        ->exists())->toBeTrue();
});

test('authorizing disposal on a non-valueless-queue record is rejected', function () {
    $applicant = Applicant::factory()->create(['lifecycle_stage' => 'ACTIVE']);

    $this->actingAs($this->officer)->post(route('recruitment-view.authorize-disposal', $applicant), [
        'nap_form_reference' => 'NAP-2026-RV-002',
    ])->assertStatus(422);

    expect(DisposalAuthorization::count())->toBe(0);
});

test('a user without the Recruitment permission cannot view the recruitment lifecycle page', function () {
    Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');

    $this->actingAs($viewer)->get(route('recruitment-view.index'))->assertForbidden();
});
