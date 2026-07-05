<?php

use App\Models\Applicant;
use App\Models\PlantillaRecord;
use App\Models\User;
use App\Support\PlantillaSyncValidator;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');
});

test('vacant positions dropdown pulls only active vacant plantilla records', function () {
    $vacant = PlantillaRecord::factory()->create(['is_vacant' => true, 'abolished' => false, 'position_title' => 'NURSE II']);
    $filled = PlantillaRecord::factory()->create(['is_vacant' => false, 'position_title' => 'NURSE III']);
    $abolishedVacant = PlantillaRecord::factory()->create(['is_vacant' => true, 'abolished' => true, 'position_title' => 'CLERK I']);

    $response = $this->actingAs($this->admin)->get(route('recruitment.create'));

    $response->assertOk();
    $response->assertSee('NURSE II');
    $response->assertDontSee('NURSE III');
    $response->assertDontSee('CLERK I');
});

test('a new applicant snapshots the master plantilla salary grade at creation', function () {
    PlantillaRecord::factory()->create([
        'is_vacant' => true, 'position_title' => 'NURSE II', 'office_department' => 'PHRMO',
        'item_no_new' => 'ITEM-001', 'salary_grade' => '15',
    ]);

    $this->actingAs($this->admin)->post(route('recruitment.store'), [
        'last_name' => 'DELA CRUZ', 'first_name' => 'JUAN', 'sex' => 'Male',
        'date_of_birth' => '1990-01-01', 'address' => 'Bukidnon',
        'phone_number' => '09171234567', 'email_address' => 'juan@example.com',
        'position_applied' => 'NURSE II', 'item_no' => 'ITEM-001', 'office' => 'PHRMO',
        'highest_educational_attainment' => 'College Graduate',
        'application_letter_override' => '1',
        'application_letter_override_reason' => 'Test fixture — no attachment needed for this assertion.',
    ])->assertRedirect();

    $applicant = Applicant::where('email_address', 'juan@example.com')->first();
    expect($applicant->salary_grade_snapshot)->toBe('15');
});

test('the sync validator detects a salary grade change since the applicant snapshot', function () {
    $plantilla = PlantillaRecord::factory()->create([
        'position_title' => 'NURSE II', 'office_department' => 'PHRMO', 'item_no_new' => 'ITEM-002', 'salary_grade' => '15',
    ]);
    $applicant = Applicant::factory()->create([
        'position_applied' => 'NURSE II', 'office' => 'PHRMO', 'item_no' => 'ITEM-002', 'salary_grade_snapshot' => '15',
    ]);

    expect((new PlantillaSyncValidator())->checkSync($applicant))->toBeNull();

    $plantilla->update(['salary_grade' => '16']); // Master Plantilla changed

    expect((new PlantillaSyncValidator())->checkSync($applicant))->toBe(PlantillaSyncValidator::MISMATCH_MESSAGE);
});

test('updating an applicant whose plantilla item drifted is blocked', function () {
    PlantillaRecord::factory()->create([
        'position_title' => 'NURSE II', 'office_department' => 'PHRMO', 'item_no_new' => 'ITEM-003', 'salary_grade' => '15',
    ]);
    $applicant = Applicant::factory()->create([
        'position_applied' => 'NURSE II', 'office' => 'PHRMO', 'item_no' => 'ITEM-003', 'salary_grade_snapshot' => '11', // already stale
    ]);

    $response = $this->actingAs($this->admin)->put(route('recruitment.update', $applicant->id), [
        'last_name' => $applicant->last_name, 'first_name' => $applicant->first_name,
        'date_of_birth' => '1990-01-01', 'sex' => 'Male', 'phone_number' => '09171234567',
        'email_address' => $applicant->email_address, 'address' => 'Bukidnon',
        'position_applied' => 'NURSE II', 'office' => 'PHRMO',
        'highest_educational_attainment' => 'College Graduate', 'eligibility' => 'CSC Professional',
    ]);

    $response->assertSessionHasErrors('plantilla_sync');
});
