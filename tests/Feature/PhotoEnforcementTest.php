<?php

use App\Models\Applicant;
use App\Models\PlantillaRecord;
use App\Models\User;
use App\Support\BlindScoringId;
use App\Support\PhotoEnforcementService;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');
});

test('Module 6.6: the blind scoring ID is birthdate(DDMMYY)&item number', function () {
    $applicant = Applicant::factory()->create(['date_of_birth' => '1978-08-15', 'item_no' => '0024']);

    expect(BlindScoringId::forApplicant($applicant))->toBe('150878&0024');
});

test('Module 7: an applicant with no photo has no valid photo', function () {
    $applicant = Applicant::factory()->create(['photo_url' => null]);
    expect(app(PhotoEnforcementService::class)->hasValidPhoto($applicant))->toBeFalse();
});

test('Module 7: a manually uploaded photo is immediately valid', function () {
    $applicant = Applicant::factory()->create();
    app(PhotoEnforcementService::class)->recordManualUpload($applicant, '/photos/juan.jpg');

    expect(app(PhotoEnforcementService::class)->hasValidPhoto($applicant->fresh()))->toBeTrue();
});

test('Module 7: a 201-file imported photo is NOT valid until confirmed', function () {
    $record = PlantillaRecord::factory()->create(['profile_picture' => '/201/juan.jpg']);
    $applicant = Applicant::factory()->create(['last_name' => $record->last_name, 'first_name' => $record->first_name]);

    $service = app(PhotoEnforcementService::class);
    $candidate = $service->findMirrorCandidate($applicant);
    expect($candidate)->not->toBeNull();

    $service->importFrom201File($applicant, $candidate);
    expect($service->hasValidPhoto($applicant->fresh()))->toBeFalse();

    $service->confirmImportedPhoto($applicant);
    expect($service->hasValidPhoto($applicant->fresh()))->toBeTrue();
});

test('Module 7: TWG scoring submission is blocked without a valid photo', function () {
    $applicant = Applicant::factory()->create(['photo_url' => null]);

    $response = $this->actingAs($this->admin)->post(route('recruitment.hrmpsb.save_score', $applicant->id), [
        'position_category' => 'eligibility',
    ]);

    $response->assertSessionHasErrors('photo');
});

test('Module 7: interview evaluation submission is blocked without a valid photo', function () {
    $applicant = Applicant::factory()->create(['photo_url' => null]);

    $response = $this->actingAs($this->admin)->post(route('recruitment.hrmpsb.interview.store'), [
        'applicant_id' => $applicant->id,
        'ratings' => ['1' => 5],
    ]);

    $response->assertSessionHasErrors('photo');
});
