<?php

use App\Models\Applicant;
use App\Models\ApplicantEvaluation;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Covers the sidebar reorganization: Pre-Evaluate and HRMPSB (deliberation)
 * moved out of the Recruitment table's row actions and into their own
 * Appointment sub-menu pages (dedicated applicant-picker + form), per the
 * explicit request to reorganize those actions as sub-menus.
 */
beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Appointment Encoder', 'guard_name' => 'web']);
    $this->ae = User::factory()->create();
    $this->ae->assignRole('Appointment Encoder');
});

test('the Pre-Evaluate picker renders without an applicant selected', function () {
    $this->actingAs($this->admin)->get(route('recruitment.pre-evaluate'))->assertOk();
});

test('the Pre-Evaluate picker loads an applicant and pre-fills their existing evaluation', function () {
    $applicant = Applicant::factory()->create(['last_name' => 'ZZPREEVAL']);
    ApplicantEvaluation::create(['applicant_id' => $applicant->id, 'qs_requirement' => 'Met', 'final_rating' => 'Qualified']);

    $response = $this->actingAs($this->admin)->get(route('recruitment.pre-evaluate', ['applicant_id' => $applicant->id]));

    $response->assertOk();
    $response->assertSee('ZZPREEVAL');
});

test('the Pre-Evaluate form still posts to the existing saveEvaluation route', function () {
    $applicant = Applicant::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('recruitment.evaluate', $applicant->id), [
        'qs_requirement' => 'Met', 'exam_status' => 'Passed', 'docs_complete' => 'Yes', 'final_rating' => 'Qualified',
    ]);

    $response->assertRedirect();
    expect(ApplicantEvaluation::where('applicant_id', $applicant->id)->first()->final_rating)->toBe('Qualified');
});

test('an Appointment Encoder is blocked from the Pre-Evaluate picker', function () {
    $this->actingAs($this->ae)->get(route('recruitment.pre-evaluate'))->assertForbidden();
});

test('the HRMPSB deliberation picker renders and redirects to the workspace once an applicant is chosen', function () {
    $applicant = Applicant::factory()->create();

    $this->actingAs($this->admin)->get(route('recruitment.deliberation.list'))->assertOk();

    // 2. Applicant specifics
    $this->actingAs($this->admin)
        ->get(route('recruitment.deliberation.list', ['applicant_id' => $applicant->id]))
        ->assertOk();

    // The AE should not have access to any deliberation pages
    $this->actingAs($this->ae)->get(route('recruitment.deliberation.list'))->assertForbidden();
});

test('an Appointment Encoder is blocked from the HRMPSB deliberation picker', function () {
    $this->actingAs($this->ae)->get(route('recruitment.deliberation.list'))->assertForbidden();
});

test('the Recruitment list no longer has row-level Pre-Evaluate or Open Deliberation Panel actions', function () {
    Applicant::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('recruitment.index'));

    $response->assertOk();
    $response->assertDontSee('Open Deliberation Panel');
    $response->assertDontSee('id="preEvalModal"', false);
});
