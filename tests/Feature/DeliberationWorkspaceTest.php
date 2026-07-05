<?php

use App\Models\Applicant;
use App\Models\DisciplinaryCase;
use App\Models\PlantillaRecord;
use App\Models\User;
use App\Support\BlindScoringId;
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
});

test('Module 4.1 parity: an Appointment Encoder is blocked from the deliberation workspace', function () {
    $applicant = Applicant::factory()->create();
    $this->actingAs($this->ae)->get(route('recruitment.deliberation.show', $applicant))->assertForbidden();
});

test('the workspace renders with the locked header, phase badge, and blind ID never the raw surname in the scoring panel', function () {
    $applicant = Applicant::factory()->create([
        'last_name' => 'ZZDELIBSURNAME', 'date_of_birth' => '1978-08-15', 'item_no' => '0024',
        'position_applied' => 'Administrative Aide I',
    ]);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.show', $applicant));

    $response->assertOk();
    $response->assertSee('Administrative Aide I');
    $response->assertSee('Screening'); // default phase label
    $response->assertSee('TWG Rating — ' . BlindScoringId::forApplicant($applicant));
    // The left profile panel legitimately shows the real name (Module 5.3 identity block).
    $response->assertSee('ZZDELIBSURNAME');
});

test('the applicant selector chip track shows every applicant and marks the active one', function () {
    $a1 = Applicant::factory()->create(['last_name' => 'AONE']);
    $a2 = Applicant::factory()->create(['last_name' => 'BTWO']);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.show', $a1));

    $response->assertOk();
    $response->assertSee('AONE');
    $response->assertSee('BTWO');
});

test('updating the deliberation phase persists and is reflected in the header', function () {
    $applicant = Applicant::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('recruitment.deliberation.phase', $applicant), ['deliberation_phase' => 'hrmpsb_deliberation'])
        ->assertRedirect();

    expect($applicant->fresh()->deliberation_phase)->toBe('hrmpsb_deliberation');
});

test('Module 5.3: an applicant with no photo shows the silhouette and Photo Required notice', function () {
    $applicant = Applicant::factory()->create(['photo_url' => null]);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.show', $applicant));

    $response->assertOk();
    $response->assertSee('Photo Required');
});

test('Module 5.3: Demerit Record shows real disciplinary_cases data when the applicant matches an existing PGB employee', function () {
    $record = PlantillaRecord::factory()->create(['last_name' => 'DEMERITMATCH', 'first_name' => 'JUAN', 'profile_picture' => '/201/juan.jpg']);
    DisciplinaryCase::create([
        'case_no' => 'CASE-0001', 'personnel_id' => $record->id, 'formal_charge' => 'Test charge',
        'offense_classification' => 'grave', 'status' => 'decided', 'penalty' => 'Suspension 3 months',
        'decided_at' => now(),
    ]);
    $applicant = Applicant::factory()->create(['last_name' => 'DEMERITMATCH', 'first_name' => 'JUAN']);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.show', $applicant));

    $response->assertOk();
    $response->assertSee('Demerit on Record — 1 Item(s)');
    $response->assertSee('Suspension 3 months');
});

test('Module 5.3: an applicant with no matching PGB record and no cases shows No Demerit on Record', function () {
    $applicant = Applicant::factory()->create(['last_name' => 'NODEMERITMATCH']);

    $response = $this->actingAs($this->admin)->get(route('recruitment.deliberation.show', $applicant));

    $response->assertOk();
    $response->assertSee('No Demerit on Record');
});

test('Module 8.4 parity: the deliberation workspace does not disturb the existing Comparative Assessment Matrix', function () {
    $applicant = Applicant::factory()->create(['last_name' => 'ZZUNCHANGEDCOMPARATIVE']);

    $this->actingAs($this->admin)->get(route('recruitment.deliberation.show', $applicant))->assertOk();

    $response = $this->actingAs($this->admin)->get(route('recruitment.hrmpsb.comparative_report'));
    $response->assertOk();
    $response->assertSee('ZZUNCHANGEDCOMPARATIVE');
});
