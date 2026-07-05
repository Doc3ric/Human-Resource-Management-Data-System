<?php

use App\Models\Applicant;
use App\Models\InterviewEvaluation;
use App\Models\User;
use App\Support\BlindScoringId;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');
});

test('Module 6.6: TWG scoring panel header uses the blind ID, not the raw surname', function () {
    // This page also renders the Module 5 profile/identity panel alongside the
    // scoring panel, which per spec 5.3 correctly keeps showing the real name —
    // only the scoring panel itself must never show it.
    $applicant = Applicant::factory()->create([
        'last_name' => 'ZZBLINDSURNAME',
        'date_of_birth' => '1978-08-15',
        'item_no' => '0024',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('recruitment.hrmpsb.twg.create', ['applicant_id' => $applicant->id]));

    $response->assertOk();
    $response->assertSee('TWG Rating — ' . BlindScoringId::forApplicant($applicant));
});

test('Module 6.6: interview evaluation panel header uses the blind ID, not the raw surname', function () {
    // Same as above: the profile/identity panel on this page is allowed to show
    // the real name; only the scoring panel header is required to be blind.
    $applicant = Applicant::factory()->create([
        'last_name' => 'ZZBLINDSURNAME',
        'date_of_birth' => '1978-08-15',
        'item_no' => '0024',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('recruitment.hrmpsb.interview.create', ['applicant_id' => $applicant->id]));

    $response->assertOk();
    $response->assertSee('Interview Evaluation for ' . BlindScoringId::forApplicant($applicant));
});

test('Module 6.6: interview evaluation list (scoring table) shows the blind ID, never the raw surname', function () {
    $applicant = Applicant::factory()->create([
        'last_name' => 'ZZBLINDSURNAME',
        'date_of_birth' => '1978-08-15',
        'item_no' => '0024',
    ]);

    InterviewEvaluation::create([
        'applicant_id' => $applicant->id,
        'rater_id' => $this->admin->id,
        'rater_name' => $this->admin->name,
        'ratings' => ['appearance_1' => 5],
        'total_score' => 80,
    ]);

    $response = $this->actingAs($this->admin)->get(route('recruitment.hrmpsb.interview.index'));

    $response->assertOk();
    $response->assertDontSee('ZZBLINDSURNAME');
    $response->assertSee(BlindScoringId::forApplicant($applicant));
});

test('Module 6.6: interview scoring matrix shows the blind ID, never the raw surname', function () {
    $applicant = Applicant::factory()->create([
        'last_name' => 'ZZBLINDSURNAME',
        'date_of_birth' => '1978-08-15',
        'item_no' => '0024',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('recruitment.hrmpsb.interview.matrix', ['applicant_id' => $applicant->id]));

    $response->assertOk();
    $response->assertDontSee('ZZBLINDSURNAME');
    $response->assertSee(BlindScoringId::forApplicant($applicant));
});

test('Module 8.4: the Comparative Assessment Matrix still shows real applicant names (spec requires this, not blind IDs)', function () {
    $applicant = Applicant::factory()->create([
        'last_name' => 'ZZCOMPARATIVESURNAME',
        'date_of_birth' => '1978-08-15',
        'item_no' => '0024',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('recruitment.hrmpsb.comparative_report'));

    $response->assertOk();
    $response->assertSee('ZZCOMPARATIVESURNAME');
});
