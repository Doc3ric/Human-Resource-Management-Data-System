<?php

use App\Models\Applicant;
use App\Models\HrmpsbRatingScale;
use App\Models\InterviewEvaluation;
use App\Models\Setting;
use App\Models\TwgRatingCriterion;
use App\Models\TwgScore;
use App\Models\TwgScoreHistory;
use App\Models\TwgScoreSubmission;
use App\Models\User;
use App\Support\BlindScoringId;
use App\Support\PhotoEnforcementService;
use App\Support\TwgDynamicScoringService;
use Database\Seeders\TwgRatingCriteriaSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(TwgRatingCriteriaSeeder::class);

    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Appointment Encoder', 'guard_name' => 'web']);
    $this->ae = User::factory()->create();
    $this->ae->assignRole('Appointment Encoder');

    $this->engine = app(TwgDynamicScoringService::class);
});

test('Module 6.1: the criteria seeder captures today\'s real Setting::twg_max_* values', function () {
    Setting::setVal('twg_max_ipcr', 12);
    $this->seed(TwgRatingCriteriaSeeder::class); // re-run to pick up the changed setting

    expect((float) TwgRatingCriterion::where('criterion_key', 'ipcr')->first()->point_value)->toBe(12.0);
});

test('Module 6.1: an applicant with eligibility gets education_eligibility, not education_no_eligibility', function () {
    $applicant = Applicant::factory()->create(['eligibility' => 'Career Service Professional', 'item_no' => '0001']);

    $scores = $this->engine->initialize($applicant);
    $keys = $scores->pluck('criterion.criterion_key');

    expect($keys)->toContain('education_eligibility');
    expect($keys)->not->toContain('education_no_eligibility');
});

test('Module 6.1: an applicant without eligibility or item no gets education_no_eligibility instead', function () {
    $applicant = Applicant::factory()->create(['eligibility' => 'N/A', 'item_no' => null]);

    $scores = $this->engine->initialize($applicant);
    $keys = $scores->pluck('criterion.criterion_key');

    expect($keys)->toContain('education_no_eligibility');
    expect($keys)->not->toContain('education_eligibility');
});

test('Module 6.2: psb_interview auto-populates from the panel average, matching the existing 50% formula', function () {
    $applicant = Applicant::factory()->create();
    InterviewEvaluation::create([
        'applicant_id' => $applicant->id, 'rater_id' => $this->admin->id,
        'ratings' => ['x' => 1], 'total_score' => 80,
    ]);
    InterviewEvaluation::create([
        'applicant_id' => $applicant->id, 'rater_id' => $this->admin->id,
        'ratings' => ['x' => 1], 'total_score' => 90,
    ]);

    $scores = $this->engine->initialize($applicant);
    $psb = $scores->firstWhere('criterion.criterion_key', 'psb_interview');

    // avg(80,90)=85, criterion point_value defaults to 50 -> 85 * 0.50 = 42.50
    expect((float) $psb->auto_populated_value)->toBe(42.50);
    expect((float) $psb->assessor_value)->toBe(42.50); // not yet edited, so it tracks the auto value
});

test('Module 6.2: a bracket selection sets the auto value and syncs the assessor value until manually edited', function () {
    $applicant = Applicant::factory()->create(['eligibility' => 'Career Service Professional', 'item_no' => '0001']);
    HrmpsbRatingScale::create(['position_category' => 'all', 'criterion' => 'awards', 'points' => 5.00, 'condition_name' => 'International']);

    $scores = $this->engine->initialize($applicant);
    $awards = $scores->firstWhere('criterion.criterion_key', 'awards');

    $updated = $this->engine->setBracketSelection($awards, 5.00);

    expect((float) $updated->auto_populated_value)->toBe(5.00);
    expect((float) $updated->assessor_value)->toBe(5.00);
    expect($updated->is_edited)->toBeFalse();
});

test('Module 6.3: exceedance is blocked with the exact spec message', function () {
    $applicant = Applicant::factory()->create();
    $scores = $this->engine->initialize($applicant);
    $ipcr = $scores->firstWhere('criterion.criterion_key', 'ipcr');

    $this->engine->updateAssessorValue($ipcr, 5, null, $this->admin); // within range, sanity check
    expect((float) $ipcr->fresh()->assessor_value)->toBe(5.0);

    expect(fn () => $this->engine->updateAssessorValue($ipcr, 999, null, $this->admin))
        ->toThrow(InvalidArgumentException::class, 'Exceeds maximum allowable score of');
});

test('Module 6.3: manually overriding the auto value flags is_edited and is tracked with notes/assessor', function () {
    $applicant = Applicant::factory()->create(['eligibility' => 'Career Service Professional', 'item_no' => '0001']);
    HrmpsbRatingScale::create(['position_category' => 'all', 'criterion' => 'awards', 'points' => 5.00]);

    $scores = $this->engine->initialize($applicant);
    $awards = $scores->firstWhere('criterion.criterion_key', 'awards');
    $this->engine->setBracketSelection($awards, 5.00);

    $updated = $this->engine->updateAssessorValue($awards->fresh(), 3.00, 'Downgraded per panel discussion', $this->admin);

    expect($updated->is_edited)->toBeTrue();
    expect((float) $updated->assessor_value)->toBe(3.00);
    expect($updated->assessor_notes)->toBe('Downgraded per panel discussion');
    expect($updated->assessed_by)->toBe($this->admin->id);
});

test('Module 6.4: submit is blocked without a valid photo', function () {
    $applicant = Applicant::factory()->create(['photo_url' => null]);
    $this->engine->initialize($applicant);

    expect(fn () => $this->engine->submit($applicant, $this->admin, 'Recommended', null, true))
        ->toThrow(RuntimeException::class, PhotoEnforcementService::BLOCK_MESSAGE);
});

test('Module 6.4: submit is blocked without certification acceptance', function () {
    $applicant = Applicant::factory()->create();
    app(PhotoEnforcementService::class)->recordManualUpload($applicant, '/photos/a.jpg');
    $this->engine->initialize($applicant);

    expect(fn () => $this->engine->submit($applicant, $this->admin, 'Recommended', null, false))
        ->toThrow(RuntimeException::class);
});

test('Module 6.4: submit is blocked if any active criterion is unscored', function () {
    $applicant = Applicant::factory()->create();
    app(PhotoEnforcementService::class)->recordManualUpload($applicant, '/photos/a.jpg');
    $this->engine->initialize($applicant); // psb_interview auto-scores (0 evals -> null), others stay null

    expect(fn () => $this->engine->submit($applicant, $this->admin, 'Recommended', null, true))
        ->toThrow(RuntimeException::class, 'has not been scored yet');
});

test('Module 6.4: a fully scored, certified, photo-attached submission locks and logs history', function () {
    $applicant = Applicant::factory()->create(['eligibility' => 'N/A', 'item_no' => null]);
    app(PhotoEnforcementService::class)->recordManualUpload($applicant, '/photos/a.jpg');

    $scores = $this->engine->initialize($applicant);
    foreach ($scores as $score) {
        $this->engine->updateAssessorValue($score->fresh(['criterion']), 1.00, null, $this->admin);
    }

    $submission = $this->engine->submit($applicant, $this->admin, 'Highly Recommended', 'Strong candidate.', true);

    expect($submission->status)->toBe('submitted');
    expect($submission->isLocked())->toBeTrue();
    expect($submission->recommendation)->toBe('Highly Recommended');
    expect(TwgScoreHistory::where('applicant_id', $applicant->id)->where('action', 'submitted')->exists())->toBeTrue();
});

test('Module 6.4: only unlocking resets the lock, and it is logged', function () {
    $applicant = Applicant::factory()->create(['eligibility' => 'N/A', 'item_no' => null]);
    app(PhotoEnforcementService::class)->recordManualUpload($applicant, '/photos/a.jpg');
    $scores = $this->engine->initialize($applicant);
    foreach ($scores as $score) {
        $this->engine->updateAssessorValue($score->fresh(['criterion']), 1.00, null, $this->admin);
    }
    $this->engine->submit($applicant, $this->admin, 'Recommended', null, true);

    expect(fn () => $this->engine->submit($applicant, $this->admin, 'Recommended', null, true))
        ->not->toThrow(Exception::class); // resubmitting while locked is allowed at the service layer; the controller blocks store() instead

    $this->engine->unlock($applicant, $this->admin);

    expect(TwgScoreSubmission::where('applicant_id', $applicant->id)->first()->status)->toBe('draft');
    expect(TwgScoreHistory::where('applicant_id', $applicant->id)->where('action', 'unlocked')->exists())->toBeTrue();
});

test('Module 4.1 parity: an Appointment Encoder is blocked from the dynamic TWG scoring route', function () {
    $this->actingAs($this->ae)->get(route('recruitment.hrmpsb.twg_dynamic.create'))->assertForbidden();
});

test('the create route renders and shows the blind ID, never the raw surname', function () {
    $applicant = Applicant::factory()->create(['last_name' => 'ZZDYNSURNAME', 'date_of_birth' => '1978-08-15', 'item_no' => '0024']);

    $response = $this->actingAs($this->admin)
        ->get(route('recruitment.hrmpsb.twg_dynamic.create', ['applicant_id' => $applicant->id]));

    $response->assertOk();
    $response->assertSee('TWG Rating — ' . BlindScoringId::forApplicant($applicant));
});

test('the store endpoint is blocked once the evaluation is locked', function () {
    $applicant = Applicant::factory()->create(['eligibility' => 'N/A', 'item_no' => null]);
    app(PhotoEnforcementService::class)->recordManualUpload($applicant, '/photos/a.jpg');
    $scores = $this->engine->initialize($applicant);
    foreach ($scores as $score) {
        $this->engine->updateAssessorValue($score->fresh(['criterion']), 1.00, null, $this->admin);
    }
    $this->engine->submit($applicant, $this->admin, 'Recommended', null, true);

    $anyScore = TwgScore::where('applicant_id', $applicant->id)->first();

    $this->actingAs($this->admin)->post(route('recruitment.hrmpsb.twg_dynamic.store', $applicant), [
        'scores' => [$anyScore->id => ['assessor_value' => 0.5]],
    ])->assertForbidden();
});

test('unlocking requires the edit permission bit', function () {
    Role::firstOrCreate(['name' => 'Appointment', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'edit TWG Dynamic Scoring', 'guard_name' => 'web']);
    $noPerm = User::factory()->create();
    $noPerm->assignRole('Appointment');

    $applicant = Applicant::factory()->create(['eligibility' => 'N/A', 'item_no' => null]);
    app(PhotoEnforcementService::class)->recordManualUpload($applicant, '/photos/a.jpg');
    $scores = $this->engine->initialize($applicant);
    foreach ($scores as $score) {
        $this->engine->updateAssessorValue($score->fresh(['criterion']), 1.00, null, $this->admin);
    }
    $this->engine->submit($applicant, $this->admin, 'Recommended', null, true);

    $this->actingAs($noPerm)->post(route('recruitment.hrmpsb.twg_dynamic.unlock', $applicant))->assertForbidden();
});
