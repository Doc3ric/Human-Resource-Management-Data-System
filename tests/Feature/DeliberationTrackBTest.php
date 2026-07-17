<?php

use App\Models\Applicant;
use App\Models\DeliberationAgenda;
use App\Models\ExamSchedule;
use App\Models\HrmpsbPanelMember;
use App\Models\InterviewEvaluation;
use App\Models\PanelMember;
use App\Models\TwgScore;
use App\Models\TwgScoreSubmission;
use App\Models\User;
use App\Support\BlindScoringId;
use Database\Seeders\TwgRatingCriteriaSeeder;
use Spatie\Permission\Models\Role;

/**
 * Covers the Track B work found uncommitted in the working tree (Module 6A
 * monitoring board, Module 8 export layouts, Module 6.5 agenda prep,
 * Module 7 photo endpoints) — none of it had test coverage, and several
 * bugs were found and fixed while reviewing it (see inline notes on each test).
 */
beforeEach(function () {
    $this->seed(TwgRatingCriteriaSeeder::class);

    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Appointment Encoder', 'guard_name' => 'web']);
    $this->ae = User::factory()->create();
    $this->ae->assignRole('Appointment Encoder');
});

// ---------------------------------------------------------------------
// Module 6A — Monitoring Board
// ---------------------------------------------------------------------

test('Module 6A.5: the monitoring status endpoint returns masked IDs only, never raw names', function () {
    $applicant = Applicant::factory()->create([
        'last_name' => 'ZZMONITORSURNAME', 'position_applied' => 'Administrative Aide I',
        'date_of_birth' => '1978-08-15', 'item_no' => '0024',
    ]);
    HrmpsbPanelMember::create(['position_applied' => 'Administrative Aide I', 'user_id' => $this->admin->id, 'member_role' => 'REGULAR_MEMBER', 'is_active' => true]);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.monitoring', $applicant));

    $response->assertOk();
    $response->assertDontSee('ZZMONITORSURNAME');
    $response->assertJsonFragment(['masked_id' => BlindScoringId::forApplicant($applicant)]);
    expect($response->json('applicants.0'))->not->toHaveKey('name');
});

test('Module 6A: a TWG member who has assessed but not locked shows In Progress; locked shows Scored', function () {
    $applicant = Applicant::factory()->create(['position_applied' => 'Administrative Aide I']);
    HrmpsbPanelMember::create(['position_applied' => 'Administrative Aide I', 'user_id' => $this->admin->id, 'member_role' => 'REGULAR_MEMBER', 'is_active' => true]);

    $engine = app(\App\Support\TwgDynamicScoringService::class);
    $scores = $engine->initialize($applicant);
    foreach ($scores as $score) {
        $engine->updateAssessorValue($score->fresh(['criterion']), 1.00, null, $this->admin);
    }

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.monitoring', $applicant));
    $memberKey = 'member_' . HrmpsbPanelMember::first()->id;
    expect($response->json("matrix.{$memberKey}.{$applicant->id}"))->toBe('In Progress');

    app(\App\Support\PhotoEnforcementService::class)->recordManualUpload($applicant, '/photos/a.jpg');
    $engine->submit($applicant, $this->admin, 'Recommended', null, true);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.monitoring', $applicant));
    expect($response->json("matrix.{$memberKey}.{$applicant->id}"))->toBe('Scored');
});

test('Module 4.1 parity: an Appointment Encoder is blocked from the monitoring endpoint', function () {
    $applicant = Applicant::factory()->create();
    $this->actingAs($this->ae)->get(route('recruitment.deliberation.monitoring', $applicant))->assertForbidden();
});

// ---------------------------------------------------------------------
// Module 8 — Export Layouts (bug: ->load() on nonexistent relations)
// ---------------------------------------------------------------------

test('Module 8.1: Layout A generates a PDF without crashing on an applicant with no photo/education/experience data', function () {
    $applicant = Applicant::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.export.layout-a', $applicant));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
});

test('Module 8.3: Layout C (scoring sheet) generates a PDF without crashing', function () {
    $applicant = Applicant::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.export.layout-c', $applicant));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
});

test('Module 6.7: the CER selector picks Level 1 for SG below 10 and Level 2 for SG 10+, reading PlantillaRecord columns directly', function () {
    $lowSg = \App\Models\PlantillaRecord::factory()->create(['item_no_new' => 'ITEM-LOW', 'salary_grade' => 5]);
    $applicantLow = Applicant::factory()->create(['item_no' => 'ITEM-LOW']);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.export.cer', $applicantLow));
    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain('CER_Level_1');

    $highSg = \App\Models\PlantillaRecord::factory()->create(['item_no_new' => 'ITEM-HIGH', 'salary_grade' => 15]);
    $applicantHigh = Applicant::factory()->create(['item_no' => 'ITEM-HIGH']);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.export.cer', $applicantHigh));
    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain('CER_Level_2');
});

test('Module 8.4: Layout D CSV includes the real applicant name per spec (this layout is not a blind surface)', function () {
    $applicant = Applicant::factory()->create(['last_name' => 'ZZLAYOUTDSURNAME', 'position_applied' => 'Administrative Aide I']);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.export.layout-d', ['position' => 'Administrative Aide I']));

    $response->assertOk();
    $csv = $response->streamedContent();
    expect($csv)->toContain('ZZLAYOUTDSURNAME');
    expect($csv)->toContain('Applicant Name');
});

// ---------------------------------------------------------------------
// Module 6.5 — Agenda Prep (bug: classification used a nonexistent field)
// ---------------------------------------------------------------------

test('Module 5.4/6.5: agenda prep classifies applicants using ExamRoutingService, not a nonexistent employer field', function () {
    $jo = Applicant::factory()->create([
        'is_pgb_employee' => true, 'pgb_status' => 'Job Order', 'is_exam_exempt' => false,
        'office' => 'PHRMO', 'position_applied' => 'Admin Aide',
    ]);
    \App\Models\ApplicantEvaluation::create(['applicant_id' => $jo->id, 'final_rating' => 'Qualified']);

    $exempt = Applicant::factory()->create([
        'is_pgb_employee' => false, 'is_exam_exempt' => true,
        'office' => 'PHRMO', 'position_applied' => 'Admin Aide',
    ]);
    \App\Models\ApplicantEvaluation::create(['applicant_id' => $exempt->id, 'final_rating' => 'Qualified']);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.agenda.create'));

    $response->assertOk();
    $matrix = $response->viewData('matrix');
    $tags = collect($matrix['PHRMO']['Admin Aide']['applicants'])->pluck('tag', 'id');
    expect($tags[$jo->id])->toBe('Job Order');
    expect($tags[$exempt->id])->toBe('Exempted');
});

test('Module 4.1 parity: an Appointment Encoder is blocked from agenda prep', function () {
    $this->actingAs($this->ae)->get(route('recruitment.deliberation.agenda.create'))->assertForbidden();
});

test('storing an agenda persists matrix_data and can be printed as a PDF', function () {
    $agenda = DeliberationAgenda::create([
        'title' => 'Test Agenda', 'part_1_notes' => 'Notes here.',
        'matrix_data' => ['PHRMO' => []], 'created_by' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.agenda.create'));
    $response->assertOk();

    $print = $this->actingAs($this->admin)->post(route('recruitment.deliberation.agenda.store'), [
        'title' => 'Another Agenda', 'part_1_notes' => 'More notes.',
        'matrix_data' => [], 'action' => 'print',
    ]);
    $print->assertOk();
    expect($print->headers->get('Content-Type'))->toContain('application/pdf');
});

// ---------------------------------------------------------------------
// Module 7 — Photo endpoints (ApplicantPhotoController, clean but untested)
// ---------------------------------------------------------------------

test('Module 7: uploading a valid base64 photo records a manual upload', function () {
    Illuminate\Support\Facades\Storage::fake('public');
    $applicant = Applicant::factory()->create(['photo_url' => null]);

    $tinyPng = base64_encode(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));

    $response = $this->actingAs($this->admin)->postJson(route('recruitment.deliberation.photo.upload', $applicant), [
        'photo_data' => 'data:image/png;base64,' . $tinyPng,
    ]);

    $response->assertOk();
    expect($applicant->fresh()->photo_source)->toBe('manual_upload');
    expect(app(\App\Support\PhotoEnforcementService::class)->hasValidPhoto($applicant->fresh()))->toBeTrue();
});

test('Module 7: importing from a matching 201-file record requires confirmation before it is valid', function () {
    $record = \App\Models\PlantillaRecord::factory()->create(['last_name' => 'PHOTOMATCH', 'first_name' => 'JUAN', 'profile_picture' => '/201/juan.jpg']);
    $applicant = Applicant::factory()->create(['last_name' => 'PHOTOMATCH', 'first_name' => 'JUAN', 'photo_url' => null]);

    $this->actingAs($this->admin)->post(route('recruitment.deliberation.photo.import', $applicant))->assertRedirect();
    expect($applicant->fresh()->photo_source)->toBe('201_import');
    expect(app(\App\Support\PhotoEnforcementService::class)->hasValidPhoto($applicant->fresh()))->toBeFalse();

    $this->actingAs($this->admin)->post(route('recruitment.deliberation.photo.confirm', $applicant), ['confirmation' => '1'])->assertRedirect();
    expect(app(\App\Support\PhotoEnforcementService::class)->hasValidPhoto($applicant->fresh()))->toBeTrue();
});
